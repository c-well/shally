<?php

namespace App\Services\Peace;

use Anthropic\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Writes the parts of a Peace Notes News issue that the sermon pipeline does
 * not already produce: the questions readers carry into the week, and the
 * two-minute preview Andre and KC approve from (how it opens, how it closes,
 * what to check). The summary itself is the sermon's own, already written.
 *
 * Quotes must be the preacher's words. Each one is checked against the
 * transcript excerpt it came from and dropped if it is not really there.
 */
class PeaceNewsGenerator
{
    private const MODEL = 'claude-opus-5-5';
    private const MAX_ATTEMPTS = 3;
    private const BACKOFF_SECONDS = [5, 20];

    /**
     * @param array{title:string, speaker:?string, heart_line:?string, summary:array<string>} $sermon
     * @return array{questions: array<string>, opening_summary: ?string, opening_quote: ?string,
     *               closing_summary: ?string, closing_quote: ?string, checks: array<string>}|null
     */
    public function generate(array $sermon, ?string $openingExcerpt, ?string $closingExcerpt, int $attempt = 1): ?array
    {
        $apiKey = config('services.anthropic.key');
        if (empty($apiKey)) {
            Log::error('PeaceNewsGenerator: ANTHROPIC_API_KEY not set');
            return null;
        }

        $msg = "SERMON TITLE: {$sermon['title']}\n"
             . "SPEAKER: " . ($sermon['speaker'] ?: '(unknown)') . "\n"
             . "HEART LINE: " . ($sermon['heart_line'] ?? '') . "\n\n"
             . "SUMMARY ALREADY WRITTEN FOR READERS:\n" . implode("\n\n", $sermon['summary']) . "\n\n"
             . "OPENING OF THE SERMON (transcript):\n" . ($openingExcerpt ?: '(not available)') . "\n\n"
             . "CLOSING OF THE SERMON (transcript):\n" . ($closingExcerpt ?: '(not available)');

        try {
            $client = new Client(apiKey: $apiKey);
            $response = $client->messages->create(
                model: self::MODEL,
                maxTokens: 3000,
                system: [['type' => 'text', 'text' => $this->systemPrompt()]],
                messages: [['role' => 'user', 'content' => $msg]],
            );
            try {
                \App\Models\AnthropicUsageLog::track([
                    'source'        => 'peace.news_generator',
                    'model'         => self::MODEL,
                    'input_tokens'  => $response->usage->inputTokens ?? 0,
                    'output_tokens' => $response->usage->outputTokens ?? 0,
                    'request_id'    => $response->id ?? null,
                    'outcome'       => 'ok',
                ]);
            } catch (Throwable $e) { /* never block on logging */ }

            $text = '';
            foreach ($response->content as $block) {
                if ($block->type === 'text') { $text = $block->text; break; }
            }
            $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));
            $data = json_decode($clean, true);
            if (! is_array($data) && preg_match('/\{.*\}/s', $clean, $mm)) {
                $data = json_decode($mm[0], true);
            }
            if (! is_array($data)) {
                Log::warning('PeaceNewsGenerator: malformed JSON', ['err' => json_last_error_msg(), 'stop' => $response->stopReason ?? null, 'raw' => substr($text, 0, 3000)]);
                return null;
            }
            return $this->clean($data, $openingExcerpt, $closingExcerpt);
        } catch (Throwable $e) {
            $m = strtolower($e->getMessage());
            $transient = false;
            foreach (['rate limit', 'overloaded', '429', '529', '503', '502', 'timed out', 'timeout'] as $n) {
                if (str_contains($m, $n)) { $transient = true; break; }
            }
            if ($transient && $attempt < self::MAX_ATTEMPTS) {
                sleep(self::BACKOFF_SECONDS[$attempt - 1] ?? 30);
                return $this->generate($sermon, $openingExcerpt, $closingExcerpt, $attempt + 1);
            }
            Log::warning('PeaceNewsGenerator failed', ['error' => $e->getMessage(), 'attempts' => $attempt]);
            return null;
        }
    }

    private function clean(array $d, ?string $open, ?string $close): array
    {
        $questions = array_values(array_filter(array_map(
            fn ($q) => is_string($q) ? trim($q) : '',
            (array) ($d['questions'] ?? [])
        )));
        $str = fn ($v, $max) => is_string($v) && trim($v) !== '' ? mb_substr(trim($v), 0, $max) : null;

        return [
            'questions'       => array_slice($questions, 0, 3),
            'opening_summary' => $str($d['opening_summary'] ?? null, 600),
            'opening_quote'   => $this->verbatim($str($d['opening_quote'] ?? null, 380), $open),
            'closing_summary' => $str($d['closing_summary'] ?? null, 600),
            'closing_quote'   => $this->verbatim($str($d['closing_quote'] ?? null, 380), $close),
            'checks'          => array_slice(array_values(array_filter(array_map(
                fn ($c) => is_string($c) ? mb_substr(trim($c), 0, 300) : '',
                (array) ($d['checks'] ?? [])
            ))), 0, 5),
        ];
    }

    /** Keep a quote only if it really appears in the excerpt (ignoring case and punctuation). */
    private function verbatim(?string $quote, ?string $excerpt): ?string
    {
        if (! $quote || ! $excerpt) return null;
        $norm = fn ($s) => trim(preg_replace('/\s+/', ' ', preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($s))));
        $q = $norm(trim($quote, " \"'“”‘’"));
        return ($q !== '' && str_contains($norm($excerpt), $q)) ? trim($quote, " \"'“”‘’") : null;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You help a church in the Bronx send a short weekly email called "Peace Notes News". It carries last Sabbath's sermon into the week for people who were there and people who were not. A summary for readers is already written. You write three things.

1. QUESTIONS: exactly 3 short questions a reader can carry through the week and answer honestly. Personal, warm, specific to this sermon's actual message, in plain words. Each under 25 words. No yes/no questions. No churchy jargon. Never assume the reader is a church member.

2. A PREVIEW for the two people who approve the email. They will not watch the whole sermon, so tell them in a few seconds how it opens and how it closes.
   - opening_summary: 1-2 sentences on how the sermon opens (story, scripture, greeting).
   - opening_quote: ONE sentence copied EXACTLY, word for word, from the OPENING transcript. Pick a line that shows the tone. Captions may lack punctuation; copy the words as they are, you may add normal punctuation. If the transcript is "(not available)", return null.
   - closing_summary: 1-2 sentences on the final appeal or point.
   - closing_quote: ONE sentence copied EXACTLY from the CLOSING transcript: the preacher's own final appeal. The excerpt often ends with a hymn, song lyrics or a prayer; never quote those, use the last thing he said to the people before them. Null if not available.

3. CHECKS: up to 4 short things the approvers should check before this goes to strangers, only if they really apply: a church member, child or family named in a sensitive context (illness, death, money, conflict); a date, number or event that may be out of date by Sunday; anything in the summary that seems to go beyond what the transcript says. If nothing applies, return an empty list. Do not invent problems.

Inside JSON strings never use straight double quotes; use single quotes or curly quotes instead.
Return ONLY this JSON, no preamble, no code fences:
{"questions": ["...", "...", "..."], "opening_summary": "...", "opening_quote": "..." or null, "closing_summary": "...", "closing_quote": "..." or null, "checks": ["..."]}
PROMPT;
    }
}
