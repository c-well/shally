<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $form ? 'Edit '.$form->title : 'New form' }} — Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital@0;1&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{background:var(--parchment);color:var(--ink);font-family:'IBM Plex Sans',system-ui,sans-serif;min-height:100dvh;-webkit-font-smoothing:antialiased}
*:focus-visible{outline:2px solid var(--teal);outline-offset:2px;border-radius:3px}
.top{padding:16px clamp(16px,5vw,32px);display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid var(--line);position:sticky;top:0;background:color-mix(in srgb,var(--parchment) 94%,transparent);backdrop-filter:blur(6px);z-index:20}
.top a,.top button.lnk{font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;text-decoration:none;color:var(--ink-soft);background:none;border:0;cursor:pointer}
.top a:hover,.top button.lnk:hover{color:var(--teal)}
.top .right{display:flex;gap:12px;align-items:center}
.savebtn{font-family:inherit;font-size:13px;font-weight:600;padding:10px 22px;border-radius:7px;border:0;background:var(--teal);color:#fff;cursor:pointer;letter-spacing:.02em}
.savebtn:hover{background:var(--teal-dark)}
.savebtn:disabled{opacity:.45;cursor:default}

main{max-width:740px;margin:0 auto;padding:clamp(24px,5vh,44px) clamp(16px,5vw,28px) 120px}
h1{font-size:clamp(26px,5vw,38px);font-weight:700;letter-spacing:-.02em;color:var(--ink);margin-bottom:28px}

.section{margin-bottom:34px}
.section-hd{font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--ink-soft);margin-bottom:12px;display:flex;align-items:center;justify-content:space-between}
.field-row{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.field-row label{font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft)}
.field-row input,.field-row select,.field-row textarea{font:inherit;font-size:15px;padding:11px 13px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);width:100%}
.field-row textarea{min-height:72px;resize:vertical}
.field-row input:focus,.field-row select:focus,.field-row textarea:focus{outline:none;border-color:var(--teal)}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:520px){.row2{grid-template-columns:1fr}}

/* form fields list */
.fields-list{display:flex;flex-direction:column;gap:8px}
.fcard{background:#fff;border:1px solid var(--line);border-radius:9px;overflow:hidden;transition:box-shadow .15s}
.fcard.dragging{opacity:.45;box-shadow:0 8px 24px -8px rgba(0,0,0,.22)}
.fcard.dragover{border-color:var(--teal);box-shadow:0 0 0 2px color-mix(in srgb,var(--teal) 30%,transparent)}
.fcard-head{display:flex;align-items:center;gap:10px;padding:11px 12px;cursor:pointer}
.fcard-grip{color:var(--ink-soft);cursor:grab;flex-shrink:0;touch-action:none;padding:2px 4px}
.fcard-grip:active{cursor:grabbing}
.fcard-label{flex:1;font-size:14px;font-weight:500;color:var(--ink);min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fcard-type{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-soft);background:var(--parchment);padding:3px 8px;border-radius:4px;flex-shrink:0}
.fcard-del{flex-shrink:0;background:none;border:0;color:var(--ink-soft);cursor:pointer;padding:4px 6px;border-radius:5px;line-height:1;font-size:16px}
.fcard-del:hover{color:#b23b2e;background:color-mix(in srgb,#b23b2e 8%,transparent)}
.fcard-body{display:none;padding:0 12px 14px;border-top:1px solid var(--line);flex-direction:column;gap:10px}
.fcard-body.open{display:flex}
.fcard-body label{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-soft);display:flex;flex-direction:column;gap:4px}
.fcard-body input,.fcard-body select,.fcard-body textarea{font:inherit;font-size:14px;padding:9px 11px;border:1px solid var(--line);border-radius:6px;background:var(--parchment);color:var(--ink)}
.fcard-body input:focus,.fcard-body select:focus,.fcard-body textarea:focus{outline:none;border-color:var(--teal);background:#fff}
.fcard-body textarea{min-height:54px;resize:vertical}
.fcard-row2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
@media(max-width:480px){.fcard-row2{grid-template-columns:1fr}}
.toggle-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-soft)}
.toggle-row input[type=checkbox]{width:16px;height:16px;accent-color:var(--teal);cursor:pointer}
.options-area{display:flex;flex-direction:column;gap:5px}
.opt-row{display:flex;gap:6px;align-items:center}
.opt-row input{flex:1}
.opt-del{background:none;border:0;color:var(--ink-soft);font-size:17px;cursor:pointer;line-height:1;padding:0 4px}
.opt-del:hover{color:#b23b2e}
.add-opt{font:inherit;font-size:11px;font-weight:600;letter-spacing:.06em;color:var(--teal);background:none;border:1px dashed var(--line);border-radius:5px;padding:7px 12px;cursor:pointer;align-self:flex-start}
.add-opt:hover{border-color:var(--teal)}
.cond-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px}
@media(max-width:460px){.cond-row{grid-template-columns:1fr}}

