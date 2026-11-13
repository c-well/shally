<!doctype html>
<html lang="en">
<head>
@include('peace-news._head')
@include('partials.seo-head', [
  'title'       => $sermon->title . ' · Peace Notes News · The Church of Peace',
  'description' => \Illuminate\Support\Str::limit($sermon->heart_line ?: 'This week’s message in two minutes, and a question to carry with you.', 155),
  'path'        => '/peace-notes/news/' . $issue->slug,
])
<style>
  .pn-q { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
  .pn-q.n2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  @media (max-width: 820px) { .pn-q, .pn-q.n2 { grid-template-columns: minmax(0, 1fr); } }
  .pn-row { display: flex; flex-wrap: wrap; gap: 14px; }
  .pn-row > div { flex: 1 1 220px; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
  .pn-dark label { font-size: 15px; font-weight: 500; }
</style>
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

@if ($preview)
  <div style="background: #fff6e6; color: #4a3a1a; text-align: center; padding: 10px 16px; font-size: 14px;">Preview for staff. This issue is <strong>{{ $issue->status }}</strong> and not public yet.</div>
@endif

<main>
  <section aria-labelledby="t" style="padding: clamp(56px, 8vw, 104px) 0 clamp(40px, 5vw, 56px); text-align: center;">
    <div class="pn-col">
      <p style="margin: 0 0 18px; font-size: 15px; font-weight: 500; color: var(--teal);"><a href="{{ route('peace.news.index') }}" style="text-decoration: none;">Peace Notes News</a> · {{ $issue->send_after->timezone('America/New_York')->format('l, F j') }}</p>
      <h1 id="t" class="pn-display">{{ $sermon->title }}</h1>
      @if ($sermon->heart_line)<p class="pn-sub" style="color: #334455;">{{ $sermon->heart_line }}</p>@endif
      <p style="margin: 18px 0 0; font-size: 15px; color: #5f6876;">{{ $sermon->speaker }} · Sabbath, {{ \Illuminate\Support\Carbon::parse($sermon->sermon_date)->format('F j') }}</p>
      <div class="pn-btns" style="display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; margin-top: 28px;">
        @if ($sermon->youtube_video_id)
          <a class="pn-btn" href="{{ $issue->youtubeAt($issue->opening_seconds) }}" target="_blank" rel="noopener"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"></path></svg>Watch the sermon</a>
        @endif
        <a class="pn-btn pn-btn--ghost" href="{{ route('peace.news.index') }}">Past issues</a>
      </div>
    </div>
  </section>

  <section aria-label="Summary" style="background: #fff; padding: clamp(56px, 7vw, 88px) 0;">
    <div class="pn-col" style="display: flex; flex-direction: column; gap: 20px; font-size: 18px; line-height: 1.7;">
      @foreach ((array) $sermon->summary_paragraphs as $p)
        <p style="margin: 0;">{{ $p }}</p>
      @endforeach
      @if ($sermon->scriptures->count())
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;" aria-label="Scriptures">
          @foreach ($sermon->scriptures->take(6) as $s)
            <span style="padding: 6px 12px; border-radius: 6px; background: var(--parchment); color: var(--teal); font-size: 14px;">{{ $s->reference_display }}</span>
          @endforeach
        </div>
      @endif
    </div>
  </section>

  <section aria-labelledby="ask" style="padding: clamp(64px, 8vw, 104px) clamp(20px, 5vw, 48px);">
    <div style="max-width: 1040px; margin: 0 auto;">
      <h2 id="ask" class="pn-sec" style="text-align: center;">This week, ask yourself.</h2>
      <p class="pn-sub" style="text-align: center; margin-bottom: 40px;">Questions to carry from Sabbath into the week.</p>
      <div class="pn-q {{ count($issue->questions) === 2 ? 'n2' : '' }}">
        @foreach ($issue->questions as $q)
          <div style="background: #fff; border-radius: 14px; padding: 28px 24px; font-size: 19px; line-height: 1.5; font-weight: 500; letter-spacing: -0.01em;">{{ $q }}</div>
        @endforeach
      </div>
    </div>
  </section>

  <section id="spoke" class="pn-dark" aria-labelledby="spoke-t" style="background: #2a1f3a; color: #fff; padding: clamp(64px, 8vw, 104px) 0;">
    <div class="pn-col">
      <h2 id="spoke-t" class="pn-sec" style="text-align: center;">What spoke to you?</h2>
      <p class="pn-sub" style="text-align: center; color: rgba(255,255,255,.78); margin-bottom: 32px;">Answer one of the questions, or share what stayed with you. A few answers open next Sunday’s issue.</p>
      @if (session('news_flash'))
        <p role="status" style="margin: 0 0 20px; padding: 14px 16px; border-radius: 8px; background: rgba(255,255,255,.1); font-size: 16px;">{{ session('news_flash') }}</p>
      @endif
      @if ($preview)
        <p style="text-align: center; color: rgba(255,255,255,.7);">The answer box opens when the issue goes out.</p>
      @else
      <form method="post" action="{{ route('peace.news.answer', $issue->slug) }}" style="display: flex; flex-direction: column; gap: 14px;">
        @csrf
        <input type="hidden" name="form_opened_at" value="0" id="pn-opened">
        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position: absolute; left: -9999px; width: 1px; height: 1px;">
        <label for="ans">Your answer</label>
        <textarea id="ans" name="body" class="pn-field" rows="5" required maxlength="2000">{{ old('body') }}</textarea>
        @error('body')<p style="margin: 0; color: #ffd0d0; font-size: 14px;">{{ $message }}</p>@enderror
        <div class="pn-row">
          <div>
            <label for="nm">First name (optional)</label>
            <input id="nm" name="first_name" class="pn-field" type="text" maxlength="40" autocomplete="given-name" value="{{ old('first_name') }}">
          </div>
          @unless ($seeker)
            <div>
              <label for="em">Email</label>
              <input id="em" name="email" class="pn-field" type="email" required autocomplete="email" value="{{ old('email') }}" aria-describedby="em-hint">
              <span id="em-hint" style="font-size: 13px; font-weight: 300; color: rgba(255,255,255,.7);">We send a link to post it. No password.</span>
            </div>
          @endunless
        </div>
        @error('email')<p style="margin: 0; color: #ffd0d0; font-size: 14px;">{{ $message }}</p>@enderror
        <label style="display: flex; gap: 10px; align-items: flex-start; font-weight: 300; color: rgba(255,255,255,.85);"><input type="hidden" name="share_ok" value="0"><input type="checkbox" name="share_ok" value="1" checked style="width: 20px; height: 20px; margin: 2px 0 0; flex: none;"> It’s fine to share my answer in next week’s issue, first name only.</label>
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 6px;">
          <button type="submit" class="pn-btn" style="background: #fff; color: #1a2332; border-color: #fff;">Send my answer</button>
          <span style="font-size: 14px; font-weight: 300; color: rgba(255,255,255,.7);">Answers show after a quick read by the church.</span>
        </div>
      </form>
      <script>document.getElementById('pn-opened').value = Date.now();</script>
      @endif
    </div>
  </section>

  @if ($youSaid->count())
    <section aria-labelledby="said" style="padding: clamp(64px, 8vw, 104px) 0; background: #fff;">
      <div class="pn-col">
        <h2 id="said" class="pn-sec" style="font-size: clamp(26px, 2.8vw, 34px); margin-bottom: 28px;">You said, last week.</h2>
        <div style="display: flex; flex-direction: column; gap: 16px;">
          @foreach ($youSaid as $a)
            <figure style="margin: 0; background: var(--parchment); border-radius: 14px; padding: 24px 26px;">
              <blockquote style="margin: 0; font-size: 18px; line-height: 1.6; font-weight: 300;">{{ $a->body }}</blockquote>
              <figcaption style="margin-top: 12px; font-size: 14px; color: #5f6876;">{{ $a->first_name ?: 'A reader' }}</figcaption>
            </figure>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @include('peace.partials.news-signup')
</main>

@include('partials.footer')
@include('partials._event-tracker')
</body>
</html>
