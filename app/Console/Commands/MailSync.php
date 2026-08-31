<?php

namespace App\Console\Commands;

use App\Models\MailAttachment;
use App\Models\MailMessage;
use App\Services\Mail\Doveadm;
use App\Services\Mail\MailTriage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;
use ZBateson\MailMimeParser\MailMimeParser;

class MailSync extends Command
{
    protected $signature = 'mail:sync
        {--mailbox= : One box only, e.g. media}
        {--folder= : One folder only, e.g. INBOX. Default is every folder in config}
        {--uid= : One message only, by UID — re-reads it and caches its files}
        {--limit= : Stop after this many new messages}
        {--fresh : Re-read messages already stored}';

    protected $description = 'Read mail off Dovecot into the database so the room opens with no wait';

    public function handle(MailTriage $triage): int
    {
        $dove = Doveadm::make();
        $parser = new MailMimeParser;

        $boxes = $this->option('mailbox')
            ? [$this->option('mailbox')]
            : array_keys(config('mailroom.boxes'));

        $folders = $this->option('folder')
            ? [$this->option('folder')]
            : array_keys(config('mailroom.folders'));

        $limit = (int) ($this->option('limit') ?: config('mailroom.sync_limit'));

        $added = $updated = $failed = 0;

        foreach ($boxes as $box) {
            foreach ($folders as $folder) {
                try {
                    $index = $dove->index($box, $folder);
                } catch (Throwable $e) {
                    $this->error("{$box}: {$e->getMessage()}");
                    $failed++;

                    continue;
                }

                $known = MailMessage::where('mailbox', $box)->where('folder', $folder)
                    ->pluck('seen', 'uid')->all();

                // Gone from the server means gone from the room. Dovecot is the
                // record; this table is only a fast copy of it.
                $vanished = array_diff(array_keys($known), array_keys($index));
                if ($vanished) {
                    MailMessage::where('mailbox', $box)->where('folder', $folder)
                        ->whereIn('uid', $vanished)->delete();
                }

                // Newest first, so a first run on a big box gets the useful end.
                $uids = array_keys($index);
                rsort($uids);

                $new = 0;
                foreach ($uids as $uid) {
                    $flags = $index[$uid];
                    $seen = str_contains($flags, '\Seen');
                    $flagged = str_contains($flags, '\Flagged');

                    if ($this->option('uid') && (int) $this->option('uid') !== $uid) {
                        continue;
                    }

                    if (isset($known[$uid]) && ! $this->option('fresh') && ! $this->option('uid')) {
                        // Already stored — only the flags can have moved.
                        if ((bool) $known[$uid] !== $seen) {
                            MailMessage::where('mailbox', $box)->where('folder', $folder)
                                ->where('uid', $uid)->update(['seen' => $seen, 'flagged' => $flagged]);
                            $updated++;
                        }

                        continue;
                    }

                    if ($new >= $limit) {
                        break;
                    }

                    try {
                        $this->store($box, $folder, $uid, $seen, $flagged, $dove, $parser, $triage);
                        $added++;
                        $new++;
                    } catch (Throwable $e) {
                        $this->warn("{$box} uid {$uid}: {$e->getMessage()}");
                        $failed++;
                    }
                }

                $this->line("{$box}/{$folder}: ".count($index).' on server');
            }
        }

        $this->info("added {$added}, flags updated {$updated}".($failed ? ", failed {$failed}" : ''));

        return $failed && ! $added ? self::FAILURE : self::SUCCESS;
    }

