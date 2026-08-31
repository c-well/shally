<?php

namespace App\Console\Commands;

use App\Models\MailAction;
use App\Models\MailMessage;
use App\Services\Mail\Doveadm;
use Illuminate\Console\Command;
use Throwable;

/**
 * Drains the intent queue into Dovecot.
 *
 * The room already showed the change; this makes it true. If doveadm refuses,
 * the row keeps the error and the room can put the message back where it was
 * rather than quietly disagreeing with the server.
 */
class MailApply extends Command
{
    protected $signature = 'mail:apply {--limit=200}';

    protected $description = 'Apply queued mail actions (read, flag, archive, trash) to Dovecot';

    public function handle(): int
    {
        $dove = Doveadm::make();
        $done = $failed = 0;

        $pending = MailAction::pending()->orderBy('id')->limit((int) $this->option('limit'))->get();

        foreach ($pending as $a) {
            try {
                match ($a->action) {
                    'seen' => $dove->flag($a->mailbox, $a->folder, $a->uid, '\Seen', true),
                    'unseen' => $dove->flag($a->mailbox, $a->folder, $a->uid, '\Seen', false),
                    'flag' => $dove->flag($a->mailbox, $a->folder, $a->uid, '\Flagged', true),
                    'unflag' => $dove->flag($a->mailbox, $a->folder, $a->uid, '\Flagged', false),
                    'archive' => $dove->move($a->mailbox, $a->folder, $a->uid, 'Archive'),
                    'trash' => $dove->move($a->mailbox, $a->folder, $a->uid, 'Trash'),
                    'restore' => $dove->move($a->mailbox, $a->folder, $a->uid, 'INBOX'),
                    default => throw new \RuntimeException("unknown action {$a->action}"),
                };

                // A move changes the UID, so the local row is now a lie. Drop
                // it and let the next sync pick the message up where it lives.
                if (in_array($a->action, ['archive', 'trash', 'restore'], true)) {
                    MailMessage::where('mailbox', $a->mailbox)->where('folder', $a->folder)
                        ->where('uid', $a->uid)->delete();
                }

                $a->update(['applied_at' => now(), 'error' => null]);
                $done++;
            } catch (Throwable $e) {
                $a->update(['error' => mb_substr($e->getMessage(), 0, 500)]);
                $failed++;
                $this->warn("{$a->mailbox} uid {$a->uid} {$a->action}: {$e->getMessage()}");
            }
        }

        if ($done || $failed) {
            $this->info("applied {$done}".($failed ? ", failed {$failed}" : ''));
        }

        return self::SUCCESS;
    }
}
