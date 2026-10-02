<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Game players — Admin</title>
@include('partials.theme-vars')
<style>
*{box-sizing:border-box}body{margin:0;background:var(--parchment);color:var(--ink);font:16px/1.5 system-ui,sans-serif}main{max-width:1120px;margin:auto;padding:30px 22px 80px}a{color:var(--teal)}h1{font-size:36px;margin-bottom:8px}h2{margin:0;font-size:21px;overflow-wrap:anywhere}p{max-width:800px;color:var(--ink-soft)}.note{padding:18px 22px;background:#fff;border:1px solid var(--line);border-radius:14px;margin:24px 0}.players{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px}.player{background:#fff;padding:22px;border:1px solid var(--line);border-radius:14px}.badge{display:inline-block;font-size:12px;font-weight:700;background:#eaf1ef;color:#244f51;padding:5px 10px;border-radius:20px;margin:10px 0}.metrics{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0}.metrics span{display:block;font-size:12px;color:var(--ink-soft)}.metrics b{font-size:21px}.muted{font-size:13px;color:var(--ink-soft)}label{display:block;margin-top:18px;font-weight:600;font-size:13px}select,button{font:inherit;padding:10px;border:1px solid var(--line);border-radius:7px;margin-top:6px;max-width:100%}button{background:var(--teal);color:white;cursor:pointer}nav{margin-top:25px}svg{width:20px}summary{cursor:pointer;font-weight:600}*:focus-visible{outline:3px solid var(--teal);outline-offset:3px}.flash{padding:12px;background:#eaf1ef}
</style></head><body><main>
<a href="{{ route('admin.games') }}">← Scripture games</a>
<h1>Who is playing?</h1><p>Quiet evidence from play, with staff judgment when you recognize someone. A nickname is not a verified identity, and one browser profile may be shared.</p>
@if(session('status'))<div class="flash" role="status">{{ session('status') }}</div>@endif
<div class="note"><details><summary>How to read the evidence</summary>
<p><b>Likely player:</b> a completed game with at least 3 valid actions and 10 seconds of measured activity, or 12 actions over 30 seconds with some progress. Long idle gaps do not count. This is an estimate; automation can imitate play.</p>
<p><b>Needs review:</b> at least 5 invalid moves, as many as valid moves. Network or client bugs can also cause this. It does not automatically block a player.</p>
<p><b>Known to the team:</b> only use when you already know who this is. Children are never asked to prove it. <b>Test / Excluded:</b> stays out of public celebrations.</p>
<p>Sessions and timing cover the last 90 days. We store no additional IP addresses, device fingerprints, location, or raw input recordings for this feature. Tetris line clears are reported by the game and checked for basic limits; the board is not replayed on the server.</p>
<p>Checked stars start with this update. Historical stars are preserved for reference and may include the old early-award bug. Replaying a level earns at most 3 checked stars per level.</p>
</details></div>
<div class="players">
@forelse($players as $p)
<article class="player"><h2>{{ $p->name }}</h2><div class="badge">{{ $p->evidence[0] }}</div><p class="muted">{{ $p->evidence[1] }}</p>
<div class="metrics"><div><b>{{ $p->runs_count }}</b><span>Sessions</span></div><div><b>{{ $p->completed_runs_count }}</b><span>Completed sessions</span></div><div><b>{{ $p->checked_stars ?? 0 }}</b><span>Checked stars</span></div><div><b>{{ $p->runs_sum_actions ?? 0 }}</b><span>Valid actions</span></div></div>
<p class="muted">Play days: {{ $p->play_days }}<br>Measured activity: {{ (int)($p->runs_sum_active_seconds ?? 0) }} seconds<br>Invalid moves: {{ $p->runs_sum_invalid_actions ?? 0 }}<br>Last played: {{ $p->runs_max_last_action_at ? \Carbon\Carbon::parse($p->runs_max_last_action_at)->format('M j, Y · g:i a T') : 'No measured play yet' }}<br>Historical stars: {{ $p->total_stars }}</p>
<form method="POST" action="{{ route('admin.games.players.update',$p) }}">@csrf @method('PATCH')
<label for="review-{{ $p->id }}">Team review</label><select name="review_status" id="review-{{ $p->id }}">
@foreach(['auto'=>'Use activity estimate','known'=>'Known to the team','test'=>'Test account','exclude'=>'Exclude from public list'] as $value=>$label)<option value="{{ $value }}" @selected($p->review_status===$value)>{{ $label }}</option>@endforeach
</select><button type="submit">Save</button></form></article>
@empty<p>No player profiles yet.</p>@endforelse
</div>{{ $players->links() }}
</main></body></html>
