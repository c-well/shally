<?php

namespace App\Services\Mail;

/**
 * Which of the three piles does this belong in — a person writing, a machine
 * reporting, or money?
 *
 * Deliberately rules over cleverness, and the order of the rules is the whole
 * design. A verification code from a shop is not a receipt just because the
 * shop sells things; a marketing blast from PayPal is not money just because
 * PayPal is money. So: what the message *is* beats who it is from, and who it
 * is from beats what it looks like.
 *
 * Every verdict carries the reason that produced it, so when a message lands
 * in the wrong pile you can see which rule fired instead of guessing at a
 * model. Nothing here is destructive — the pile is a filter, never a move.
 */
class MailTriage
{
    /** Transactional but not money. Checked first: these are the ones that
        get misread as receipts because of who sent them. */
    private const NOTICE_SUBJECT = [
        'verification code', 'verify your', 'confirm your email', 'email verification',
        'security alert', 'sign-in', 'sign in attempt', 'password', 'reset your',
        'two-factor', 'terms of service', 'privacy policy', 'subprocessors',
        'legal agreement', 'account is inactive', 'action required',
        'temporary pin', 'one-time', 'confirmation request',
    ];

    private const MONEY_SUBJECT = [
        'receipt', 'invoice', 'statement', 'payment received', 'payout', 'your order',
        'order confirmation', 'amount due', 'giving', 'donation', 'tithe',
        'renewal notice', 'refund', 'bill is ready', 'billing statement',
        'order #', 'order confirmed', 'bill payment',
    ];

    private const MONEY_SENDER = [
        'adventistgiving', 'stripe', 'paypal', 'square', 'quickbooks', 'bhphoto',
        'bhphotovideo', 'bunny.net', 'namecheap', 'coned', 'intuit', 'zeffy', 'venmo',
    ];

    /** A subdomain that exists only to send mail. Nobody at news.example.com
        is typing to you. */
    private const BULK_SUBDOMAIN = [
        'order.', 'orders.', 'email.', 'emails.', 'mail.', 'mailer.', 'news.',
        'newsletter.', 'connect.', 'connected.', 'notify.', 'notification.',
        'reply.', 'e.', 'em.', 'info.', 'marketing.', 'campaign.',
    ];

    /** Role addresses — a desk, not a person. */
    private const ROLE_LOCAL = [
        'noreply', 'no-reply', 'donotreply', 'do-not-reply', 'notifications',
        'notification', 'mailer', 'mailer-daemon', 'bounce', 'automated', 'auto',
        'postmaster', 'webmaster', 'cpanel', 'wordpress', 'admin', 'root',
        'newsletter', 'news', 'updates', 'alerts', 'team', 'webteam', 'support',
    ];

    /** @return array{kind:string, confidence:float, reason:string} */
    public function classify(string $fromEmail, string $fromName, string $subject, string $headers = ''): array
    {
        $addr = strtolower($fromEmail);
        $subj = strtolower($subject);
        $head = strtolower($headers);

        [$local, $domain] = array_pad(explode('@', $addr, 2), 2, '');

        foreach (self::NOTICE_SUBJECT as $s) {
            if (str_contains($subj, $s)) {
                return $this->verdict('update', 0.85, "subject is a notice — “{$s}”");
            }
        }

        foreach (self::MONEY_SUBJECT as $s) {
            if (str_contains($subj, $s)) {
                return $this->verdict('receipt', 0.85, "subject mentions “{$s}”");
            }
        }

        // A List-Unsubscribe header is the most honest signal there is: the
        // sender has told you outright that this is a mailing, not a letter.
        if (str_contains($head, 'list-unsubscribe')) {
            return $this->verdict('update', 0.9, 'carries List-Unsubscribe');
        }

        if (preg_match('/auto-(generated|replied|submitted)|bulk|list-id:/', $head)) {
            return $this->verdict('update', 0.8, 'marked bulk or auto-generated');
        }

        foreach (self::BULK_SUBDOMAIN as $sub) {
            if (str_starts_with($domain, $sub)) {
                return $this->verdict('update', 0.8, "sent from a bulk subdomain, {$domain}");
            }
        }

        foreach (self::MONEY_SENDER as $s) {
            if (str_contains($addr, $s)) {
                return $this->verdict('receipt', 0.7, "sender is {$s}");
            }
        }

        foreach (self::ROLE_LOCAL as $r) {
            // Whole word only, so "andrew" is not read as "andre-the-desk";
            // separators are what make it a role and not somebody's name.
            if ($local === $r || preg_match('/(^|[.\-_])'.preg_quote($r, '/').'([.\-_]|$)/', $local)) {
                return $this->verdict('update', 0.75, "sent from the “{$r}” desk, not a person");
            }
        }

        // Some machines sign with a name rather than an address.
        foreach (['wordpress', 'cpanel', 'mailer-daemon', 'postmaster'] as $bot) {
            if (str_contains(strtolower($fromName), $bot)) {
                return $this->verdict('update', 0.8, "signed “{$bot}”");
            }
        }

        // What is left is somebody writing. A full name on the From line
        // raises the confidence; a bare address lowers it.
        $named = $fromName !== '' && $fromName !== $fromEmail && str_contains(trim($fromName), ' ');

        return $this->verdict('person', $named ? 0.8 : 0.55,
            $named ? 'a person’s name on the From line' : 'nothing marks it as automated');
    }

    private function verdict(string $kind, float $confidence, string $reason): array
    {
        return ['kind' => $kind, 'confidence' => $confidence, 'reason' => $reason];
    }
}
