<script>
// One serialized move stream per game; retry the same sequence after an uncertain response.
const GameSession = (() => {
  const preview = new URLSearchParams(location.search).get('preview') === '1';
  const key = preview ? 'cop_kid_preview' : 'cop_kid';
  let player, run, retryAction, busy = false;
  const status = document.getElementById('saveStatus');
  const retry = document.getElementById('retrySave');
  try { player = JSON.parse(localStorage.getItem(key) || 'null'); } catch (_) {}
  function remember() { try { localStorage.setItem(key, JSON.stringify(player)); } catch (_) {} }
  function paint() {
    document.getElementById('who').textContent = preview ? 'Practice preview · no public stars' : `${player.name} · ★ ${player.total_stars || 0}`;
  }
  function message(text) { if (status) status.textContent = text; }
  async function post(url, body) {
    const controller = new AbortController(); const timer = setTimeout(() => controller.abort(), 15000);
    try {
      const r = await fetch(url, {method:'POST',signal:controller.signal,headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(body)});
      const d = await r.json();
      if (!r.ok) {const error=new Error(d.message || 'Could not save this move.');error.status=r.status;throw error;}
      return d;
    } catch (e) { if (e.name === 'AbortError' || e instanceof TypeError) throw new Error('Connection interrupted. Your move is waiting to save.'); throw e; }
    finally { clearTimeout(timer); }
  }
  function runKey() { return `${key}_run_${LEVEL.id}`; }
  async function begin(fresh = false) {
    let saved; try { saved = fresh ? null : localStorage.getItem(runKey()); } catch (_) {}
    run = await post('{{ route('kids.begin') }}', {token:player.token,level_id:LEVEL.id,run_token:saved || null});
    player.total_stars=run.total_stars; remember(); paint();
    try { localStorage.setItem(runKey(),run.run_token); } catch (_) {}
    message(preview ? 'Test play is marked for administrators and excluded from public rewards.' : 'Progress saves as you play.');
    return run;
  }
  function ready() {
    return new Promise(resolve => {
      const go = document.getElementById('nameGo'); const gate = document.getElementById('nameGate');
      const input = document.getElementById('nameInput'); const error = document.getElementById('nameError');
      async function load() {
        if(go.disabled)return;
        go.disabled = true; error.textContent = '';
        try {
          if (!player?.token) {
            const name = preview ? 'Practice preview' : input.value.trim();
            if (!name) throw new Error('Choose a nickname to begin.');
            player = await post('{{ route('kids.register') }}',{name,preview}); remember();
          }
          const data = await begin(); gate.classList.remove('show'); resolve(data);
        } catch (e) { if(e.status===404){player=null;try{localStorage.removeItem(key);}catch(_){} error.textContent='Let’s start a fresh player profile. Choose a nickname below.';}else error.textContent=e.message;gate.classList.add('show'); }
        finally { go.disabled = false; }
      }
      go.addEventListener('click',load); input.addEventListener('keydown',e=>{if(e.key==='Enter')load();});
      if (player?.token || preview) load(); else { gate.classList.add('show'); input.focus(); }
    });
  }
  async function move(event) {
    if (busy) throw new Error('Wait for the previous move.');
    busy = true;
    const payload = {token:player.token,run_token:run.run_token,sequence:run.sequence+1,event};
    return new Promise(resolve => {
      async function attempt() {
        if(retry) retry.hidden=true;
        message('Saving…');
        try {
          const d=await post('{{ route('kids.save') }}',payload);
          run.state=d.state; run.sequence=d.sequence; player.total_stars=d.total_stars; remember(); paint();
          busy=false; retryAction=null; message(preview?'Practice preview · no public stars':d.state.completed?'Saved — well done!':'Progress saved');
          if(d.state.completed) { try {localStorage.removeItem(runKey());} catch(_){} }
          resolve(d);
        } catch(e) { message(e.message); retryAction=[404,409,419,422].includes(e.status)?()=>location.reload():attempt;if(retry){retry.textContent=[404,409,419,422].includes(e.status)?'Reload game':'Retry saving';retry.hidden=false;} }
      }
      attempt();
    });
  }
  if(retry) retry.addEventListener('click',()=>retryAction?.());
  // Keep keyboard focus inside visible dialogs and remove covered game controls from navigation.
  let activeDialog=null, previousFocus=null;
  const syncDialogs=()=>{
    const current=Array.from(document.querySelectorAll('.ov.show')).pop()||null;
    document.querySelectorAll('main,.game,body>.top').forEach(el=>el.inert=!!current);
    document.querySelectorAll('.ov').forEach(el=>el.inert=el!==current);
    if(current!==activeDialog){
      if(current){if(!activeDialog)previousFocus=document.activeElement;queueMicrotask(()=>current.querySelector('input,button,a')?.focus());}
      else if(previousFocus?.isConnected)previousFocus.focus();
      activeDialog=current;
    }
  };
  document.querySelectorAll('.ov').forEach(ov=>{ov.setAttribute('role','dialog');ov.setAttribute('aria-modal','true');new MutationObserver(syncDialogs).observe(ov,{attributes:true,attributeFilter:['class']});});
  document.addEventListener('keydown',e=>{if(e.key!=='Tab')return;const ov=Array.from(document.querySelectorAll('.ov.show')).pop();if(!ov)return;const items=Array.from(ov.querySelectorAll('button,a,input,select')).filter(el=>!el.disabled&&el.getClientRects().length);if(!items.length)return;const first=items[0],last=items[items.length-1];if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}});
  return {ready,move,begin,preview,message};
})();
</script>