.addfld{font-family:inherit;font-size:12px;font-weight:600;letter-spacing:.04em;padding:12px 18px;border:1px dashed var(--line);border-radius:8px;background:transparent;color:var(--teal);cursor:pointer;width:100%;margin-top:10px;transition:border-color .15s}
.addfld:hover{border-color:var(--teal);border-style:solid}

/* settings block */
.settings-grid{display:flex;flex-direction:column;gap:12px}
.pill-group{display:flex;flex-wrap:wrap;gap:7px}
.pill{font:inherit;font-size:12px;padding:7px 13px;border:1px solid var(--line);border-radius:999px;background:#fff;color:var(--ink-soft);cursor:pointer;transition:all .12s}
.pill.on{background:var(--teal);color:#fff;border-color:var(--teal)}
.pill:hover:not(.on){border-color:var(--teal);color:var(--teal)}

.saved-pip{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);font-size:12px;color:var(--ink-soft);opacity:0;transition:opacity .2s;pointer-events:none;background:#fff;border:1px solid var(--line);border-radius:999px;padding:7px 16px}
.saved-pip.show{opacity:1}
.err{background:color-mix(in srgb,#b23b2e 8%,transparent);color:#8b2a1f;border-radius:7px;padding:11px 14px;font-size:13px;margin-bottom:14px;display:none}
.err.show{display:block}
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme','default') }}">

<div class="top">
  <a href="{{ route('admin.intake.index') }}">← Forms</a>
  <div class="right">
    @if ($form)
      <a href="{{ route('admin.intake.submissions', $form) }}" class="lnk">View gallery →</a>
    @endif
    <button class="savebtn" id="saveBtn">{{ $form ? 'Save changes' : 'Create form' }}</button>
  </div>
</div>

<main>
  <h1>{{ $form ? 'Edit form' : 'New form' }}</h1>
  <div class="err" id="err"></div>

  {{-- Basic info --}}
  <div class="section">
    <div class="section-hd">Form basics</div>
    <div class="field-row"><label>Form title</label><input id="f-title" value="{{ $form?->title }}" placeholder="e.g. Graduate Recognition"></div>
    <div class="row2">
      <div class="field-row"><label>URL slug</label><input id="f-slug" value="{{ $form?->slug }}" placeholder="e.g. grad" {{ $form ? 'readonly' : '' }}></div>
      <div class="field-row"><label>Output type</label>
        <select id="f-output">
          <option value="none" @selected(($form?->output_type ?? 'none') === 'none')>None (data only)</option>
          <option value="graduation" @selected($form?->output_type === 'graduation')>Graduation slide (1920×1080 PNG)</option>
        </select>
      </div>
    </div>
    <div class="field-row"><label>Intro text (shown above the form)</label><textarea id="f-intro">{{ $form?->intro }}</textarea></div>
    <div class="field-row"><label>Thank-you message</label><textarea id="f-thanks">{{ $form?->setting('thank_you') }}</textarea></div>
    <div class="field-row"><label>Submit button label</label><input id="f-submit-label" value="{{ $form?->setting('submit_label','Submit') }}" placeholder="Submit"></div>
  </div>

  {{-- Fields --}}
  <div class="section">
    <div class="section-hd">Fields <span style="color:var(--ink-faint,#9aa0aa);font-weight:400;letter-spacing:0">drag to reorder</span></div>
    <div class="fields-list" id="fieldsList"></div>
    <button class="addfld" id="addField">＋ Add field</button>
  </div>

  {{-- Notifications --}}
  <div class="section">
    <div class="section-hd">Notifications</div>
    <div class="settings-grid">
      <div class="field-row"><label>Email notifications (comma-separated)</label><input id="f-emails" value="{{ implode(', ', $form?->setting('notify_emails',[]) ?? []) }}" placeholder="shalomsda3323@gmail.com"></div>
      <div class="field-row"><label>SMS to (phone number)</label><input id="f-sms" value="{{ $form?->setting('sms_to') }}" placeholder="+1..."></div>
    </div>
  </div>

  {{-- Slide style (shown only for graduation output) --}}
  <div class="section" id="slideSection" style="{{ ($form?->output_type ?? 'none') !== 'graduation' ? 'display:none' : '' }}">
    <div class="section-hd">Slide style</div>
    <div class="pill-group" id="slidePills">
      <button class="pill {{ ($form?->setting('slide_style','sans') === 'sans') ? 'on' : '' }}" data-val="sans">Sans (Poppins)</button>
      <button class="pill {{ ($form?->setting('slide_style','sans') === 'serif') ? 'on' : '' }}" data-val="serif">Serif (IBM Plex Serif)</button>
    </div>
  </div>
</main>

<div class="saved-pip" id="pip">Saved</div>
@include('partials._confirm')

<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  var IS_EDIT = {{ $form ? 'true' : 'false' }};
  var FORM_ID = {{ $form ? $form->id : 'null' }};
  var SAVE_URL = IS_EDIT ? '{{ $form ? route("admin.intake.builder.update", $form) : "" }}' : '{{ route("admin.intake.store") }}';
  var pip = document.getElementById('pip');
  var errEl = document.getElementById('err');

  function showPip(msg) { pip.textContent = msg || 'Saved'; pip.classList.add('show'); clearTimeout(pip._t); pip._t = setTimeout(function(){ pip.classList.remove('show'); },1400); }
  function showErr(msg) { errEl.textContent = msg; errEl.classList.add('show'); setTimeout(function(){ errEl.classList.remove('show'); },4000); }

  /* ── Field types ── */
  var TYPES = [
    { v:'text',      l:'Short text' },
    { v:'textarea',  l:'Long text' },
    { v:'email',     l:'Email' },
    { v:'tel',       l:'Phone' },
    { v:'date',      l:'Date' },
    { v:'select',    l:'Dropdown' },
    { v:'checkboxes',l:'Checkboxes (multi)' },
    { v:'checkbox',  l:'Single checkbox' },
    { v:'photo',     l:'Photo upload' },
  ];

  /* ── Fields state ── */
  var fields = @json($form ? $form->fields() : []);

  /* ── Render ── */
  var list = document.getElementById('fieldsList');
  function render() {
    list.innerHTML = '';
    fields.forEach(function(f, i) { list.appendChild(makeCard(f, i)); });
    enableDrag();
  }

  function opt(v, l, sel) { var o=document.createElement('option'); o.value=v; o.textContent=l; if(sel)o.selected=true; return o; }

  function makeCard(f, i) {
    var wrap = document.createElement('div');
    wrap.className = 'fcard';
    wrap.setAttribute('data-i', i);
    var typeLbl = (TYPES.find(function(t){return t.v===f.type;})||{l:f.type||'text'}).l;
    wrap.innerHTML = '<div class="fcard-head">' +
      '<span class="fcard-grip" title="Drag to reorder">⠿</span>' +
      '<span class="fcard-label">'+(f.label||'Untitled field')+'</span>' +
      '<span class="fcard-type">'+typeLbl+'</span>' +
      '<button class="fcard-del" title="Delete field">×</button>' +
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
      window.shConfirm('Delete the "' + (f.label||'this field') + '" field?', {okLabel:'Delete',danger:true}).then(function(ok){
        if (!ok) return;
        fields.splice(i, 1); render();
      });
    });
    return wrap;
  }

  function buildBody(f, i) {
    var d = document.createElement('div');
    d.style.display = 'contents';

    // label + key
    var r1 = document.createElement('div'); r1.className='fcard-row2';
    var lbl = fld('Label', 'text', f.label||'', function(v){ fields[i].label=v; refreshHead(i); }); lbl.querySelector('input').placeholder='e.g. Graduate\'s name';
    var key = fld('Field key (no spaces)', 'text', f.key||'', function(v){ fields[i].key=v; }); key.querySelector('input').placeholder='e.g. name';
    r1.appendChild(lbl); r1.appendChild(key); d.appendChild(r1);

    // type
    var typeWrap = document.createElement('label');
    typeWrap.innerHTML='<span>Field type</span>';
    var sel = document.createElement('select');
    TYPES.forEach(function(t){ sel.appendChild(opt(t.v,t.l,t.v===f.type)); });
    sel.addEventListener('change', function(){ fields[i].type=this.value; render(); list.querySelectorAll('.fcard')[i].querySelector('.fcard-body').classList.add('open'); });
    typeWrap.appendChild(sel); d.appendChild(typeWrap);

    // placeholder + help
    if (!['checkbox','checkboxes','photo'].includes(f.type||'text')) {
      var r2=document.createElement('div'); r2.className='fcard-row2';
      r2.appendChild(fld('Placeholder','text',f.placeholder||'',function(v){fields[i].placeholder=v;}));
      r2.appendChild(fld('Help text','text',f.help||'',function(v){fields[i].help=v;}));
      d.appendChild(r2);
    }

    // options (select / checkboxes)
    if (f.type==='select'||f.type==='checkboxes') {
      var oplbl = document.createElement('label'); oplbl.innerHTML='<span>Options (one per line)</span>';
      var area = document.createElement('div'); area.className='options-area'; area.id='opts-'+i;
      (f.options||[]).forEach(function(o){ area.appendChild(optRow(o,i)); });
      var addO = document.createElement('button'); addO.type='button'; addO.className='add-opt'; addO.textContent='＋ Add option';
      addO.addEventListener('click',function(){ fields[i].options=fields[i].options||[]; fields[i].options.push(''); area.appendChild(optRow('',i)); });
      oplbl.appendChild(area); oplbl.appendChild(addO); d.appendChild(oplbl);
    }

    // checkbox label
    if (f.type==='checkbox') {
      d.appendChild(fld('Checkbox label','text',f.checkbox_label||'',function(v){fields[i].checkbox_label=v;}));
    }

    // required
    var req = document.createElement('div'); req.className='toggle-row';
    var reqCb = document.createElement('input'); reqCb.type='checkbox'; reqCb.checked=!!f.required;
    reqCb.addEventListener('change',function(){fields[i].required=this.checked;});
    req.appendChild(reqCb); req.appendChild(document.createTextNode('Required'));
    d.appendChild(req);

    // condition
    var cl = document.createElement('label'); cl.innerHTML='<span>Show only when (optional)</span>';
    var cr = document.createElement('div'); cr.className='cond-row';
    var fieldKeys = fields.map(function(ff){return ff.key||'';}).filter(function(k){return k;});
    var cs1 = document.createElement('select');
    cs1.appendChild(opt('','— any field —',!f.show_if));
    fieldKeys.forEach(function(k){ cs1.appendChild(opt(k,k,f.show_if&&f.show_if.field===k)); });
    var cs2 = document.createElement('select');
    ['not_empty','equals','not_equals'].forEach(function(op){ cs2.appendChild(opt(op,op,f.show_if&&f.show_if.op===op)); });
    var cs3 = document.createElement('input'); cs3.type='text'; cs3.placeholder='value'; cs3.value=(f.show_if&&f.show_if.value)||'';
    function saveCond(){ if(!cs1.value){ delete fields[i].show_if; return; } fields[i].show_if={field:cs1.value,op:cs2.value,value:cs3.value}; }
    cs1.addEventListener('change',saveCond); cs2.addEventListener('change',saveCond); cs3.addEventListener('input',saveCond);
    cr.appendChild(cs1); cr.appendChild(cs2); cr.appendChild(cs3); cl.appendChild(cr); d.appendChild(cl);

    return d;
  }

  function optRow(val, fi) {
    var row=document.createElement('div'); row.className='opt-row';
    var inp=document.createElement('input'); inp.type='text'; inp.value=val; inp.placeholder='Option…';
    inp.addEventListener('input',function(){ syncOpts(fi); });
    var del=document.createElement('button'); del.type='button'; del.className='opt-del'; del.textContent='×';
    del.addEventListener('click',function(){ row.remove(); syncOpts(fi); });
    row.appendChild(inp); row.appendChild(del); return row;
  }

  function syncOpts(fi) {
    var area=document.getElementById('opts-'+fi);
    if (!area) return;
    fields[fi].options=[].slice.call(area.querySelectorAll('input')).map(function(e){return e.value.trim();}).filter(Boolean);
  }

  function fld(lbl, type, val, onChange) {
    var wrap=document.createElement('label');
    wrap.innerHTML='<span>'+lbl+'</span>';
    var inp=document.createElement('input'); inp.type=type; inp.value=val;
    inp.addEventListener('input',function(){ onChange(this.value); });
    wrap.appendChild(inp); return wrap;
  }

  function refreshHead(i) {
    var card=list.querySelectorAll('.fcard')[i];
    if (card) card.querySelector('.fcard-label').textContent=fields[i].label||'Untitled field';
  }

  /* ── Add field ── */
  document.getElementById('addField').addEventListener('click', function() {
    fields.push({ key:'', label:'', type:'text', required:false });
    render();
    var cards=list.querySelectorAll('.fcard');
    var last=cards[cards.length-1];
    if (last) { last.querySelector('.fcard-body').classList.add('open'); last.scrollIntoView({behavior:'smooth',block:'center'}); }
  });

  /* ── Output type toggle ── */
  document.getElementById('f-output').addEventListener('change', function() {
    document.getElementById('slideSection').style.display = this.value==='graduation' ? '' : 'none';
  });

  /* ── Slide style pills ── */
  document.querySelectorAll('#slidePills .pill').forEach(function(p) {
    p.addEventListener('click', function() {
      document.querySelectorAll('#slidePills .pill').forEach(function(x){x.classList.remove('on');});
      this.classList.add('on');
    });
  });

  /* ── Drag to reorder ── */
  var dragIdx=null;
  function enableDrag() {
    list.querySelectorAll('.fcard').forEach(function(card, i) {
      var grip=card.querySelector('.fcard-grip');
      card.setAttribute('draggable','true');
      card.addEventListener('dragstart',function(e){ dragIdx=i; card.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; });
      card.addEventListener('dragend',function(){ card.classList.remove('dragging'); list.querySelectorAll('.fcard').forEach(function(c){c.classList.remove('dragover');}); });
      card.addEventListener('dragover',function(e){ e.preventDefault(); if(i!==dragIdx){ card.classList.add('dragover'); } });
      card.addEventListener('dragleave',function(){ card.classList.remove('dragover'); });
      card.addEventListener('drop',function(e){ e.preventDefault(); card.classList.remove('dragover'); if(dragIdx===null||dragIdx===i) return; var moved=fields.splice(dragIdx,1)[0]; fields.splice(i,0,moved); dragIdx=null; render(); });
    });
  }

  /* ── Build payload ── */
  function payload() {
    var emails = document.getElementById('f-emails').value.split(',').map(function(s){return s.trim();}).filter(Boolean);
    var slideStyle = (document.querySelector('#slidePills .pill.on')||{getAttribute:function(){return 'sans';}}).getAttribute('data-val');
    return {
      title:     document.getElementById('f-title').value.trim(),
      slug:      document.getElementById('f-slug').value.trim().toLowerCase().replace(/[^a-z0-9-]/g,'-'),
      output_type: document.getElementById('f-output').value,
      intro:     document.getElementById('f-intro').value.trim(),
      schema:    { fields: fields },
      settings:  {
        thank_you:     document.getElementById('f-thanks').value.trim(),
        submit_label:  document.getElementById('f-submit-label').value.trim() || 'Submit',
        notify_emails: emails,
        sms_to:        document.getElementById('f-sms').value.trim(),
        slide_style:   slideStyle,
      },
    };
  }

  /* ── Save ── */
  document.getElementById('saveBtn').addEventListener('click', function() {
    var btn=this; var p=payload();
    if (!p.title) { showErr('Please enter a form title.'); return; }
    if (!p.slug)  { showErr('Please enter a URL slug.'); return; }
    if (!fields.length) { showErr('Add at least one field.'); return; }
    var emptyKey=fields.find(function(f){return !f.key||!f.label;});
    if (emptyKey) { showErr('Each field needs a key and a label.'); return; }
    btn.disabled=true;
    fetch(SAVE_URL, { method:'POST', headers:{'X-CSRF-TOKEN':token,'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body:JSON.stringify(p) })
      .then(function(r){return r.json().then(function(d){return{ok:r.ok,d:d};});})
      .then(function(res) {
        btn.disabled=false;
        if (res.ok && res.d.ok) {
          showPip(IS_EDIT ? 'Saved.' : 'Form created!');
          if (!IS_EDIT && res.d.slug) { window.location.href='/admin/intake/'+res.d.slug+'/builder'; }
        } else { showErr(res.d.message||res.d.error||'Could not save — try again.'); }
      })
      .catch(function(){ btn.disabled=false; showErr('Network error.'); });
  });

  /* ── Init ── */
  render();
})();
</script>
</body>
</html>
