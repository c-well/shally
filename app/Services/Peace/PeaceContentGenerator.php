<?php
namespace App\Services\Peace;

use Anthropic\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pass 2 — generate pastoral content from a sermon's transcript.
 *
 * Input:  the sermon-only slice of the transcript (Pass 1's boundaries already applied)
 * Output: { title, heart_line, summary_paragraphs[], scriptures[], qa_pairs[], topics[] }
 *
 * The voice / tone / framing rules in the system prompt are the non-negotiable
 * design from the spec — pastoral, Christ-centered (not denominational), seeker-language.
 */
class PeaceContentGenerator
{
    private const MODEL = 'claude-sonnet-4-5';

    /**
     * @return array{
     *   title: string, heart_line: string, summary_paragraphs: array<string>,
     *   scriptures: array<array{book: string, chapter: int, verse_start: int, verse_end: ?int, reference_display: string}>,
     *   qa_pairs:   array<array{question: string, answer: string, confidence: float}>,
     *   topics:     array<string>
     * }|null
     */
    public function generate(string $sermonTranscript, array $videoMeta): ?array
    {
        $apiKey = config('services.anthropic.key');
        if (empty($apiKey)) {
            Log::error('PeaceContentGenerator: ANTHROPIC_API_KEY not set');
            return null;
        }

        try {
            $client = new Client(apiKey: $apiKey);

            $userMsg = "Speaker: " . ($videoMeta['speaker'] ?? '(unknown)') . "\n"
                     . "Date: "    . ($videoMeta['date']    ?? '(unknown)') . "\n"
                     . "Stage title: " . ($videoMeta['title'] ?? '(unknown)') . "\n\n"
                     . "SERMON TRANSCRIPT (timestamped):\n\n" . $sermonTranscript;

            $response = $client->messages->create(
                model: self::MODEL,
                maxTokens: 4000,
                system: [
                    [
                        'type' => 'text',
                        'text' => $this->systemPrompt(),
                        'cacheControl' => ['type' => 'ephemeral'],
                    ],
                ],
                messages: [
                    ['role' => 'user', 'content' => $userMsg],
                ],
            );

                        // ANTHROPIC_USAGE_TRACKED — log this call for the in-app cost dashboard
            try {
                \App\Models\AnthropicUsageLog::track([
                    'source'             => 'peace.content_generator',
                    'model'              => self::MODEL,
                    'input_tokens'       => $response->usage->inputTokens ?? 0,
                    'output_tokens'      => $response->usage->outputTokens ?? 0,
                    'cache_read_tokens'  => $response->usage->cacheReadInputTokens ?? 0,
                    'cache_write_tokens' => $response->usage->cacheCreationInputTokens ?? 0,
                    'request_id'         => $response->id ?? null,
                    'outcome'            => 'ok',
                ]);
            } catch (\Throwable $e) { /* never block on logging */ }

            foreach ($response->content as $block) {
                if ($block->type === 'text') {
                    return $this->parseJson($block->text);
                }
            }
            return null;
        } catch (Throwable $e) {
            Log::warning('PeaceContentGenerator::generate failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an experienced pastoral writer creating content for "Finding Peace," a quiet, Christ-centered ministry website for people who are hurting and searching online for help.

Your audience is NOT the existing congregation. It is:
  - The grieving, the doubting, the depressed, the abandoned
  - The dechurched and the never-churched
  - People searching at 2am with phrases like "why does God let me feel so alone"
  - People who would close the tab if they sensed religious judgment, denominational positioning, or AI generation

Your voice must be:
  - Pastoral, not professorial — name the feeling before teaching
  - Christ-centered, not denominational — never lead with SDA distinctives
  - Honest about pain — never dismiss with platitudes like "just trust God"
  - Scripture-grounded — every claim anchored in real biblical text
  - Warm and specific — sound like a trusted pastor, not a Bible app

CRITICAL — handling the "fluff vs. meat" of a sermon:
Pastors typically open with a personal anecdote or story to draw listeners in (e.g., "I was driving this week and got a text..."). That opener is a hook, not the message's substance. The substance is in the scripture exposition (what the passage means) and the application/call (what it asks of the listener). Generate Q&As from THE SUBSTANCE. The opener may inform tone but should NEVER become a Q&A on its own.

Given the sermon transcript below, return ONLY this JSON. No preamble, no markdown fences, no commentary:

{
  "title": "<a quiet, evocative 1-3 word title — NOT the sermon's stage title if it's churchy>",
  "heart_line": "<ONE sentence, ~20 words, capturing the pastoral heart in seeker-language>",
  "summary_paragraphs": [
    "<paragraph 1: the message's core movement>",
    "<paragraph 2: how it lands>",
    "<paragraph 3: the closing call>"
  ],
  "scriptures": [
    {
      "book": "<full book name, e.g. '1 Kings'>",
      "chapter": <int>,
      "verse_start": <int>,
      "verse_end": <int or null>,
      "reference_display": "<e.g. 'Joshua 1:9' or '1 Kings 19:4-8'>"
    }
  ],
  "qa_pairs": [
    {
      "question": "<a first-person seeker question, e.g. 'Why do I feel discouraged even when I'm praying?'>",
      "answer": "<2-4 sentences. Name the feeling. Anchor in scripture or sermon insight. End with hope. Never preachy.>",
      "confidence": <0.0-1.0 — your confidence this Q&A is theologically sound and pastorally wise>
    }
  ],
  "topics": ["<2-5 lowercase topic tags, e.g. 'discouragement', 'prayer', 'doubt'>"]
}

RULES:
  1. Generate exactly 5 Q&A pairs — quality over quantity. The page surfaces all 5 as cards; more becomes scroll fatigue.
  2. Questions must sound like what someone Googles in pain — not theological treatises.
  3. Every scripture reference must be REAL. If you're unsure of a reference, OMIT it. A downstream system validates every reference against bible-api.com — invalid refs are dropped along with any Q&A that mentions them.
  4. Never quote the sermon directly. Paraphrase. Stand on the sermon's foundation; speak in your own pastoral voice.
  5. If the sermon contains denominational distinctives (Sabbath, Adventist eschatology, etc.), include them ONLY if they're central to that specific message. Default to broadly Christian framing.
  6. If a Q&A is theologically uncertain or pastorally risky, set its confidence below 0.6 — items below 0.6 will be filtered before publish.
  7. Return ONLY the JSON object.
PROMPT;
    }

    private function parseJson(string $text): ?array
    {
        $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));
        $data = json_decode($clean, true);
        if (! is_array($data)) {
            Log::warning('PeaceContentGenerator: malformed JSON', ['raw' => substr($text, 0, 500)]);
            return null;
        }
        return $data;
    }
}
