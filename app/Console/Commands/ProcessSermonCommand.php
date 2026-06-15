<?php
namespace App\Console\Commands;

use App\Models\PeaceQaPair;
use App\Models\PeaceScripture;
use App\Models\PeaceSermon;
use App\Models\PeaceTopic;
use App\Services\Peace\AudioExtractor;
use App\Services\Peace\BoundaryDetector;
use App\Services\Peace\PeaceContentGenerator;
use App\Services\Peace\ScriptureValidator;
use App\Services\Peace\TranscriptFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manual entry point for the Peace pipeline.
 *
 *   php artisan peace:process VIDEO_ID            # full pipeline
 *   php artisan peace:process VIDEO_ID --dry-run  # all passes but no DB write
 *   php artisan peace:process VIDEO_ID --skip-audio  # text-only test, no yt-dlp audio download
 *
 * This is the calibration command — what we use to validate the prompts before
 * any cron or queue infrastructure runs.
 */
class ProcessSermonCommand extends Command
{
    protected $signature = 'peace:process
                            {video_id : YouTube video ID (e.g., NLHFsH0qlkc)}
                            {--dry-run : Run all passes but do not write to DB}
                            {--skip-audio : Skip the audio extraction step (text-only test)}';

    protected $description = 'Process a YouTube sermon through the full Peace pipeline';

