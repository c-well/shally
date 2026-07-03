<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $form ? $form->title.' — Edit' : 'New form' }} — Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{background:var(--parchment);color:var(--ink);font-family:'Poppins',system-ui,sans-serif;min-height:100dvh;-webkit-font-smoothing:antialiased}
*:focus-visible{outline:2px solid var(--teal);outline-offset:2px;border-radius:3px}

.top{padding:20px clamp(20px,5vw,40px);display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid var(--line);position:sticky;top:0;background:color-mix(in srgb,var(--parchment) 96%,transparent);backdrop-filter:blur(8px);z-index:20}
.top-back{font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:.18em;text-transform:uppercase;text-decoration:none;color:var(--ink-soft)}
.top-back:hover{color:var(--teal)}
.top-right{display:flex;gap:16px;align-items:center}
.top-link{font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;text-decoration:none;color:var(--ink-soft);background:none;border:0;cursor:pointer}
.top-link:hover{color:var(--teal)}
.savebtn{font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;padding:11px 24px;border-radius:7px;border:0;background:var(--teal);color:#fff;cursor:pointer}
.savebtn:hover{background:var(--teal-dark)}
.savebtn:disabled{opacity:.45;cursor:default}

main{max-width:720px;margin:0 auto;padding:clamp(40px,7vh,64px) clamp(20px,5vw,32px) 120px}
.page-title{font-family:'JetBrains Mono',monospace;font-size:clamp(20px,3vw,28px);font-weight:500;letter-spacing:.03em;text-transform:uppercase;line-height:1;margin-bottom:48px;color:var(--ink)}
.err{background:color-mix(in srgb,#b23b2e 8%,transparent);color:#8b2a1f;border-radius:7px;padding:12px 16px;font-size:13px;margin-bottom:20px;display:none;border-left:3px solid #b23b2e}
.err.show{display:block}

.sec{margin-bottom:48px}
.sec-hd{display:flex;align-items:center;gap:12px;margin-bottom:22px;padding-bottom:14px;border-bottom:1px solid var(--line)}
.sec-label{font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--ink-soft)}
.sec-count{font-family:'JetBrains Mono',monospace;font-size:10px;color:var(--ink-soft);opacity:.55;background:var(--parchment);padding:2px 8px;border-radius:4px}
.sec-hint{font-family:'Instrument Sans',sans-serif;font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-soft);opacity:.4;margin-left:auto}

.frow{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.frow label{font-family:'Instrument Sans',sans-serif;font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--ink-soft)}
.frow input,.frow select{font:inherit;font-size:15px;padding:12px 14px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--ink);width:100%;height:48px}
.frow textarea{font:inherit;font-size:15px;padding:12px 14px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--ink);width:100%;min-height:80px;resize:vertical}
.frow input:focus,.frow select:focus,.frow textarea:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px color-mix(in srgb,var(--teal) 9%,transparent)}
.frow input[readonly]{background:var(--parchment);opacity:.7}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:540px){.grid2{grid-template-columns:1fr}}
.slug-preview{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--ink-soft);opacity:.65;margin-top:5px;padding:0 2px;min-height:16px}

