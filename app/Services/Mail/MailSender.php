<?php
namespace App\Services\Mail;

use App\Models\MailAction;
use RuntimeException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Process\Process;

/**
 * Sends a reply, and files the copy.
 *
 * CLI only, same as Doveadm and for the same reason — the sent copy has to be
 * written into the mailbox with doveadm, and the web SAPI cannot shell out.
 *
 * One set of bytes does both jobs. The message is built once, then the same
 * string is piped to sendmail and to `doveadm save`. Building it twice would
 * eventually produce a Sent copy that differs from what the recipient got,
 * which is the kind of discrepancy nobody notices until it matters.
 *
 * Three things make a reply behave like a reply rather than a new message to
 * the same person:
 *
 *   From      the mailbox it is answering as — media@, treasurer@ — never the
 *             app's own address. A reply from app@ to someone who wrote to
 *             media@ reads as a machine and breaks the conversation.
 *   Threading In-Reply-To and References, so it lands inside the thread.
 *   Sent      filed into INBOX.Sent, so whoever opens this mailbox in their
 *             own mail app sees the reply there too. Without it the room
 *             would be sending mail that exists nowhere else.
 */
class MailSender
{
    public function __construct(
        private readonly Doveadm $dove,
        private readonly string $sendmail = '/usr/sbin/sendmail',
    ) {}

    public static function make(): self
    {
        return new self(Doveadm::make(), config('mailroom.sendmail', '/usr/sbin/sendmail'));
    }

    /** Send one queued reply and file it. Throws on failure so the row keeps its error. */
    public function send(MailAction $action): void
    {
        if (PHP_SAPI !== 'cli') {
            throw new RuntimeException('sending is CLI-only here — queue a mail_action instead.');
        }

        $p    = $action->payload ?? [];
        $from = $this->dove->address($action->mailbox);

        $to = array_values(array_filter((array) ($p['to'] ?? [])));
        if (! $to) {
            throw new RuntimeException('no recipient');
        }

        $email = (new Email())
            ->from(new Address($from, (string) ($p['from_name'] ?? '')))
            ->subject((string) ($p['subject'] ?? '(no subject)'))
            ->text((string) ($p['body'] ?? ''));

        foreach ($to as $addr) {
            $email->addTo($addr);
        }
        foreach (array_values(array_filter((array) ($p['cc'] ?? []))) as $addr) {
            $email->addCc($addr);
        }

        // Threading. Both headers want angle brackets; what we stored may or
        // may not have them, so normalise rather than trust.
        $h = $email->getHeaders();
        if ($irt = $this->bracket($p['in_reply_to'] ?? null)) {
            $h->addIdHeader('In-Reply-To', [trim($irt, '<>')]);
            $chain = trim(((string) ($p['references'] ?? '')) . ' ' . $irt);
            $ids   = array_values(array_filter(array_map(
                fn ($x) => trim($x, '<> '),
                preg_split('/\s+/', $chain) ?: []
            )));
            if ($ids) {
                $h->addIdHeader('References', array_unique($ids));
            }
        }

        $raw = $email->toString();

        // Out first. If sendmail fails nothing is filed, and the queue row
        // keeps its error for a retry — better than a Sent copy of a message
        // that never left.
        $this->pipe([$this->sendmail, '-t', '-i', '-f', $from], $raw, 'sendmail');

        // Then filed. A failure here means the recipient has it but the
        // mailbox does not, so it is logged loudly rather than swallowed.
        try {
            $this->dove->save($action->mailbox, config('mailroom.sent_to', 'INBOX.Sent'), $raw);
        } catch (\Throwable $e) {
            \Log::error('mail: sent but not filed into Sent', [
                'mailbox' => $action->mailbox,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function bracket(?string $id): ?string
    {
        $id = trim((string) $id);
        return $id === '' ? null : (str_starts_with($id, '<') ? $id : "<{$id}>");
    }

    private function pipe(array $cmd, string $input, string $label): void
    {
        $proc = new Process($cmd);
        $proc->setInput($input);
        $proc->setTimeout(60);
        $proc->run();

        if (! $proc->isSuccessful()) {
            throw new RuntimeException(
                "{$label} failed (exit {$proc->getExitCode()}): " . trim($proc->getErrorOutput() ?: $proc->getOutput())
            );
        }
    }

    /**
     * The quoted original, in the plain-text convention every mail client
     * understands. HTML mail is quoted from its text part, because a reply
     * that embeds somebody's marketing HTML inside it is a mess.
     */
    public static function quote(?string $body, ?string $who, ?string $when): string
    {
        $body = trim((string) $body);
        if ($body === '') {
            return '';
        }

        $lines = preg_split('/\r?\n/', $body) ?: [];
        $lines = array_slice($lines, 0, 200);   // a reply is not an archive

        $head = 'On ' . ($when ?: 'an earlier date') . ', ' . ($who ?: 'they') . ' wrote:';

        return "\n\n" . $head . "\n" . implode("\n", array_map(fn ($l) => '> ' . $l, $lines)) . "\n";
    }
}
