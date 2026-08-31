<?php

namespace App\Console\Commands;

use App\Models\MailMessage;
use App\Services\Mail\Doveadm;
use App\Services\Mail\MailTriage;
use Illuminate\Console\Command;
use Throwable;
use ZBateson\MailMimeParser\MailMimeParser;

class MailSync extends Command
{
    protected $signature = 'mail:sync
        {--mailbox= : One box only, e.g. media}
        {--folder=INBOX : Dovecot folder}
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

        $folder = $this->option('folder');
        $limit = (int) ($this->option('limit') ?: config('mailroom.sync_limit'));

        $added = $updated = $failed = 0;

        foreach ($boxes as $box) {
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

                if (isset($known[$uid]) && ! $this->option('fresh')) {
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

        $attachments = 0;
        foreach ($msg->getAllAttachmentParts() as $part) {
            // Inline images that only exist to lay out the mail are not
            // attachments in the sense anybody means by the word.
            if (strtolower((string) $part->getContentDisposition()) === 'inline' && $part->getContentId()) {
                continue;
            }
            $attachments++;
        }

        $verdict = $triage->classify($fromEmail, $fromName, $subject, $this->headerBlob($msg));

        MailMessage::updateOrCreate(
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
                'has_attachments' => $attachments > 0,
                'kind' => $verdict['kind'],
                'kind_confidence' => $verdict['confidence'],
                'kind_reason' => $verdict['reason'],
            ]
        );
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