.fields-list{display:flex;flex-direction:column;gap:8px;margin-bottom:8px}
.fcard{background:#fff;border:1px solid var(--line);border-radius:10px;overflow:hidden;transition:box-shadow .15s,border-color .15s}
.fcard.dragging{opacity:.38;box-shadow:0 12px 32px -8px rgba(0,0,0,.15)}
.fcard.dragover{border-color:var(--teal);box-shadow:0 0 0 2px color-mix(in srgb,var(--teal) 18%,transparent)}
.fcard-head{display:flex;align-items:center;gap:10px;padding:13px 14px;cursor:pointer;user-select:none}
.fcard-grip{color:var(--ink-soft);opacity:.35;cursor:grab;flex-shrink:0;touch-action:none;font-size:15px;line-height:1}
.fcard-grip:hover{opacity:.7}
.fcard-grip:active{cursor:grabbing}
.fcard-head-info{flex:1;min-width:0;display:flex;align-items:baseline;gap:8px;overflow:hidden}
.fcard-label{font-size:14px;font-weight:500;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex-shrink:1}
.fcard-key{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--ink-soft);opacity:.5;white-space:nowrap;flex-shrink:0}
.fcard-type{font-family:'Instrument Sans',sans-serif;font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--teal);background:color-mix(in srgb,var(--teal) 10%,transparent);padding:3px 9px;border-radius:4px;flex-shrink:0}
.fcard-del{flex-shrink:0;background:none;border:0;color:var(--ink-soft);opacity:.35;cursor:pointer;padding:4px 6px;border-radius:5px;line-height:1;font-size:18px}
.fcard-del:hover{opacity:1;color:#b23b2e;background:color-mix(in srgb,#b23b2e 8%,transparent)}
.fcard-body{display:none;padding:14px 16px 18px;border-top:1px solid var(--line);flex-direction:column;gap:12px}
.fcard-body.open{display:flex}
.fcard-body label{display:flex;flex-direction:column;gap:5px;font-family:'Instrument Sans',sans-serif;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--ink-soft)}
.fcard-body input,.fcard-body select{font:inherit;font-size:14px;padding:9px 12px;border:1px solid var(--line);border-radius:7px;background:var(--parchment);color:var(--ink);height:42px;width:100%}
.fcard-body textarea{font:inherit;font-size:14px;padding:9px 12px;border:1px solid var(--line);border-radius:7px;background:var(--parchment);color:var(--ink);min-height:52px;resize:vertical;width:100%}
.fcard-body input:focus,.fcard-body select:focus,.fcard-body textarea:focus{outline:none;border-color:var(--teal);background:#fff}
.fcard-row2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
@media(max-width:480px){.fcard-row2{grid-template-columns:1fr}}
.toggle-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-soft)}
.toggle-row input[type=checkbox]{width:16px;height:16px;accent-color:var(--teal);cursor:pointer;flex-shrink:0}
.options-area{display:flex;flex-direction:column;gap:5px}
.opt-row{display:flex;gap:6px;align-items:center}
.opt-row input{flex:1;height:38px!important}
.opt-del{background:none;border:0;color:var(--ink-soft);opacity:.4;font-size:18px;cursor:pointer;line-height:1;padding:0 4px}
.opt-del:hover{opacity:1;color:#b23b2e}
.add-opt{font-family:'Instrument Sans',sans-serif;font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--teal);background:none;border:1.5px dashed color-mix(in srgb,var(--teal) 35%,var(--line));border-radius:6px;padding:7px 12px;cursor:pointer;align-self:flex-start}
.add-opt:hover{border-style:solid;border-color:var(--teal)}
.cond-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
@media(max-width:460px){.cond-row{grid-template-columns:1fr}}

.addfld{font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;padding:14px;border:1.5px dashed color-mix(in srgb,var(--ink-soft) 28%,transparent);border-radius:9px;background:transparent;color:var(--ink-soft);cursor:pointer;width:100%;transition:border-color .15s,color .15s}
.addfld:hover{border-color:var(--teal);color:var(--teal);border-style:solid}

