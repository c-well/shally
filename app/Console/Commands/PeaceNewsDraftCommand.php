<?php

namespace App\Console\Commands;

use App\Models\PeaceNewsIssue;
use App\Models\PeaceSermon;
use App\Services\Peace\PeaceNewsGenerator;
use App\Services\Peace\TranscriptFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Drafts this week's Peace Notes News from the newest published sermon and
 * emails Andre (cc KC) the two-minute review. Runs hourly; a no-op unless a
 * sermon from the last 8 days has no issue yet. Nothing reaches subscribers
 * until someone approves (first approval sends, Sunday 8 AM ET).
 */
class PeaceNewsDraftCommand extends Command
{
    protected $signature = 'peace:news-draft
        {--sermon= : Slug of a specific sermon (ignores the 8-day window)}
        {--dry-run : Generate and print, write nothing, send nothing}
        {--no-email : Create the draft but do not send the review email}';

    protected $description = 'Draft Peace Notes News from the latest sermon and send the review email';

    public const REVIEW_TO = 'andre.marshall@gmail.com';
    public const REVIEW_CC = 'contact@c-wellpics.com';

    public function handle(TranscriptFetcher $fetcher, PeaceNewsGenerator $generator): int
    {
        $q = PeaceSermon::query()->whereNotNull('published_at')->whereNull('discarded_at')
            ->where('is_no_sermon', false)
            ->whereNotIn('id', PeaceNewsIssue::query()->select('sermon_id'));
        if ($slug = $this->option('sermon')) {
            $q->where('slug', $slug);
        } else {
            $q->where('sermon_date', '>=', now()->subDays(8));
        }
        $sermon = $q->orderByDesc('sermon_date')->first();
        if (! $sermon) {
            $this->line('Nothing to draft.');
            return self::SUCCESS;
        }

        // Only a sermon whose Sunday is still ahead. A sermon whose Sunday has
        // passed is old news; it can still be drafted by hand with --sermon.
        $sunday = self::sendSunday($sermon);
        if (! $this->option('sermon') && $sunday->lt(now()->subHours(12))) {
            $this->line("Skipping {$sermon->title}: its Sunday ({$sunday->format('M j')}) has passed.");
            return self::SUCCESS;
        }

        $failKey = 'peace-news-fail:' . $sermon->id;
        if (! $this->option('sermon') && Cache::get($failKey, 0) >= 3) {
            return self::SUCCESS; // already alerted; waits for a manual run
        }

        $this->info("Drafting from #{$sermon->id} {$sermon->title}");

        // The preacher's own words, for the preview. Captions are re-fetched
        // because the pipeline does not keep the transcript.
        $opening = $closing = null;
        $lines = $sermon->youtube_video_id ? $fetcher->getTranscript($sermon->youtube_video_id) : null;
        $start = (int) ($sermon->sermon_start_seconds ?? 0);
        $end   = (int) ($sermon->sermon_end_seconds ?? 0);
        if ($lines && $end > $start) {
            $opening = $this->words($fetcher->formatForPrompt($lines, $start, min($end, $start + 240), false), 700, false);
            $closing = $this->words($fetcher->formatForPrompt($lines, max($start, $end - 720), $end, false), 1400, true);
        }
        $this->line('  transcript: ' . ($lines ? count($lines) . ' lines' : 'not available'));

        $out = $generator->generate([
            'title'      => $sermon->title,
            'speaker'    => $sermon->speaker,
            'heart_line' => $sermon->heart_line,
            'summary'    => (array) $sermon->summary_paragraphs,
        ], $opening, $closing);

        if (! $out || count($out['questions']) < 2) {
            Cache::add($failKey, 0, now()->addDays(8));
            $n = (int) Cache::increment($failKey);
            $this->error('  generation failed (attempt ' . $n . ')');
            if ($n === 3 && ! $this->option('dry-run')) {
                Mail::raw("Peace Notes News could not be drafted for \"{$sermon->title}\" after 3 tries.\n\nRun by hand: php artisan peace:news-draft --sermon={$sermon->slug}\n", function ($m) {
                    $m->to(self::REVIEW_CC)->subject('[Shalom] Peace Notes News draft failed');
                });
            }
            return self::FAILURE;
        }
        if (! $lines) {
            $out['checks'][] = 'The transcript could not be fetched, so the preview has no quotes. Listen to the opening and close before approving.';
        }

        foreach ($out['questions'] as $i => $qq) $this->line('  Q' . ($i + 1) . ': ' . $qq);
        $this->line('  opens:  ' . ($out['opening_quote'] ?? '(no verified quote)'));
        $this->line('  closes: ' . ($out['closing_quote'] ?? '(no verified quote)'));
        foreach ($out['checks'] as $c) $this->line('  check: ' . $c);
        if ($this->option('dry-run')) {
            $this->info('DRY RUN: nothing written.');
            return self::SUCCESS;
        }

        // Sunday 8 AM ET after the sermon. If that has already passed (a late
        // sermon), it goes out as soon as someone approves.
        $sendAfter = self::sendSunday($sermon);

        $issue = PeaceNewsIssue::create([
            'sermon_id'       => $sermon->id,
            'slug'            => $sermon->slug,
            'status'          => 'draft',
            'questions'       => $out['questions'],
            'opening_summary' => $out['opening_summary'],
            'opening_quote'   => $out['opening_quote'],
            'opening_seconds' => $start ?: null,
            'closing_summary' => $out['closing_summary'],
            'closing_quote'   => $out['closing_quote'],
            'closing_seconds' => $end > $start ? max($start, $end - 480) : null,
            'checks'          => $out['checks'],
            'review_token'    => bin2hex(random_bytes(32)),
            'send_after'      => $sendAfter,
        ]);
        Cache::forget($failKey);
        \App\Models\AuditLog::record(event: 'peace_news_drafted', description: "Peace Notes News drafted: {$sermon->title}");

        if (! $this->option('no-email')) {
            self::sendReview($issue);
            $this->info('  review email sent');
        }
        $this->info("Issue #{$issue->id} drafted; sends " . $sendAfter->format('D M j g:i A') . ' once approved.');
        return self::SUCCESS;
    }

