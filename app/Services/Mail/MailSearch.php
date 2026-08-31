<?php

namespace App\Services\Mail;

use App\Models\MailMessage;

/**
 * Search across every mailbox at once.
 *
 * Free words search everything. A key:value scopes to one field, so
 * "from:bhphoto" finds the vendor without also matching every message that
 * happens to mention them. Aliases are generous on purpose — sent:, by: and
 * from: all mean the same thing, because nobody should have to remember which
 * word this app happened to choose.
 *
 * Spelling does not have to be right: a prefix wins, then a forgiven typo,
 * then anywhere in the word. So "depot" still finds homedepot and "invoce"
 * still finds invoice, but an exact start always ranks above them.
 */
class MailSearch
{
    private const ALIAS = [
        'from' => 'from', 'sent' => 'from', 'by' => 'from', 'sender' => 'from',
        'subject' => 'subject', 'subj' => 'subject', 're' => 'subject',
        'in' => 'box', 'box' => 'box', 'mailbox' => 'box', 'folder' => 'box',
        'has' => 'has', 'is' => 'is', 'kind' => 'kind', 'type' => 'kind',
    ];

    public function run(string $raw): array
    {
        [$ops, $free] = $this->parse($raw);

        if (! $ops && ! $free) {
            return ['query' => $raw, 'items' => [], 'ops' => [], 'fixes' => [], 'boxes' => 0];
        }

        $hits = [];
        $fixes = [];

        // Search everywhere except the bin — a thing you threw away should
        // not come back as a result, but archived mail is exactly what people
        // are looking for when they search.
        MailMessage::where('folder', '!=', config('mailroom.trash_to'))->orderByDesc('sent_at')->chunk(500, function ($chunk) use ($ops, $free, &$hits, &$fixes) {
            foreach ($chunk as $m) {
                // Scoped terms are gates: fail one and the message is out.
                foreach ($ops as $o) {
                    if ($o['field'] === 'has') {
                        if (! $m->has_attachments) {
                            continue 2;
                        }

                        continue;
                    }
                    if ($o['field'] === 'is') {
                        if (preg_match('/unread|new/', $o['value']) && $m->seen) {
                            continue 2;
                        }
                        if (preg_match('/read|seen/', $o['value']) && ! $m->seen) {
                            continue 2;
                        }
                        if (preg_match('/flag/', $o['value']) && ! $m->flagged) {
                            continue 2;
                        }

                        continue;
                    }

                    $words = explode(' ', $this->fieldText($m, $o['field']));
                    foreach (explode(' ', $o['value']) as $v) {
                        if (! $this->matchTerm($v, $words)) {
                            continue 3;
                        }
                    }
                }

                $words = explode(' ', $this->norm(implode(' ', [
                    $m->from_name, $m->from_email, $m->subject, $m->preview, $m->body_text,
                ])));

                $score = count($ops) * 3;
                foreach ($free as $t) {
                    $r = $this->matchTerm($t, $words);
                    if (! $r) {
                        continue 2;
                    }
                    $score += $r['score'];
                    if (isset($r['was'])) {
                        $fixes[$t] = $r['was'];
                    }
                }

                $hits[] = ['m' => $m, 'score' => $score];
            }
        });

        usort($hits, fn ($a, $b) => $b['score'] <=> $a['score']);

        return [
            'query' => $raw,
            'ops' => $ops,
            'fixes' => array_values($fixes),
            'boxes' => count(array_unique(array_map(fn ($h) => $h['m']->mailbox, $hits))),
            'items' => array_map(fn ($h) => [
                'id' => $h['m']->id,
                'box' => $h['m']->mailbox,
                'folder' => $h['m']->folder,
                'who' => $h['m']->from_name,
                'addr' => $h['m']->from_email,
                'subj' => $h['m']->subject,
                'prev' => $h['m']->preview,
                'when' => $h['m']->when,
                'kind' => $h['m']->kind,
                'seen' => (bool) $h['m']->seen,
                'flagged' => (bool) $h['m']->flagged,
                'attach' => (bool) $h['m']->has_attachments,
            ], $hits),
        ];
    }

    /** @return array{0: array<int, array{field:string, value:string}>, 1: array<int, string>} */
    private function parse(string $raw): array
    {
        $ops = $free = [];

        preg_match_all('/(\w+):("([^"]*)"|\S+)|(\S+)/u', $raw, $m, PREG_SET_ORDER);

        foreach ($m as $bit) {
            $key = strtolower($bit[1] ?? '');
            if ($key !== '' && isset(self::ALIAS[$key])) {
                $ops[] = ['field' => self::ALIAS[$key], 'value' => $this->norm($bit[3] ?? $bit[2])];
            } else {
                foreach (explode(' ', $this->norm($bit[0])) as $t) {
                    if (strlen($t) >= 2) {
                        $free[] = $t;
                    }
                }
            }
        }

        return [$ops, $free];
    }

    private function fieldText(MailMessage $m, string $field): string
    {
        return match ($field) {
            'from' => $this->norm($m->from_name.' '.$m->from_email),
            'subject' => $this->norm($m->subject),
            'box' => $this->norm($m->mailbox),
            'kind' => $this->norm((string) $m->kind),
            default => '',
        };
    }

    /** @return array{score:int, was?:string}|null */
    private function matchTerm(string $term, array $words): ?array
    {
        foreach ($words as $w) {
            if ($w !== '' && str_starts_with($w, $term)) {
                return ['score' => 3];
            }
        }

        $tol = strlen($term) <= 3 ? 0 : (strlen($term) <= 6 ? 1 : 2);
        if ($tol > 0) {
            foreach ($words as $w) {
                if ($w !== '' && abs(strlen($w) - strlen($term)) <= $tol && levenshtein($term, $w) <= $tol) {
                    return ['score' => 2, 'was' => $w];
                }
            }
        }

        if (strlen($term) >= 3) {
            foreach ($words as $w) {
                if ($w !== '' && str_contains($w, $term)) {
                    return ['score' => 1];
                }
            }
        }

        return null;
    }

    private function norm(string $x): string
    {
        $x = mb_strtolower($x);
        $x = preg_replace('/[^a-z0-9 ]/u', ' ', $x) ?? $x;

        return trim(preg_replace('/\s+/', ' ', $x) ?? $x);
    }
}