.pill-group{display:flex;flex-wrap:wrap;gap:8px}
.pill{font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:9px 18px;border:1px solid var(--line);border-radius:var(--r-btn, 8px);background:#fff;color:var(--ink-soft);cursor:pointer;transition:all .12s}
.pill.on{background:var(--teal);color:#fff;border-color:var(--teal)}
.pill:hover:not(.on){border-color:var(--teal);color:var(--teal)}

.saved-pip{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-soft);opacity:0;transition:opacity .2s;pointer-events:none;background:#fff;border:1px solid var(--line);border-radius:999px;padding:10px 22px;box-shadow:0 8px 24px -8px rgba(0,0,0,.12)}
.saved-pip.show{opacity:1}
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme','default') }}">

<div class="top">
  <a href="{{ route('admin.intake.index') }}" class="top-back">← Forms</a>
  <div class="top-right">
    @if ($form)
      <a href="{{ route('admin.intake.submissions', $form) }}" class="top-link">Gallery →</a>
    @endif
    <button class="savebtn" id="saveBtn">{{ $form ? 'Save changes' : 'Create form' }}</button>
  </div>
</div>

<main>
  <div class="page-title">{{ $form ? $form->title : 'New form' }}</div>
  <div class="err" id="err"></div>

  <div class="sec">
    <div class="sec-hd"><span class="sec-label">Form basics</span></div>
    <div class="frow"><label>Form title</label><input id="f-title" value="{{ $form?->title }}" placeholder="e.g. Graduate Recognition"></div>
    <div class="grid2">
      <div>
        <div class="frow"><label>URL slug</label><input id="f-slug" value="{{ $form?->slug }}" placeholder="e.g. grad"{{ $form ? ' readonly' : '' }}></div>
        <div class="slug-preview" id="slug-preview">{{ $form ? 'thechurchofpeace.org/intake/'.$form->slug : '' }}</div>
      </div>
      <div class="frow"><label>Output type</label>
        <select id="f-output">
          <option value="none" @selected(($form?->output_type ?? 'none')==='none')>None (data only)</option>
          <option value="graduation" @selected($form?->output_type==='graduation')>Graduation slide (PNG)</option>
        </select>
      </div>
    </div>
    <div class="frow"><label>Intro text</label><textarea id="f-intro" placeholder="Shown above the form…">{{ $form?->intro }}</textarea></div>
    <div class="grid2">
      <div class="frow"><label>Thank-you message</label><textarea id="f-thanks" placeholder="Shown after they submit…">{{ $form?->setting('thank_you') }}</textarea></div>
      <div class="frow"><label>Submit button label</label><input id="f-submit-label" value="{{ $form?->setting('submit_label','Submit') }}" placeholder="Submit"></div>
    </div>
  </div>

  <div class="sec">
    <div class="sec-hd">
      <span class="sec-label">Fields</span>
      <span class="sec-count" id="field-count">{{ $form ? count($form->fields()) : '0' }} fields</span>
      <span class="sec-hint">drag to reorder</span>
    </div>
    <div class="fields-list" id="fieldsList"></div>
    <button class="addfld" id="addField">＋ Add field</button>
  </div>

  <div class="sec">
    <div class="sec-hd"><span class="sec-label">Notifications</span></div>
    <div class="grid2">
      <div class="frow"><label>Email (comma-separated)</label><input id="f-emails" value="{{ implode(', ', $form?->setting('notify_emails',[]) ?? []) }}" placeholder="shalomsda3323@gmail.com"></div>
      <div class="frow"><label>SMS to (phone number)</label><input id="f-sms" value="{{ $form?->setting('sms_to') }}" placeholder="+1 (868) ..."></div>
    </div>
  </div>

  <div class="sec" id="slideSection" style="{{ ($form?->output_type ?? 'none')!=='graduation' ? 'display:none' : '' }}">
    <div class="sec-hd"><span class="sec-label">Slide style</span></div>
    <div class="pill-group" id="slidePills">
      <button class="pill {{ ($form?->setting('slide_style','sans')==='sans') ? 'on' : '' }}" data-val="sans">Sans</button>
      <button class="pill {{ ($form?->setting('slide_style','sans')==='serif') ? 'on' : '' }}" data-val="serif">Serif</button>
    </div>
  </div>
</main>

<div class="saved-pip" id="pip">Saved</div>
@include('partials._confirm')

<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  var IS_EDIT = {{ $form ? 'true' : 'false' }};
  var SAVE_URL = IS_EDIT ? '{{ $form ? route("admin.intake.builder.update", $form) : "" }}' : '{{ route("admin.intake.store") }}';
  var pip = document.getElementById('pip');
  var errEl = document.getElementById('err');

  function showPip(msg) { pip.textContent = msg||'Saved'; pip.classList.add('show'); clearTimeout(pip._t); pip._t = setTimeout(function(){ pip.classList.remove('show'); }, 1600); }
  function showErr(msg) { errEl.textContent = msg; errEl.classList.add('show'); errEl.scrollIntoView({behavior:'smooth',block:'nearest'}); setTimeout(function(){ errEl.classList.remove('show'); }, 4000); }

  var TYPES = [
    {v:'text',l:'Short text'},{v:'textarea',l:'Long text'},{v:'email',l:'Email'},
    {v:'tel',l:'Phone'},{v:'date',l:'Date'},{v:'select',l:'Dropdown'},
    {v:'checkboxes',l:'Checkboxes'},{v:'checkbox',l:'Single checkbox'},{v:'photo',l:'Photo upload'},
  ];

  var fields = @json($form ? $form->fields() : []);
  var list = document.getElementById('fieldsList');

  function updateCount() {
    var el = document.getElementById('field-count');
    if (el) el.textContent = fields.length + ' ' + (fields.length === 1 ? 'field' : 'fields');
  }

  function render() {
    list.innerHTML = '';
    fields.forEach(function(f, i) { list.appendChild(makeCard(f, i)); });
    enableDrag();
    updateCount();
  }

  function opt(v, l, sel) { var o = document.createElement('option'); o.value = v; o.textContent = l; if (sel) o.selected = true; return o; }

  function makeCard(f, i) {
    var wrap = document.createElement('div');
    wrap.className = 'fcard';
    wrap.setAttribute('data-i', i);
    var typeLbl = (TYPES.find(function(t){ return t.v === f.type; }) || {l: f.type || 'text'}).l;
    wrap.innerHTML =
      '<div class="fcard-head">' +
        '<span class="fcard-grip" title="Drag to reorder">⠿</span>' +
        '<div class="fcard-head-info">' +
          '<span class="fcard-label">' + (f.label || 'Untitled field') + '</span>' +
          (f.key ? '<span class="fcard-key">' + f.key + '</span>' : '') +
        '</div>' +
        '<span class="fcard-type">' + typeLbl + '</span>' +
        '<button class="fcard-del" type="button" title="Remove field">×</button>' +
      '</div>' +
      '<div class="fcard-body"></div>';
    var body = wrap.querySelector('.fcard-body');
    body.appendChild(buildBody(f, i));
    wrap.querySelector('.fcard-head').addEventListener('click', function(e) {
      if (e.target.closest('.fcard-del') || e.target.closest('.fcard-grip')) return;
      body.classList.toggle('open');
    });
    wrap.querySelector('.fcard-del').addEventListener('click', function(e) {
      e.stopPropagation();
      window.shConfirm('Remove the "' + (f.label || 'this field') + '" field?', {okLabel:'Remove',danger:true}).then(function(ok) {
        if (!ok) return; fields.splice(i, 1); render();
      });
    });
    return wrap;
  }

  function buildBody(f, i) {
    var d = document.createElement('div'); d.style.display = 'contents';
    var r1 = document.createElement('div'); r1.className = 'fcard-row2';
    var lbl = fld('Label', 'text', f.label||'', function(v){ fields[i].label = v; refreshHead(i); });
    lbl.querySelector('input').placeholder = "e.g. Graduate's name";
    var key = fld('Key (no spaces)', 'text', f.key||'', function(v){ fields[i].key = v; refreshHead(i); });
    key.querySelector('input').placeholder = 'e.g. name';
    r1.appendChild(lbl); r1.appendChild(key); d.appendChild(r1);

    var typeWrap = document.createElement('label'); typeWrap.innerHTML = '<span>Field type</span>';
    var sel = document.createElement('select');
    TYPES.forEach(function(t){ sel.appendChild(opt(t.v, t.l, t.v === f.type)); });
    sel.addEventListener('change', function() {
      fields[i].type = this.value; render();
      list.querySelectorAll('.fcard')[i].querySelector('.fcard-body').classList.add('open');
    });
    typeWrap.appendChild(sel); d.appendChild(typeWrap);

    if (!['checkbox','checkboxes','photo'].includes(f.type || 'text')) {
      var r2 = document.createElement('div'); r2.className = 'fcard-row2';
      r2.appendChild(fld('Placeholder', 'text', f.placeholder||'', function(v){ fields[i].placeholder = v; }));
      r2.appendChild(fld('Help text', 'text', f.help||'', function(v){ fields[i].help = v; }));
      d.appendChild(r2);
    }

    if (f.type === 'select' || f.type === 'checkboxes') {
      var oplbl = document.createElement('label'); oplbl.innerHTML = '<span>Options</span>';
      var area = document.createElement('div'); area.className = 'options-area'; area.id = 'opts-' + i;
      (f.options || []).forEach(function(o){ area.appendChild(optRow(o, i)); });
      var addO = document.createElement('button'); addO.type = 'button'; addO.className = 'add-opt'; addO.textContent = '＋ Add option';
      addO.addEventListener('click', function(){ fields[i].options = fields[i].options || []; fields[i].options.push(''); area.appendChild(optRow('', i)); });
      oplbl.appendChild(area); oplbl.appendChild(addO); d.appendChild(oplbl);
    }

    if (f.type === 'checkbox') {
      d.appendChild(fld('Checkbox label', 'text', f.checkbox_label||'', function(v){ fields[i].checkbox_label = v; }));
    }

    var req = document.createElement('div'); req.className = 'toggle-row';
    var reqCb = document.createElement('input'); reqCb.type = 'checkbox'; reqCb.checked = !!f.required;
    reqCb.addEventListener('change', function(){ fields[i].required = this.checked; });
    req.appendChild(reqCb); req.appendChild(document.createTextNode('Required'));
    d.appendChild(req);

    var cl = document.createElement('label'); cl.innerHTML = '<span>Show only when (optional)</span>';
    var cr = document.createElement('div'); cr.className = 'cond-row';
    var fieldKeys = fields.map(function(ff){ return ff.key || ''; }).filter(Boolean);
    var cs1 = document.createElement('select');
    cs1.appendChild(opt('', '— always show —', !f.show_if));
    fieldKeys.forEach(function(k){ cs1.appendChild(opt(k, k, f.show_if && f.show_if.field === k)); });
    var cs2 = document.createElement('select');
    ['not_empty','equals','not_equals'].forEach(function(op){ cs2.appendChild(opt(op, op, f.show_if && f.show_if.op === op)); });
    var cs3 = document.createElement('input'); cs3.type = 'text'; cs3.placeholder = 'value'; cs3.value = (f.show_if && f.show_if.value) || '';
    function saveCond() { if (!cs1.value) { delete fields[i].show_if; return; } fields[i].show_if = {field:cs1.value, op:cs2.value, value:cs3.value}; }
    cs1.addEventListener('change', saveCond); cs2.addEventListener('change', saveCond); cs3.addEventListener('input', saveCond);
    cr.appendChild(cs1); cr.appendChild(cs2); cr.appendChild(cs3); cl.appendChild(cr); d.appendChild(cl);
    return d;
  }

  function optRow(val, fi) {
    var row = document.createElement('div'); row.className = 'opt-row';
    var inp = document.createElement('input'); inp.type = 'text'; inp.value = val; inp.placeholder = 'Option…';
    inp.addEventListener('input', function(){ syncOpts(fi); });
    var del = document.createElement('button'); del.type = 'button'; del.className = 'opt-del'; del.textContent = '×';
    del.addEventListener('click', function(){ row.remove(); syncOpts(fi); });
    row.appendChild(inp); row.appendChild(del); return row;
  }

  function syncOpts(fi) {
    var area = document.getElementById('opts-' + fi); if (!area) return;
    fields[fi].options = [].slice.call(area.querySelectorAll('input')).map(function(e){ return e.value.trim(); }).filter(Boolean);
  }

  function fld(lbl, type, val, onChange) {
    var wrap = document.createElement('label'); wrap.innerHTML = '<span>' + lbl + '</span>';
    var inp = document.createElement('input'); inp.type = type; inp.value = val;
    inp.addEventListener('input', function(){ onChange(this.value); });
    wrap.appendChild(inp); return wrap;
  }

  function refreshHead(i) {
    var card = list.querySelectorAll('.fcard')[i]; if (!card) return;
    card.querySelector('.fcard-label').textContent = fields[i].label || 'Untitled field';
    var keyEl = card.querySelector('.fcard-key');
    if (keyEl) { keyEl.textContent = fields[i].key || ''; }
    else if (fields[i].key) {
      var info = card.querySelector('.fcard-head-info');
      if (info) { var k = document.createElement('span'); k.className = 'fcard-key'; k.textContent = fields[i].key; info.appendChild(k); }
    }
  }

  document.getElementById('addField').addEventListener('click', function() {
    fields.push({key:'', label:'', type:'text', required:false});
    render();
    var cards = list.querySelectorAll('.fcard');
    var last = cards[cards.length - 1];
    if (last) { last.querySelector('.fcard-body').classList.add('open'); last.scrollIntoView({behavior:'smooth', block:'center'}); }
  });

  document.getElementById('f-output').addEventListener('change', function() {
    document.getElementById('slideSection').style.display = this.value === 'graduation' ? '' : 'none';
  });

  document.querySelectorAll('#slidePills .pill').forEach(function(p) {
    p.addEventListener('click', function() {
      document.querySelectorAll('#slidePills .pill').forEach(function(x){ x.classList.remove('on'); });
      this.classList.add('on');
    });
  });

  var slugInput = document.getElementById('f-slug');
  if (slugInput && !IS_EDIT) {
    slugInput.addEventListener('input', function() {
      var clean = this.value.trim().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-');
      var prev = document.getElementById('slug-preview');
      if (prev) prev.textContent = clean ? 'thechurchofpeace.org/intake/' + clean : '';
    });
  }

  var dragIdx = null;
  function enableDrag() {
    list.querySelectorAll('.fcard').forEach(function(card, i) {
      card.setAttribute('draggable', 'true');
      card.addEventListener('dragstart', function(e){ dragIdx = i; card.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; });
      card.addEventListener('dragend', function(){ card.classList.remove('dragging'); list.querySelectorAll('.fcard').forEach(function(c){ c.classList.remove('dragover'); }); });
      card.addEventListener('dragover', function(e){ e.preventDefault(); if (i !== dragIdx) card.classList.add('dragover'); });
      card.addEventListener('dragleave', function(){ card.classList.remove('dragover'); });
      card.addEventListener('drop', function(e){ e.preventDefault(); card.classList.remove('dragover'); if (dragIdx === null || dragIdx === i) return; var moved = fields.splice(dragIdx, 1)[0]; fields.splice(i, 0, moved); dragIdx = null; render(); });
    });
  }

  function payload() {
    var emails = document.getElementById('f-emails').value.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
    var slideStyle = (document.querySelector('#slidePills .pill.on') || {getAttribute: function(){ return 'sans'; }}).getAttribute('data-val');
    return {
      title:       document.getElementById('f-title').value.trim(),
      slug:        document.getElementById('f-slug').value.trim().toLowerCase().replace(/[^a-z0-9-]/g, '-'),
      output_type: document.getElementById('f-output').value,
      intro:       document.getElementById('f-intro').value.trim(),
      schema:      {fields: fields},
      settings: {
        thank_you:     document.getElementById('f-thanks').value.trim(),
        submit_label:  document.getElementById('f-submit-label').value.trim() || 'Submit',
        notify_emails: emails,
        sms_to:        document.getElementById('f-sms').value.trim(),
        slide_style:   slideStyle,
      },
    };
  }

  document.getElementById('saveBtn').addEventListener('click', function() {
    var btn = this; var p = payload();
    if (!p.title) { showErr('Enter a form title.'); return; }
    if (!p.slug)  { showErr('Enter a URL slug.'); return; }
    if (!fields.length) { showErr('Add at least one field.'); return; }
    var bad = fields.find(function(f){ return !f.key || !f.label; });
    if (bad) { showErr('Each field needs a key and a label — click the card to expand it.'); return; }
    btn.disabled = true;
    fetch(SAVE_URL, {
      method: 'POST',
      headers: {'X-CSRF-TOKEN':token, 'Content-Type':'application/json', 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'},
      body: JSON.stringify(p)
    })
    .then(function(r){ return r.json().then(function(d){ return {ok:r.ok, d:d}; }); })
    .then(function(res) {
      btn.disabled = false;
      if (res.ok && res.d.ok) {
        showPip(IS_EDIT ? 'Changes saved.' : 'Form created!');
        if (!IS_EDIT && res.d.slug) { window.location.href = '/admin/intake/' + res.d.slug + '/builder'; }
      } else { showErr(res.d.message || res.d.error || 'Could not save — try again.'); }
    })
    .catch(function(){ btn.disabled = false; showErr('Network error — try again.'); });
  });

  render();
})();
</script>
</body>
</html>
