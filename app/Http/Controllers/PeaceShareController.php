<?php
namespace App\Http\Controllers;

use App\Models\PeaceSermon;
use App\Models\PeaceTopic;
use App\Models\PeaceUserSubmission;
use App\Services\Peace\ResendMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * /find-peace/share — verified seeker submits their own question or experience.
 * Both types route to clerk/pastor for moderation before any public display.
 */
class PeaceShareController extends Controller
{
    public function show(Request $request): View
    {
        $seeker = $request->attributes->get('seeker');
        abort_unless($seeker, 401);

        return view('find-peace.share', [
            'seeker'  => $seeker,
            'sermons' => PeaceSermon::whereNotNull('published_at')
                            ->where('is_offsite', false)
                            ->where('is_no_sermon', false)
                            ->orderByDesc('sermon_date')->limit(20)
                            ->get(['id', 'slug', 'title', 'sermon_date']),
            'topics'  => PeaceTopic::has('sermons')->orderBy('name')->get(['id', 'name', 'slug']),
            'recent'  => PeaceUserSubmission::where('subscriber_id', $seeker->id)
                            ->orderByDesc('created_at')->limit(5)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $seeker = $request->attributes->get('seeker');
        abort_unless($seeker, 401);

        // Honeypot
        if (filled($request->input('website'))) {
            \App\Models\AuditLog::record(
                event: 'peace_share_honeypot',
                description: 'Honeypot tripped on /find-peace/share by subscriber #' . $seeker->id,
            );
            return back()->with('peace_flash', 'Thank you — we received your submission.');
        }

        // Time-trap
        $openedAt = (int) $request->input('form_opened_at', 0);
        if ($openedAt > 0 && (time() * 1000 - $openedAt) < 3000) {
            \App\Models\AuditLog::record(
                event: 'peace_share_timetrap',
                description: 'Form submitted in <3 sec by subscriber #' . $seeker->id,
            );
            return back()->with('peace_flash', 'Thank you — we received your submission.');
        }

        $data = $request->validate([
            'type'                => 'required|in:question,experience',
            'body'                => 'required|string|min:20|max:3000',
            'related_sermon_id'   => 'nullable|integer|exists:peace_sermons,id',
            'related_topic_id'    => 'nullable|integer|exists:peace_topics,id',
            'attribution'         => 'required|in:anonymous,first_name_city',
            'attribution_display' => 'nullable|string|max:100',
        ]);

        // Rate limits
        $subKey = 'peace-share-sub:' . $seeker->id;
        $ipKey  = 'peace-share-ip:'  . $request->ip();
        if (RateLimiter::tooManyAttempts($subKey, 5) || RateLimiter::tooManyAttempts($ipKey, 3)) {
            return back()->with('peace_flash', 'You\'ve sent a few recently. Please wait a day before sending more.');
        }
        RateLimiter::hit($subKey, 86400);
        RateLimiter::hit($ipKey, 86400);

        // Sanitize attribution_display — only used when attribution=first_name_city
        $attrDisplay = $data['attribution'] === 'first_name_city'
            ? mb_substr(trim((string) ($data['attribution_display'] ?? '')), 0, 100) ?: null
            : null;

        $sub = PeaceUserSubmission::create([
            'subscriber_id'       => $seeker->id,
            'type'                => $data['type'],
            'body'                => $data['body'],
            'related_sermon_id'   => $data['related_sermon_id'] ?? null,
            'related_topic_id'    => $data['related_topic_id']  ?? null,
            'attribution'         => $data['attribution'],
            'attribution_display' => $attrDisplay,
            'status'              => 'pending',
            'ip_hash'             => hash('sha256', $request->ip() . config('app.key')),
            'ua_hash'             => hash('sha256', (string) $request->userAgent() . config('app.key')),
        ]);

        // Notify pastoral team (sendmail — internal/admin email channel)
        try {
            $bodyLines  = "New " . $data['type'] . " from a seeker on Finding Peace\n";
            $bodyLines .= str_repeat('─', 60) . "\n";
            $bodyLines .= "Type:        " . $data['type'] . "\n";
            $bodyLines .= "From:        " . $seeker->email . " (subscriber #" . $seeker->id . ")\n";
            $bodyLines .= "Attribution: " . $data['attribution'];
            if ($attrDisplay) $bodyLines .= " — \"" . $attrDisplay . "\"";
            $bodyLines .= "\n";
            if ($sub->sermon) $bodyLines .= "Related sermon: " . $sub->sermon->title . "\n";
            if ($sub->topic)  $bodyLines .= "Related topic:  " . $sub->topic->name . "\n";
            $bodyLines .= "Submitted:   " . $sub->created_at->toDayDateTimeString() . "\n";
            $bodyLines .= str_repeat('─', 60) . "\n\n";
            $bodyLines .= $data['body'] . "\n\n";
            $bodyLines .= str_repeat('─', 60) . "\n";
            $bodyLines .= "Review + reply: " . url('/admin/peace/submissions') . "\n";

            Mail::raw($bodyLines, function ($m) use ($data, $seeker) {
                $m->to('andre.marshall@gmail.com')
                  ->cc('contact@c-wellpics.com')
                  ->subject('[Finding Peace] New ' . $data['type'] . ' from ' . $seeker->email);
            });
        } catch (\Throwable $e) {
            \App\Models\AuditLog::record(
                event: 'peace_share_email_failed',
                description: 'Email send failed for submission #' . $sub->id . ': ' . $e->getMessage(),
            );
        }

        \App\Models\AuditLog::record(
            event: 'peace_share_submitted',
            userId: null,
            description: 'New ' . $data['type'] . ' from seeker #' . $seeker->id . ' (submission #' . $sub->id . ')',
            meta: ['submission_id' => $sub->id, 'type' => $data['type']],
        );

        $thanks = $data['type'] === 'question'
            ? 'Thank you. A pastor will see your question and may follow up by email.'
            : 'Thank you for sharing. Your experience is being read — and may be shared with the community if you allowed it.';

        return redirect()->route('peace.share.show')->with('peace_flash', $thanks);
    }
}
