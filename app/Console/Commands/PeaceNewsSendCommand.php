<?php

namespace App\Console\Commands;

use App\Models\PeaceNewsIssue;
use App\Models\PeaceSubscriber;
use App\Services\Peace\ResendMailer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

/**
 * Sends approved Peace Notes News to subscribers once its send time arrives
 * (Sunday 8 AM ET; right away if it was approved after that). Runs every 10
 * minutes. An issue is marked sent BEFORE the loop so a crash can never send
 * it twice.
 *
 *   --to=email   send one copy to that address only, any status, marks nothing
 */
class PeaceNewsSendCommand extends Command
{
    protected $signature = 'peace:news-send {--to= : Send a single test copy to this address}';
    protected $description = 'Email approved Peace Notes News to subscribers';

    public function handle(ResendMailer $mailer): int
    {
        if ($to = $this->option('to')) {
            $issue = PeaceNewsIssue::with('sermon')->orderByDesc('id')->first();
            if (! $issue) { $this->error('No issue yet.'); return self::FAILURE; }
            $ok = $this->sendOne($mailer, $issue, $to, null, '[Test] ');
            $this->info($ok ? "Test copy sent to {$to}" : 'Send failed (see log)');
            return $ok ? self::SUCCESS : self::FAILURE;
        }

        $issue = PeaceNewsIssue::with('sermon')->where('status', 'approved')
            ->whereNull('sent_at')->where('send_after', '<=', now())
            ->orderBy('send_after')->first();
        if (! $issue) return self::SUCCESS;

        $issue->forceFill(['status' => 'sent', 'sent_at' => now()])->save();

        $count = 0;
        PeaceSubscriber::whereNotNull('email_verified_at')->where('subscribed_newsletter', true)
            ->orderBy('id')->chunk(100, function ($subs) use ($mailer, $issue, &$count) {
                foreach ($subs as $s) {
                    if ($this->sendOne($mailer, $issue, $s->email, $s)) $count++;
                    usleep(600000); // stay under the mail service's rate limit
                }
            });

        $issue->forceFill(['sent_count' => $count])->save();
        \App\Models\AuditLog::record(event: 'peace_news_sent', description: "Peace Notes News \"{$issue->sermon->title}\" sent to {$count} subscribers");
        $this->info("Sent to {$count}");
        return self::SUCCESS;
    }

    private function sendOne(ResendMailer $mailer, PeaceNewsIssue $issue, string $email, ?PeaceSubscriber $sub, string $prefix = ''): bool
    {
        $unsub = $sub
            ? URL::signedRoute('peace.news.unsubscribe', ['subscriber' => $sub->id])
            : url('/peace-notes/news');
        return $mailer->sendView(
            to: $email,
            subject: $prefix . 'Peace Notes News: ' . $issue->sermon->title,
            view: 'peace.emails.news-issue',
            viewData: [
                'issue'     => $issue,
                'sermon'    => $issue->sermon,
                'issueUrl'  => route('peace.news.show', $issue->slug) . '?utm_source=newsletter',
                'answerUrl' => route('peace.news.show', $issue->slug) . '?utm_source=newsletter#spoke',
                'youSaid'   => $issue->youSaid(),
                'unsubUrl'  => $unsub,
            ],
            opts: ['headers' => ['List-Unsubscribe' => '<' . $unsub . '>']],
        );
    }
}
