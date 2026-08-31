<?php

namespace App\Services\Mail;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * A thin wrapper round doveadm.
 *
 * This only ever runs from the CLI. The FPM pools carry
 * disable_functions = exec,passthru,shell_exec,system, which is the right
 * setting and stays — so the web request queues an intent and the scheduler
 * does the work. Calling any of this from a web request will throw.
 */
class Doveadm
{
    public function __construct(private readonly string $bin, private readonly string $domain) {}

    public static function make(): self
    {
        return new self(config('mailroom.doveadm'), config('mailroom.domain'));
    }

    public function address(string $box): string
    {
        return $box.'@'.$this->domain;
    }

    /** UID => flags string, for one folder. One call, however many messages. */
    public function index(string $box, string $folder): array
    {
        $out = $this->run(['fetch', '-u', $this->address($box), 'uid flags', 'mailbox', $folder]);

        $index = [];
        $uid = null;
        foreach (preg_split('/\R/', $out) as $line) {
            if (str_starts_with($line, 'uid: ')) {
                $uid = (int) substr($line, 5);
            } elseif ($uid !== null && str_starts_with($line, 'flags:')) {
                $index[$uid] = trim(substr($line, 6));
                $uid = null;
            }
        }

        return $index;
    }

    /** The whole raw message for one UID, headers and all. */
    public function raw(string $box, string $folder, int $uid): string
    {
        $out = $this->run([
            'fetch', '-u', $this->address($box), 'text',
            'mailbox', $folder, 'uid', (string) $uid,
        ]);

        // doveadm prefixes the field name; strip it and keep the message.
        return preg_replace('/\Atext:\R?/', '', $out) ?? $out;
    }

    public function flag(string $box, string $folder, int $uid, string $flag, bool $on): void
    {
        $this->run([
            'flags', $on ? 'add' : 'remove',
            '-u', $this->address($box), $flag,
            'mailbox', $folder, 'uid', (string) $uid,
        ]);
    }

    public function move(string $box, string $folder, int $uid, string $to): void
    {
        $this->run([
            'move', '-u', $this->address($box), $to,
            'mailbox', $folder, 'uid', (string) $uid,
        ]);
    }

    /**
     * Timeout on purpose: a wedged doveadm must not hold the scheduler open,
     * and the next run picks up exactly where this one stopped.
     */
    private function run(array $args): string
    {
        if (PHP_SAPI !== 'cli') {
            throw new RuntimeException('doveadm is CLI-only here — queue a mail_action instead.');
        }

        $p = new Process([$this->bin, ...$args]);
        $p->setTimeout(90);
        $p->run();

        if (! $p->isSuccessful()) {
            throw new RuntimeException('doveadm '.$args[0].' failed: '.trim($p->getErrorOutput() ?: $p->getOutput()));
        }

        return $p->getOutput();
    }
}
