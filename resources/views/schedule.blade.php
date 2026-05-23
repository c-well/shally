<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Department Schedule · Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
<style>  :root { --paper:#fff; --gold:#b89a4a; --past:#c9c2af; }
  *{box-sizing:border-box; margin:0; padding:0;} html,body{overflow-x:hidden; max-width:100%; background:var(--cream);}
  body{
    color:var(--ink); font-family:'Poppins',ui-sans-serif,sans-serif; font-size:16px; line-height:1.5;
    -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    text-rendering:optimizeLegibility;
  }
  a{color:inherit;}
  .wrap{max-width:1100px; margin:0 auto; padding:24px 18px 80px;}
  .topbar{
    display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;
    padding:18px 20px; background:var(--paper); border:1px solid var(--line); border-radius:6px;
  }
  .topbar-title{font-family:'Cormorant Garamond',serif; font-size:26px; font-weight:500; line-height:1.05;}
  .topbar-title small{display:block; font-family:'Instrument Sans',sans-serif; font-size:9px; letter-spacing:0.22em; text-transform:uppercase; color:var(--ink-soft); font-weight:600; margin-top:6px;}
  .home-link{font-family:'Instrument Sans',sans-serif; font-size:11px; letter-spacing:0.18em; text-transform:uppercase; color:var(--ink-soft); font-weight:600; text-decoration:none; padding:8px 14px; border:1px solid var(--line); border-radius:4px;}
  .home-link:hover{color:var(--teal); border-color:var(--teal);}

  .dept-tabs{display:flex; flex-wrap:wrap; gap:6px; margin-top:18px;}
  .dept-tab{
    appearance:none; border:1px solid var(--line); background:var(--paper); color:var(--ink-soft);
    font-family:'Instrument Sans',sans-serif; font-size:12px; letter-spacing:0.12em; text-transform:uppercase;
    font-weight:600; padding:11px 18px; border-radius:999px; cursor:pointer;
    transition:all 0.15s; min-height:44px;
  }
  .dept-tab:hover{color:var(--teal); border-color:var(--teal);}
  .dept-tab.active{background:var(--teal); color:#fff; border-color:var(--teal);}

  .month-nav{display:flex; align-items:center; justify-content:space-between; margin:22px 4px 14px;}
  .arrow{
    width:44px; height:44px; border-radius:50%; border:1px solid var(--line); background:var(--paper);
    display:flex; align-items:center; justify-content:center; color:var(--ink); cursor:pointer; transition:all 0.15s;
  }
  .arrow:hover{border-color:var(--teal); color:var(--teal);}
  .arrow:disabled{opacity:0.35; cursor:not-allowed;}
  .month-label{font-family:'Cormorant Garamond',serif; font-size:30px; font-weight:500; letter-spacing:-0.02em; line-height:1; text-align:center;}
  .month-label small{display:block; font-family:'Instrument Sans',sans-serif; font-size:9px; letter-spacing:0.22em; text-transform:uppercase; color:var(--ink-soft); margin-top:5px;}

  .names-card{background:var(--paper); border:1px solid var(--line); border-radius:6px; padding:18px 20px; margin-bottom:20px;}
  .names-label{font-family:'Instrument Sans',sans-serif; font-size:10px; letter-spacing:0.22em; text-transform:uppercase; color:var(--ink-soft); font-weight:600; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;}
  .names-label .manage-btn{
    background:transparent; border:1px solid var(--line); border-radius:4px; padding:6px 12px;
    font:inherit; font-size:9px; letter-spacing:0.18em; color:var(--ink-soft); cursor:pointer; text-transform:uppercase; font-weight:600;
  }
  .names-label .manage-btn:hover{color:var(--teal); border-color:var(--teal);}
  .names-strip{display:flex; flex-wrap:wrap; gap:10px;}
  .name-chip{
    display:inline-flex; align-items:center; gap:8px; padding:13px 20px;
    background:var(--teal-light); color:var(--teal); border-radius:4px;
    font-size:16px; font-weight:500; cursor:grab; user-select:none; border:1px solid transparent;
    transition:all 0.15s; min-height:48px;
  }
  .name-chip:hover{background:var(--teal); color:#fff; transform:translateY(-1px);}
  .name-chip:active{cursor:grabbing;}
  .name-chip .grip{opacity:0.4; font-size:11px;}
  .name-chip.add{background:transparent; color:var(--teal); border:1.5px dashed color-mix(in srgb, var(--teal) 40%, transparent); cursor:pointer;}

  .weeks{display:grid; gap:14px; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));}
  .week{
    background:var(--paper); border:1px solid var(--line); border-radius:6px;
    padding:18px 20px; min-height:200px; display:flex; flex-direction:column; gap:14px;
    transition:all 0.15s;
  }
  .week.drop-target{border-color:var(--teal); background:var(--teal-light);}
  .week-head{display:flex; align-items:baseline; justify-content:space-between; border-bottom:1px solid var(--line); padding-bottom:10px;}
  .week-date{font-family:'Cormorant Garamond',serif; font-size:24px; font-weight:500; letter-spacing:-0.02em;}
  .week-date small{display:block; font-family:'Instrument Sans',sans-serif; font-size:9px; letter-spacing:0.22em; text-transform:uppercase; color:var(--ink-soft); margin-top:4px; font-weight:600;}
  .week-tag{font-family:'Instrument Sans',sans-serif; font-size:9px; letter-spacing:0.18em; text-transform:uppercase; font-weight:600; color:var(--gold);}
  .week.locked{background:rgba(0,0,0,0.02);} .week.locked .week-tag{color:var(--past);} .week.locked .empty-slot{display:none;}
  .assignments{display:flex; flex-direction:column; gap:8px; flex:1;}
  .name-chip-assigned{
    display:flex; align-items:center; justify-content:space-between; padding:12px 14px;
    background:var(--teal-light); color:var(--teal); border-radius:4px; font-size:16px; font-weight:500;
    cursor:grab; transition:all 0.15s;
  }
  .name-chip-assigned:hover{background:var(--teal); color:#fff;}
  .name-chip-assigned .x{
    background:transparent; border:0; color:inherit; opacity:0.5; cursor:pointer;
    font-size:18px; padding:0 4px; line-height:1;
  }
  .name-chip-assigned:hover .x{opacity:1;}
  .empty-slot{
    border:1.5px dashed color-mix(in srgb, var(--teal) 25%, transparent); border-radius:4px; padding:18px; text-align:center;
    font-family:'Instrument Sans',sans-serif; font-size:10px; letter-spacing:0.18em; text-transform:uppercase;
    color:var(--ink-soft); font-weight:600;
  }
  .role-row{display:flex; gap:10px; align-items:center; padding:8px 0; border-bottom:1px dashed var(--line);}
  .role-row:last-child{border-bottom:0;}
  .role-label{font-family:'Instrument Sans',sans-serif; font-size:10px; letter-spacing:0.18em; text-transform:uppercase; color:var(--ink-soft); font-weight:600; flex:0 0 130px;}
  .role-row select, .role-row input[type=text]{
    flex:1; padding:11px 12px; border:1px solid var(--line); border-radius:4px; font:inherit; background:#fff; color:var(--ink);
    min-height:44px;
  }
  .role-row select:focus, .role-row input:focus{outline:0; border-color:var(--teal); box-shadow:0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent);}

  /* Previous-weeks collapsible (Anthropic-style list) */
  .prev-weeks{margin-top:36px; padding-top:8px; border-top:1px solid var(--line);}
  .prev-weeks summary{
    list-style:none; cursor:pointer; user-select:none;
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 4px;
  }
  .prev-weeks summary::-webkit-details-marker{display:none;}
  .prev-weeks-label{
    font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:500;
    color:var(--ink); letter-spacing:-0.01em;
  }
  .prev-weeks-chev{
    color:var(--ink-soft); transition:transform 0.25s ease;
    animation:chev-bounce 1.6s ease-in-out infinite;
  }
  .prev-weeks[open] .prev-weeks-chev{transform:rotate(180deg); animation:none; color:var(--teal);}
  .prev-weeks summary:hover .prev-weeks-chev{color:var(--teal);}
  @keyframes chev-bounce{
    0%, 100%{transform:translateY(0);}
    50%{transform:translateY(4px);}
  }
  .prev-weeks-body{padding:6px 0 22px;}
  .past-row{
    display:flex; align-items:baseline; gap:24px;
    padding:18px 4px; border-bottom:1px solid rgba(0,0,0,0.07);
    font-family:'Poppins',sans-serif;
  }
  .past-row:last-child{border-bottom:0;}
  .past-date{
    flex:0 0 130px;
    font-family:'Instrument Sans',sans-serif; font-size:13px; letter-spacing:0.1em;
    text-transform:uppercase; color:var(--ink); font-weight:600;
  }
  .past-names{
    flex:1; color:var(--ink);
    font-size:17px; line-height:1.5; font-weight:400;
    letter-spacing:-0.005em;
  }
  .past-names .role-tag{
    color:var(--ink-soft); font-size:12px; letter-spacing:0.06em;
    margin-left:4px; font-weight:500;
  }
  .past-empty{color:var(--ink-soft); font-style:italic; padding:14px 0;}
  @media (max-width:520px){
    .past-row{flex-direction:column; gap:6px; padding:18px 4px;}
    .past-date{flex:0 0 auto;}
    .past-names{font-size:16px;}
  }

  .saved-toast{
    position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(20px);
    background:var(--ink); color:#fff; padding:11px 18px; border-radius:6px;
    font-family:'Instrument Sans',sans-serif; font-size:12px; letter-spacing:0.04em;
    opacity:0; visibility:hidden; transition:all 0.2s; z-index:200;
  }
  .saved-toast.show{opacity:1; visibility:visible; transform:translateX(-50%) translateY(0);}

  .modal{position:fixed; inset:0; background:color-mix(in srgb, var(--ink) 55%, transparent); display:none; align-items:center; justify-content:center; padding:24px; z-index:300;}
  .modal.open{display:flex;}
  .modal-card{background:#fff; border-radius:8px; max-width:520px; width:100%; max-height:80vh; overflow-y:auto; padding:22px;}
  .modal-card h3{font-family:'Cormorant Garamond',serif; font-size:24px; font-weight:500; margin-bottom:6px;}
  .modal-card p.help{font-size:13px; color:var(--ink-soft); margin-bottom:14px;}
  .members-list{display:flex; flex-direction:column; gap:6px;}
  .member-row{display:flex; align-items:center; justify-content:space-between; padding:9px 12px; border-radius:4px; background:var(--cream); font-size:14px; gap:8px;}
  .member-row.inactive{opacity:0.6;}
  .member-row .name-text{flex:1; font-weight:500;}
  .member-row button{
    background:transparent; border:1px solid var(--line); border-radius:4px; padding:5px 10px;
    font-family:'Instrument Sans',sans-serif; font-size:9px; letter-spacing:0.18em; text-transform:uppercase; font-weight:600; color:var(--ink-soft); cursor:pointer;
  }
  .member-row button:hover{color:var(--teal); border-color:var(--teal);}
  .member-row button.danger:hover{color:#c0392b; border-color:#c0392b;}
  .member-add{display:flex; gap:8px; margin-top:14px;}
  .member-add input{flex:1; padding:10px 12px; border:1px solid var(--line); border-radius:4px; font:inherit;}
  .member-add button{padding:10px 18px; background:var(--teal); color:#fff; border:0; border-radius:4px; font:inherit; cursor:pointer; font-weight:600;}
  .modal-close{position:absolute; top:14px; right:14px; background:transparent; border:0; font-size:24px; color:var(--ink-soft); cursor:pointer;}

  @media (max-width:700px){
    .wrap{padding:14px 12px 60px;}
    .weeks{grid-template-columns:minmax(0, 1fr);}
    .topbar-title{font-size:22px;}
    .month-label{font-size:24px;}
    .role-row{flex-wrap:wrap;}
    .role-label{flex:0 0 100%;}
  }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div class="wrap">
  <div class="topbar">
    <div class="topbar-title">
      Department Schedule
      <small>Drag a name into a week, or tap to assign</small>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      @auth
        @if(in_array(auth()->user()->role, ['super_admin'], true))
          <a href="{{ route('admin.hub') }}" class="home-link">← Admin</a>
        @endif
      @endauth
      <a href="/" class="home-link">← Bulletin</a>
    </div>
  </div>

  <div class="dept-tabs" id="dept-tabs">
    @foreach ($departments as $d)
      <button class="dept-tab @if($d->id === $deptId) active @endif" data-dept-id="{{ $d->id }}" type="button">{{ $d->name }}</button>
    @endforeach
  </div>

  <div class="month-nav">
    <button class="arrow" id="prev-month" type="button" aria-label="Previous month">
      <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="month-label">
      <span id="month-label-text">…</span>
      <small>Plan up to 4 months ahead</small>
    </div>
    <button class="arrow" id="next-month" type="button" aria-label="Next month">
      <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  </div>

  <div class="names-card" id="names-card">
    <div class="names-label">
      <span id="names-label-text">Members</span>
      <button class="manage-btn" id="open-members-btn" type="button">Manage members</button>
    </div>
    <div class="names-strip" id="names-strip">…</div>
  </div>

  <div class="weeks" id="weeks">…</div>

  <details class="prev-weeks" id="prev-weeks">
    <summary>
      <span class="prev-weeks-label">Previous weeks</span>
      <svg class="prev-weeks-chev" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </summary>
    <div id="prev-weeks-body" class="prev-weeks-body"></div>
  </details>
</div>

<div class="saved-toast" id="saved-toast">Saved</div>

<div class="modal" id="members-modal" role="dialog" aria-hidden="true">
  <div class="modal-card" style="position:relative;">
    <button class="modal-close" id="members-close" type="button" aria-label="Close">×</button>
    <h3 id="members-modal-title">Manage members</h3>
    <p class="help">Add or remove people from this department's roster.</p>
    <div class="members-list" id="members-list">…</div>
    <div class="member-add">
      <input type="text" id="member-add-name" placeholder="New member name" maxlength="180">
      <button type="button" id="member-add-btn">Add</button>
    </div>
  </div>
</div>

<script>
(function(){
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  let state = {
    deptId: {{ $deptId ?? 'null' }},
    monthIso: {!! json_encode($monthIso) !!},
    data: null,
  };

  const params = new URLSearchParams(location.search);
  if (params.get('dept')) state.deptId = parseInt(params.get('dept'));
  if (params.get('month')) state.monthIso = params.get('month');

  function showSaved(text){
    const t = document.getElementById('saved-toast');
    t.textContent = text || 'Saved';
    t.classList.add('show');
    clearTimeout(t._t); t._t = setTimeout(() => t.classList.remove('show'), 1400);
  }
  async function api(method, url, body){
    const opts = { method, credentials:'same-origin', headers:{ 'Accept':'application/json', 'X-CSRF-TOKEN':CSRF } };
    if (body !== undefined) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    const res = await fetch(url, opts);
    if (!res.ok) { showSaved('Error'); throw new Error(res.status); }
    return res.json();
  }
  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  function formatDateLong(iso){ const d = new Date(iso + 'T00:00:00'); return d.toLocaleDateString('en-US', { month:'short', day:'numeric' }); }
  function isPast(iso){ const d = new Date(iso + 'T23:59:59'); return d < new Date(); }

  async function load(){
    const url = '/api/schedule/' + state.deptId + '?month=' + encodeURIComponent(state.monthIso);
    state.data = await api('GET', url);
    render();
    history.replaceState(null, '', '/schedule?dept=' + state.deptId + '&month=' + state.monthIso);
  }

  function render(){
    const d = state.data;
    document.getElementById('month-label-text').textContent = d.month_label;
    document.getElementById('names-label-text').textContent = d.department.name + ' — drag a name into a week';
    renderNamesStrip();
    renderWeeks();
    document.querySelectorAll('.dept-tab').forEach(t => t.classList.toggle('active', parseInt(t.dataset.deptId) === state.deptId));
  }

  function renderNamesStrip(){
    const strip = document.getElementById('names-strip');
    strip.innerHTML = state.data.members.map(m => `<span class="name-chip" data-member-id="${m.id}" draggable="true"><span class="grip">⋮⋮</span> ${escapeHtml(m.name)}</span>`).join('');
    if (state.data.is_platform_party) {
      strip.style.opacity = '0.55';
      document.getElementById('names-label-text').textContent = state.data.department.name + ' — assign by role per week';
    } else {
      strip.style.opacity = '';
    }
  }

  function destroySortable(el){
    if (!el || !window.Sortable) return;
    const inst = window.Sortable.get(el);
    if (inst) inst.destroy();
  }

  function enableNameDrag(){
    if (!window.Sortable) return;
    if (state.data.is_platform_party) return;

    const strip = document.getElementById('names-strip');
    destroySortable(strip);
    new window.Sortable(strip, {
      group: { name:'names', pull:'clone', put:false },
      animation:150, sort:false, draggable:'.name-chip',
    });

    document.querySelectorAll('.assignments').forEach(z => {
      destroySortable(z);
      const weekDate = z.dataset.weekDate;
      const past = isPast(weekDate);
      new window.Sortable(z, {
        group: { name:'names', pull:false, put: !past },
        animation:150, draggable:'.name-chip-assigned',
        onAdd: async (evt) => {
          const memberId = parseInt(evt.item.dataset.memberId);
          // Always remove the dropped clone immediately — the server is the source of truth,
          // load() will re-render whatever the canonical state is. Keeping the clone causes
          // visual duplicates if the user drops the same name several times in quick succession.
          evt.item.remove();
          if (!memberId || !weekDate || past) return;
          // Client-side dedupe: if this member is already assigned to this week in current state,
          // skip the API call. Server also dedupes as a safety net.
          const alreadyAssigned = (state.data.assignments || []).some(a =>
            a.week_date === weekDate && a.member_id === memberId
          );
          if (alreadyAssigned) { showSaved('Already assigned'); return; }
          try {
            await api('POST', '/api/schedule/assignments', {
              department_id: state.data.department.id, week_date: weekDate, member_id: memberId,
            });
            showSaved('Assigned');
            await load();
          } catch (e) { console.error('assign failed', e); showSaved('Failed'); }
        },
      });
    });
  }

  function renderWeeks(){
    // Only render current + future Sabbaths as editable tiles. Past weeks are intentionally hidden
    // to avoid confusing the clerk; she can navigate to a past month via the arrows if she really needs them.
    const future = state.data.saturdays.filter(d => !isPast(d));
    const tilesHtml = future.map(weekDate => {
      const weekAssigns = state.data.assignments.filter(a => a.week_date === weekDate);
      const dateNum = new Date(weekDate + 'T00:00:00').getDate();
      const monShort = new Date(weekDate + 'T00:00:00').toLocaleDateString('en-US', { month:'short' });
      return `<div class="week">
        <div class="week-head">
          <div class="week-date">${monShort} ${dateNum}<small>Saturday</small></div>
        </div>
        <div class="assignments" data-week-date="${weekDate}">
          ${state.data.is_platform_party ? renderRoleRows(weekDate, weekAssigns, false) : renderNameRows(weekAssigns, false)}
        </div>
      </div>`;
    }).join('');
    document.getElementById('weeks').innerHTML = tilesHtml || '<p style="text-align:center; color:var(--ink-soft); font-style:italic; padding:30px;">No upcoming Sabbaths in this month — try the next month.</p>';

    // Past Sabbaths in this month — render compactly inside the collapsible <details>
    const past = state.data.saturdays.filter(d => isPast(d));
    const prevWrap = document.getElementById('prev-weeks');
    const prevBody = document.getElementById('prev-weeks-body');
    if (past.length === 0) {
      prevWrap.style.display = 'none';
    } else {
      prevWrap.style.display = '';
      prevBody.innerHTML = past.map(weekDate => {
        const weekAssigns = state.data.assignments.filter(a => a.week_date === weekDate);
        const dateLabel = new Date(weekDate + 'T00:00:00').toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric' });
        return `<div class="past-row"><span class="past-date">${dateLabel}</span><span class="past-names">${renderPastSummary(weekAssigns)}</span></div>`;
      }).join('');
    }

    if (!state.data.is_platform_party) enableNameDrag();
    if (state.data.is_platform_party) wirePlatformPartyHandlers();
    document.querySelectorAll('.name-chip-assigned .x').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        const id = btn.closest('.name-chip-assigned').dataset.assignmentId;
        await api('DELETE', '/api/schedule/assignments/' + id); showSaved(); await load();
      });
    });
  }

  function renderPastSummary(weekAssigns){
    const filled = weekAssigns.filter(a => a.member_id);
    if (filled.length === 0 && !weekAssigns.some(a => a.text_value)) {
      return '<em class="past-empty">No assignments</em>';
    }
    if (state.data.is_platform_party) {
      const parts = weekAssigns.map(a => {
        if (a.role === 'Sermon Title' && a.text_value) return `<em>"${escapeHtml(a.text_value)}"</em>`;
        if (!a.member_id) return null;
        const m = state.data.members.find(mm => mm.id === a.member_id);
        return m ? `${escapeHtml(m.name)}<span class="role-tag">${escapeHtml(a.role || '')}</span>` : null;
      }).filter(Boolean);
      return parts.join(' · ');
    }
    return filled.map(a => {
      const m = state.data.members.find(mm => mm.id === a.member_id);
      return m ? escapeHtml(m.name) : null;
    }).filter(Boolean).join(', ');
  }

  function renderNameRows(weekAssigns, past){
    if (weekAssigns.length === 0 && !past) return '<div class="empty-slot">Drop a name here</div>';
    return weekAssigns.map(a => {
      const member = state.data.members.find(m => m.id === a.member_id);
      const name = member ? member.name : '(removed)';
      return `<div class="name-chip-assigned" data-assignment-id="${a.id}">${escapeHtml(name)}${past ? '' : '<button class="x" type="button" aria-label="Remove">×</button>'}</div>`;
    }).join('');
  }

  function renderRoleRows(weekDate, weekAssigns, past){
    const roles = ['Call to Worship', 'Welcome', 'Intercessory Prayer', 'Pastor', 'Sermon Title'];
    return roles.map(role => {
      const a = weekAssigns.find(x => x.role === role);
      const isText = role === 'Sermon Title';
      const opts = state.data.members.map(m => `<option value="${m.id}" ${a && a.member_id === m.id ? 'selected' : ''}>${escapeHtml(m.name)}</option>`).join('');
      const valueAttr = a && a.text_value ? `value="${escapeHtml(a.text_value).replace(/"/g,'&quot;')}"` : '';
      const ctrl = isText
        ? `<input type="text" placeholder="Sermon title…" ${valueAttr} ${past ? 'disabled' : ''} data-role="${role}" data-week="${weekDate}" data-assignment-id="${a ? a.id : ''}">`
        : `<select ${past ? 'disabled' : ''} data-role="${role}" data-week="${weekDate}" data-assignment-id="${a ? a.id : ''}">
             <option value="">— none —</option>
             ${opts}
           </select>`;
      return `<div class="role-row">
        <div class="role-label">${role}</div>
        ${ctrl}
      </div>`;
    }).join('');
  }

  function wirePlatformPartyHandlers(){
    const handler = async (e) => {
      const ctrl = e.target;
      if (ctrl.disabled) return;
      const weekDate = ctrl.dataset.week;
      const role = ctrl.dataset.role;
      const assignmentId = ctrl.dataset.assignmentId;
      const isSelect = ctrl.tagName === 'SELECT';
      const memberId = isSelect ? (ctrl.value ? parseInt(ctrl.value) : null) : null;
      const textVal = !isSelect ? ctrl.value : null;
      try {
        if (assignmentId) {
          await api('PATCH', '/api/schedule/assignments/' + assignmentId, isSelect ? { member_id: memberId } : { text_value: textVal });
        } else {
          const r = await api('POST', '/api/schedule/assignments', {
            department_id: state.data.department.id, week_date: weekDate, role, member_id: memberId, text_value: textVal,
          });
          ctrl.dataset.assignmentId = r.assignment.id;
        }
        showSaved();
      } catch (err) {}
    };
    document.querySelectorAll('.role-row select').forEach(s => s.addEventListener('change', handler));
    document.querySelectorAll('.role-row input[type=text]').forEach(i => i.addEventListener('change', handler));
  }

  // Dept tabs
  document.querySelectorAll('.dept-tab').forEach(t => t.addEventListener('click', () => {
    state.deptId = parseInt(t.dataset.deptId);
    load();
  }));
  // Month nav
  document.getElementById('prev-month').addEventListener('click', () => { state.monthIso = state.data.prev_month; load(); });
  document.getElementById('next-month').addEventListener('click', () => { state.monthIso = state.data.next_month; load(); });

  // Members modal
  const membersModal = document.getElementById('members-modal');
  const membersList = document.getElementById('members-list');
  const memberAddName = document.getElementById('member-add-name');
  document.getElementById('open-members-btn').addEventListener('click', renderMembersModal);
  document.getElementById('members-close').addEventListener('click', () => membersModal.classList.remove('open'));
  membersModal.addEventListener('click', (e) => { if (e.target === membersModal) membersModal.classList.remove('open'); });
  document.getElementById('member-add-btn').addEventListener('click', async () => {
    const name = memberAddName.value.trim();
    if (!name) return;
    await api('POST', '/api/schedule/' + state.deptId + '/members', { name });
    memberAddName.value = '';
    await load(); renderMembersModal();
  });
  function renderMembersModal(){
    document.getElementById('members-modal-title').textContent = 'Manage ' + state.data.department.name + ' members';
    membersList.innerHTML = state.data.members.map(m => `
      <div class="member-row">
        <span class="name-text">${escapeHtml(m.name)}</span>
        <button class="danger" data-action="delete" data-id="${m.id}" data-name="${escapeHtml(m.name).replace(/"/g,'&quot;')}">Remove</button>
      </div>
    `).join('');
    membersList.querySelectorAll('button[data-action="delete"]').forEach(b => b.addEventListener('click', async () => {
      if (!confirm('Remove "' + b.dataset.name + '" from ' + state.data.department.name + '?')) return;
      await api('DELETE', '/api/schedule/members/' + b.dataset.id);
      await load(); renderMembersModal();
    }));
    membersModal.classList.add('open');
  }

  // Init
  if (window.Sortable) load();
  else window.addEventListener('load', load);
})();
</script>
</body>
</html>