    public static function sendReview(PeaceNewsIssue $issue): void
    {
        $issue->loadMissing('sermon');
        $pending = \App\Models\PeaceNewsAnswer::where('status', 'pending')->count();
        Mail::send('peace.emails.news-review', [
            'issue'      => $issue,
            'sermon'     => $issue->sermon,
            'reviewUrl'  => route('peace.news.review', $issue->review_token),
            'editUrl'    => route('admin.peace.news.edit', $issue),
            'answersUrl' => route('admin.peace.news.index') . '#answers',
            'pending'    => $pending,
        ], function ($m) use ($issue) {
            $m->to(self::REVIEW_TO)->cc(self::REVIEW_CC)
              ->subject('Peace Notes News is ready to approve: ' . $issue->sermon->title);
        });
        $issue->forceFill(['review_sent_at' => now()])->save();
    }

    /** Sunday 8 AM ET for this sermon. The stored date is often YouTube's
     *  upload day (the Sunday after Sabbath), so step back one day first:
     *  a Saturday or Sunday date both land on that same Sunday. */
    public static function sendSunday(PeaceSermon $sermon): Carbon
    {
        return Carbon::parse($sermon->sermon_date)->timezone('America/New_York')->startOfDay()
            ->subDay()->next(Carbon::SUNDAY)->setTime(8, 0);
    }

    /** First (or last) N words of a text. */
    private function words(string $text, int $n, bool $fromEnd): string
    {
        $w = preg_split('/\s+/', trim($text));
        return implode(' ', $fromEnd ? array_slice($w, -$n) : array_slice($w, 0, $n));
    }
}
