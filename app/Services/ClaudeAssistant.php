<?php
namespace App\Services;

use Anthropic\Client;
use Throwable;

/**
 * Single-call wrapper around the Anthropic API for the in-app feedback bot.
 * - Stateless: each feedback submission is one API call.
 * - Continuity: passes the most recent N feedback log entries as context so Claude
 *   feels like she remembers Andre's running thread.
 * - Cost-aware: system prompt is cached (5-minute TTL) so back-to-back submissions
 *   only pay the cache-write premium once.
 */
class ClaudeAssistant
{
    private const MODEL = 'claude-opus-4-7';
    private const LOG_PATH = '/home/shalom/feedback-log.md';
    private const RECENT_ENTRIES = 5;

    public function reply(string $userName, string $tag, string $message): ?string
    {
        $apiKey = config('services.anthropic.key');
        if (empty($apiKey)) return null;

        try {
            $client = new Client(apiKey: $apiKey);

            $recentContext = $this->loadRecentEntries();
            $contextBlock = $recentContext === ''
                ? "(No prior feedback yet — this is the first entry.)"
                : "Recent prior entries from the same log (most recent last):\n\n{$recentContext}";

            $userMsg = "Andre just submitted a new feedback entry tagged **{$tag}**:\n\n\"{$message}\"\n\n— Reply to him directly.";

            $response = $client->messages->create(
                model: self::MODEL,
                maxTokens: 1024,
                system: [
                    [
                        'type' => 'text',
                        'text' => $this->systemPrompt(),
                        'cacheControl' => ['type' => 'ephemeral'],
                    ],
                    [
                        'type' => 'text',
                        'text' => $contextBlock,
                    ],
                ],
                messages: [
                    ['role' => 'user', 'content' => $userMsg],
                ],
            );

            // content is an array of polymorphic blocks — guard for the text block
            foreach ($response->content as $block) {
                if ($block->type === 'text') {
                    return trim($block->text);
                }
            }
            return null;
        } catch (Throwable $e) {
            \Log::warning('ClaudeAssistant::reply failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
You are Claude, the AI collaborator on the Church of Peace web app — a Laravel application Karlon Marshall is building for the Shalom Seventh-day Adventist Church in the Bronx, NY. Andre Marshall is the head clerk and primary user; he's not deeply technical but he is sharp, and he relies on this app to manage the weekly bulletin, events, and a department roster (Ushers, Deacons, Music, Communication, Platform Party).

When Andre submits feedback through the in-app form, you respond directly to him — warm but brief (3–6 sentences). Your job is to:
1. Acknowledge specifically what he wrote (don't be generic — quote a phrase or restate the actual issue/idea).
2. If it's a bug, ask one focused clarifying question (which page? which device? what did you click before it broke?). If the path forward is obvious, say what's next.
3. If it's an idea, reflect it back honestly — does it fit the existing patterns? Is there a simpler version? Anything you'd push back on?
4. Tell him Karlon will see this and follow up on the next action.
5. Sign off as **Claude** on its own line.

Style: warm, plainspoken, never sycophantic. No "Great question!" energy. Don't promise specific timelines. If you're uncertain, say so. If a previous entry in the log addresses this same topic, reference it briefly so it feels continuous.
PROMPT;
    }

    /**
     * Read only the last ~32KB of the log file (instead of the whole thing) so memory
     * stays bounded as the log grows. 32KB easily holds the last 5 entries even with
     * long Claude replies. Prevents OOM on a multi-megabyte log.
     */
    private function loadRecentEntries(): string
    {
        if (! is_file(self::LOG_PATH)) return '';
        $size = @filesize(self::LOG_PATH);
        if (! $size) return '';

        $tailBytes = 32768;
        $fp = @fopen(self::LOG_PATH, 'rb');
        if (! $fp) return '';
        @fseek($fp, max(0, $size - $tailBytes));
        $tail = @stream_get_contents($fp);
        @fclose($fp);
        if ($tail === false || $tail === '') return '';

        // If the tail-slice cut into the middle of an entry, drop the partial first chunk.
        $entries = array_filter(array_map('trim', explode("\n---\n\n", $tail)));
        if ($size > $tailBytes && count($entries) > 1) {
            array_shift($entries);
        }
        $recent = array_slice(array_values($entries), -self::RECENT_ENTRIES);
        return implode("\n\n---\n\n", $recent);
    }
}