    public function handle(
        TranscriptFetcher $fetcher,
        BoundaryDetector $detector,
        AudioExtractor $audio,
        PeaceContentGenerator $generator,
        ScriptureValidator $validator,
    ): int {
        $videoId = $this->argument('video_id');
        $dryRun  = $this->option('dry-run');
        $skipAudio = $this->option('skip-audio');

        $this->info("┌─ Peace pipeline ─────────────────────────────────");
        $this->line("│  Video ID:  {$videoId}");
        $this->line("│  Dry-run:   " . ($dryRun ? 'YES (no DB writes)' : 'no'));
        $this->line("│  Skip audio: " . ($skipAudio ? 'YES' : 'no'));
        $this->info("└──────────────────────────────────────────────────");

        // ═══ Step 1: Metadata + transcript ════════════════════════════
        $this->info("\n[1/6] Fetching metadata + transcript via yt-dlp …");
        $meta = $fetcher->getMetadata($videoId);
        if (! $meta) { $this->error('   metadata fetch failed'); return self::FAILURE; }
        $this->line("   title:    {$meta['title']}");
        $this->line("   duration: {$meta['duration']}s (" . gmdate('H:i:s', $meta['duration']) . ')');
        $this->line("   was_live: " . ($meta['was_live'] ? 'true' : 'false'));

        $transcript = $fetcher->getTranscript($videoId);
        if (! $transcript) { $this->error('   transcript fetch failed'); return self::FAILURE; }
        $this->line("   captions: " . count($transcript) . " lines");

        // ═══ Step 2: Pass 1 — boundary detection (NEW: gap-based, no AI) ═════
        $this->info("\n[2/6] Pass 1: boundary detection (gap heuristic on captions) …");
        $bounds = $detector->detect($transcript, $meta['duration']);
        if (! $bounds) { $this->error('   boundary detection failed'); return self::FAILURE; }
        $startFmt = gmdate('H:i:s', $bounds['sermon_start_seconds']);
        $endFmt   = gmdate('H:i:s', $bounds['sermon_end_seconds']);
        $this->line("   Pass 1 →  start={$bounds['sermon_start_seconds']}s ({$startFmt})  end={$bounds['sermon_end_seconds']}s ({$endFmt})");
        $this->line("              confidence={$bounds['confidence']}  reason: {$bounds['reason']}");
        if (! empty($bounds['candidates'])) {
            $this->line("   top candidates (by length):");
            foreach ($bounds['candidates'] as $c) $this->line("     • " . $c);
        }

        if ($bounds['confidence'] < 0.70) {
            $this->warn("   ⚠  confidence below 0.70 — would normally flag for admin review");
            if (! $this->confirm('Continue anyway?', true)) return self::FAILURE;
        }

        // ═══ Step 3: Slice transcript for Pass 2 ═══════════════════════
        $sermonText = $fetcher->formatForPrompt($transcript, $bounds["sermon_start_seconds"], $bounds["sermon_end_seconds"], false); // TOKEN_DIET: no timestamps for content gen
        $this->line("\n[3/6] Sermon-only slice: " . strlen($sermonText) . " chars (~" . (int) (str_word_count($sermonText) * 1.3) . " tokens)");

        // ═══ Step 4: Pass 2 — content generation ═══════════════════════
        $this->info("\n[4/6] Pass 2: generate Q&As + scripture + summary (sermon slice → Claude) …");
        $content = $generator->generate($sermonText, [
            'speaker' => $meta['channel'] ?? '',
            'date'    => $this->parseUploadDate($meta['upload_date'])?->toDateString() ?? '',
            'title'   => $meta['title'],
        ]);
        if (! $content) { $this->error('   content gen failed'); return self::FAILURE; }
        $this->line("   title:      \"{$content['title']}\"");
        $this->line("   heart_line: \"{$content['heart_line']}\"");
        $this->line("   summary:    " . count($content['summary_paragraphs'] ?? []) . " paragraphs");
        $this->line("   scriptures: " . count($content['scriptures'] ?? []) . " refs");
        $this->line("   qa_pairs:   " . count($content['qa_pairs'] ?? []));
        $this->line("   topics:     " . implode(', ', $content['topics'] ?? []));

        // ═══ Step 5: Validate scriptures (cascade-drop bad Q&As) ══════
        $this->info("\n[5/6] Validating scriptures against bible-api.com …");
        $validatedScriptures = $validator->validateAll($content['scriptures'] ?? []);
        $this->line("   " . count($validatedScriptures) . "/" . count($content['scriptures'] ?? []) . " refs validated");

        $validRefs = array_map(fn($s) => $s['reference_display'], $validatedScriptures);
        $cleanQAs = $this->dropQAsWithBadScripture($content['qa_pairs'] ?? [], $validRefs);
        if (count($cleanQAs) < count($content['qa_pairs'] ?? [])) {
            $this->warn("   dropped " . (count($content['qa_pairs']) - count($cleanQAs)) . " Q&As that referenced unvalidated scripture");
        }
        $cleanQAs = array_filter($cleanQAs, fn($qa) => ($qa['confidence'] ?? 0) >= 0.6);
        $this->line("   " . count($cleanQAs) . " Q&As remain after confidence + scripture filter");

        // ═══ Step 6: Audio (skip if --skip-audio) ═════════════════════
        $audioPath = null;
        if (! $skipAudio) {
            $this->info("\n[6/6] Audio extraction (yt-dlp + ffmpeg) …");
            $audioPath = $audio->extract($videoId, $bounds['sermon_start_seconds'], $bounds['sermon_end_seconds']);
            if ($audioPath) {
                $this->line("   audio saved: {$audioPath}");
            } else {
                $this->warn("   audio extraction failed (continuing without)");
            }
        } else {
            $this->info("\n[6/6] Audio extraction SKIPPED (--skip-audio)");
        }

        // ═══ DB write (skip if --dry-run) ═════════════════════════════
        if ($dryRun) {
            $this->newLine();
            $this->info('DRY RUN — no DB writes.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Saving to DB …');

        $sermon = DB::transaction(function () use ($videoId, $meta, $bounds, $content, $validatedScriptures, $cleanQAs, $audioPath) {
            // Parse speaker from YouTube title — e.g., "My Sermon" || Pastor Foo || Shalom SDA
            $parsedSpeaker = null;
            if (str_contains($meta['title'], '||')) {
                $parts = array_map('trim', explode('||', $meta['title']));
                if (count($parts) >= 2) {
                    $candidate = $parts[1];
                    // Strip leading 'Pastor:' / 'Pastor :' / 'Speaker:' prefixes
                    $candidate = trim(preg_replace('/^(Pastor|Speaker|Elder)\s*:?\s*/i', '', $candidate));
                    if ($candidate !== '' && $candidate !== $parts[0]) {
                        $parsedSpeaker = $candidate;
                    }
                }
            }

            $sermon = PeaceSermon::updateOrCreate(
                ['youtube_video_id' => $videoId],
                [
                    'slug'                  => $this->slugify($content['title'], $videoId),
                    'title'                 => $content['title'] ?? $meta['title'],
                    'speaker'               => $parsedSpeaker ?? $meta['channel'] ?? null,
                    'sermon_date'           => $this->parseUploadDate($meta['upload_date']) ?? now(),
                    'heart_line'            => $content['heart_line'] ?? null,
                    'summary_paragraphs'    => $content['summary_paragraphs'] ?? [],
                    'audio_url'             => $audioPath,
                    'audio_status'          => $audioPath ? 'ready' : 'pending',
                    'sermon_start_seconds'  => $bounds['sermon_start_seconds'],
                    'sermon_end_seconds'    => $bounds['sermon_end_seconds'],
                    'boundary_confidence'   => $bounds['confidence'],
                    'boundary_reason'       => $bounds['reason'],
                    'boundary_source'       => 'auto',
                    'processing_status'     => 'published',
                    'published_at'          => now(),
                ]
            );

            $sermon->qaPairs()->delete();
            foreach ($cleanQAs as $i => $qa) {
                PeaceQaPair::create([
                    'sermon_id'        => $sermon->id,
                    'question'         => $qa['question'],
                    'answer'           => $qa['answer'],
                    'display_order'    => $i,
                    'confidence_score' => $qa['confidence'] ?? null,
                ]);
            }

            $sermon->scriptures()->delete();
            foreach ($validatedScriptures as $i => $s) {
                PeaceScripture::create([
                    'sermon_id'         => $sermon->id,
                    'book'              => $s['book'],
                    'chapter'           => $s['chapter'],
                    'verse_start'       => $s['verse_start'],
                    'verse_end'         => $s['verse_end'],
                    'reference_display' => $s['reference_display'],
                    'verse_text'        => $s['verse_text'],
                    'translation'       => $s['translation'],
                    'validated'         => true,
                    'display_order'     => $i,
                ]);
            }

            $topicIds = [];
            foreach ($content['topics'] ?? [] as $topicName) {
                $topicIds[] = PeaceTopic::fromName($topicName)->id;
            }
            $sermon->topics()->sync($topicIds);

            return $sermon;
        });

        $this->info("✓ Sermon saved as #{$sermon->id} — slug: {$sermon->slug}");
        $this->line("Visit: /find-peace/{$sermon->slug}");
        return self::SUCCESS;
    }

    private function dropQAsWithBadScripture(array $qas, array $validRefs): array
    {
        return array_values(array_filter($qas, function ($qa) use ($validRefs) {
            // If the answer text mentions a scripture pattern (Book Chapter:Verse),
            // ensure that mention is in the validated list. Otherwise drop the Q&A.
            preg_match_all('/\b(\d?\s?[A-Z][a-z]+(?:\s[A-Z][a-z]+)*)\s+(\d+):(\d+)(?:-\d+)?\b/', $qa['answer'] ?? '', $m);
            if (empty($m[0])) return true; // no scripture mentioned; safe
            foreach ($m[0] as $mentioned) {
                $hit = false;
                foreach ($validRefs as $valid) {
                    if (str_starts_with($valid, trim($mentioned)) || str_starts_with(trim($mentioned), $valid)) {
                        $hit = true; break;
                    }
                }
                if (! $hit) return false; // mentioned scripture isn't in validated set; drop
            }
            return true;
        }));
    }

    private function slugify(string $title, string $videoId): string
    {
        $base = Str::slug($title);
        if ($base === '') $base = $videoId;
        return $base . '-' . substr($videoId, 0, 6);
    }

    private function parseUploadDate(?string $ymd): ?Carbon
    {
        if (! $ymd || strlen($ymd) !== 8) return null;
        try {
            return Carbon::createFromFormat('Ymd', $ymd);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