    private function store(
        string $box, string $folder, int $uid, bool $seen, bool $flagged,
        Doveadm $dove, MailMimeParser $parser, MailTriage $triage
    ): void {
        $raw = $dove->raw($box, $folder, $uid);
        $msg = $parser->parse($raw, false);

        $from = $msg->getHeader('From');
        $fromEmail = $from?->getEmail() ?? '';
        $fromName = trim((string) ($from?->getPersonName() ?? '')) ?: $fromEmail;

        $subject = trim((string) $msg->getHeaderValue('Subject')) ?: '(no subject)';

        $text = (string) $msg->getTextContent();
        $html = (string) $msg->getHtmlContent();

        // A message with no plain part still has to read as something in the
        // list, so fall back to the HTML stripped down.
        if (trim($text) === '' && $html !== '') {
            // strip_tags leaves the *contents* of <style> and <script> behind,
            // which is how a search for "order" starts matching "border".
            $stripped = preg_replace('#<(style|script)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
            $text = trim(preg_replace('/\s+/u', ' ',
                html_entity_decode(strip_tags($stripped), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        }

        $max = config('mailroom.max_body');
        $sentAt = $msg->getHeader('Date')?->getDateTime();

        $verdict = $triage->classify($fromEmail, $fromName, $subject, $this->headerBlob($msg));

        $row = MailMessage::updateOrCreate(
            ['mailbox' => $box, 'folder' => $folder, 'uid' => $uid],
            [
                'message_id' => substr((string) $msg->getHeaderValue('Message-ID'), 0, 255) ?: null,
                'from_name' => mb_substr($fromName, 0, 190),
                'from_email' => mb_substr($fromEmail, 0, 190),
                'subject' => mb_substr($subject, 0, 500),
                'preview' => mb_substr(preg_replace('/\s+/u', ' ', $text), 0, 300),
                'body_text' => mb_substr($text, 0, $max),
                'body_html' => $html ? mb_substr($html, 0, $max) : null,
                'sent_at' => $sentAt,
                'seen' => $seen,
                'flagged' => $flagged,
                'has_attachments' => false,   // set once they are actually saved, below
                'kind' => $verdict['kind'],
                'kind_confidence' => $verdict['confidence'],
                'kind_reason' => $verdict['reason'],
            ]
        );

        $this->storeAttachments($row, $msg);
    }

    /**
     * Record every attachment, and cache the bytes while there is room.
     *
     * Recording and caching are separate on purpose. The room should always
     * know a file exists — its name, type and size — even when we are not
     * holding a copy. Opening one we do not hold pulls it from the original
     * message, which never left the mailbox.
     */
    private function storeAttachments(MailMessage $row, $msg): void
    {
        $dir = storage_path('app/mail-attachments');
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        foreach ($row->attachments()->get() as $old) {
            @unlink($old->path);
            $old->delete();
        }

        // An explicit fetch means somebody asked for this file by name, so it
        // gets room whether or not the mailbox is at its budget.
        $budget = $this->option('uid')
            ? PHP_INT_MAX
            : $this->roomLeft($row->mailbox);
        $ceiling = (int) config('mailroom.cache_file_ceiling');
        $saved = 0;
        $i = -1;

        foreach ($msg->getAllAttachmentParts() as $part) {
            $i++;

            // Inline images that only exist to lay out the mail are not
            // attachments in the sense anybody means by the word.
            if (strtolower((string) $part->getContentDisposition()) === 'inline' && $part->getContentId()) {
                continue;
            }

            $name = $this->safeName((string) $part->getFilename());
            $bytes = (string) $part->getContent();
            $size = strlen($bytes);
            $stored = Str::ulid().'.'.$this->extension($name);

            $keep = $size <= $ceiling && $size <= $budget;

            if ($keep) {
                file_put_contents($dir.'/'.$stored, $bytes);
                chmod($dir.'/'.$stored, 0640);
                $budget -= $size;
            }

            MailAttachment::create([
                'mail_message_id' => $row->id,
                'name' => $name,
                'stored_name' => $stored,
                'mime' => strtolower((string) $part->getContentType()),
                'bytes' => $size,
                'part_index' => $i,
                'cached_at' => $keep ? now() : null,
            ]);

            $saved++;
        }

        if ($saved) {
            $row->update(['has_attachments' => true]);
        }
    }

    /** How many more bytes this mailbox may hold on our disk. */
    private function roomLeft(string $box): int
    {
        $used = (int) MailAttachment::whereNotNull('cached_at')
            ->whereIn('mail_message_id', MailMessage::where('mailbox', $box)->select('id'))
            ->sum('bytes');

        return max(0, (int) config('mailroom.cache_budget') - $used);
    }

    /**
     * The displayed name. Strips any path the sender put in it, drops control
     * characters, and refuses to be empty.
     */
    private function safeName(string $raw): string
    {
        $name = basename(str_replace('\\', '/', $raw));
        $name = preg_replace('/[\x00-\x1f\/]/u', '', $name) ?? $name;
        $name = trim($name, " .\t");

        return mb_substr($name !== '' ? $name : 'attachment', 0, 200);
    }

    /** Our extension: taken from the name, but constrained to something sane. */
    private function extension(string $name): string
    {
        $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));

        return preg_match('/^[a-z0-9]{1,8}$/', $ext) ? $ext : 'bin';
    }

    /** Just the headers triage cares about, lowercased into one string. */
    private function headerBlob($msg): string
    {
        $out = [];
        foreach (['List-Unsubscribe', 'List-Id', 'Precedence', 'Auto-Submitted', 'X-Mailer'] as $h) {
            if ($v = $msg->getHeaderValue($h)) {
                $out[] = $h.': '.$v;
            }
        }

        return implode("\n", $out);
    }
}
