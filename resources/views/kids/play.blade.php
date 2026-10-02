<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $level->reference }} — Scripture Games</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; -webkit-tap-highlight-color: transparent; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 16px clamp(16px,4vw,28px); display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); }
  .top a { font-size: 12px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; }
  .top a:hover { color: var(--teal); }
  .who { font-size: 13px; color: var(--ink-soft); }
  .who b { color: var(--teal); }
  .star { color: var(--brass); }

  main { max-width: 720px; margin: 0 auto; padding: clamp(20px,4vh,40px) clamp(16px,4vw,28px) 80px; text-align: center; }
  .ref { font-size: 11px; font-weight: 600; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); }
  h1 { margin-top: 8px; font-family: 'IBM Plex Serif', serif; font-size: clamp(26px,5vw,38px); font-weight: 500; }
  .hint { margin-top: 10px; font-size: 15px; color: var(--ink-soft); }

  /* Word search */
  .ws { margin: 26px auto 0; display: inline-block; touch-action: none; user-select: none; }
  .ws-grid { display: grid; gap: 3px; }
  .cell { width: var(--cs); height: var(--cs); display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--line); border-radius: 7px; font-weight: 600; font-size: calc(var(--cs) * 0.46); color: var(--ink); cursor: pointer; transition: background .1s, color .1s, transform .1s; }
  .cell.sel { background: color-mix(in srgb, var(--teal) 22%, #fff); }
  .cell.found { background: var(--teal); color: #fff; border-color: var(--teal); }
  .words { margin: 22px auto 0; display: flex; flex-wrap: wrap; gap: 8px 12px; justify-content: center; max-width: 560px; }
  .word { font-size: 15px; font-weight: 600; color: var(--ink-soft); padding: 6px 12px; background: #fff; border: 1px solid var(--line); border-radius: 999px; transition: all .2s; }
  .word.got { color: #fff; background: var(--teal); border-color: var(--teal); text-decoration: line-through; }

  /* Name gate + win overlay */
  .ov { position: fixed; inset: 0; background: color-mix(in srgb, var(--ink) 55%, transparent); display: flex; align-items: center; justify-content: center; padding: 24px; z-index: 50; opacity: 0; pointer-events: none; transition: opacity .25s; }
  .ov.show { opacity: 1; pointer-events: auto; }
  .card { background: var(--parchment); border-radius: 18px; padding: 34px 30px; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 30px 70px -30px rgba(0,0,0,.5); transform: translateY(10px) scale(.98); transition: transform .28s; }
  .ov.show .card { transform: none; }
  .card h2 { font-family: 'IBM Plex Serif', serif; font-size: 28px; font-weight: 500; }
  .card p { margin-top: 12px; color: var(--ink-soft); line-height: 1.55; }
  .card .verse { margin-top: 18px; font-family: 'IBM Plex Serif', serif; font-style: italic; font-size: 19px; line-height: 1.5; color: var(--ink); }
  .card input { margin-top: 18px; width: 100%; font: inherit; font-size: 17px; padding: 13px 16px; border: 1px solid var(--line); border-radius: 10px; text-align: center; }
  .btn { margin-top: 18px; font: inherit; font-size: 15px; font-weight: 600; padding: 14px 30px; border: 0; border-radius: 10px; background: var(--teal); color: #fff; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); }
  .btn-ghost { background: transparent; color: var(--teal); }
  .stars-big { font-size: 40px; letter-spacing: 6px; margin-top: 14px; }
  .pop { animation: pop .5s ease; }
  @keyframes pop { 0%{transform:scale(.6);opacity:0} 60%{transform:scale(1.12)} 100%{transform:scale(1)} }
  /* Memory match */
  .mm { margin: 26px auto 0; display: grid; gap: 10px; max-width: 460px; }
  .mcard { aspect-ratio: 3/4; border: 2px solid var(--teal); border-radius: 14px; background: var(--teal); color: #fff; font-weight: 600; font-size: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 6px; text-align: center; }
  .mcard .w { display: none; }
  .mcard.up, .mcard.done { background: #fff; color: var(--ink); font-size: clamp(13px,3.6vw,17px); animation: flip .34s ease; }
  .mcard.up .q, .mcard.done .q { display: none; }
  .mcard.up .w, .mcard.done .w { display: inline; }
  .mcard.done { background: color-mix(in srgb, var(--teal) 14%, #fff); }
  @keyframes flip { 0% { transform: rotateY(82deg); } 100% { transform: rotateY(0); } }

  /* Hidden words */
  .hw { margin: 26px auto 0; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; max-width: 600px; }
  .htile { font: inherit; font-size: clamp(17px,4vw,21px); font-weight: 600; padding: 12px 18px; border-radius: 12px; border: 2px dashed var(--line); background: #fff; color: var(--ink-soft); cursor: pointer; transition: all .18s; }
  .htile:hover { border-color: var(--teal); }
  .htile.on { border-style: solid; border-color: var(--teal); background: color-mix(in srgb, var(--teal) 10%, #fff); color: var(--ink); animation: pop .35s ease; cursor: default; }

  .soon { margin-top: 40px; padding: 30px; background: #fff; border: 1px dashed var(--line); border-radius: 14px; color: var(--ink-soft); }

  .ov{visibility:hidden}.ov.show{visibility:visible}.card{max-height:90dvh;overflow:auto}.small{font:12px/1.6 'IBM Plex Sans',sans-serif!important;margin-top:12px;color:var(--ink-soft)}.small a{color:var(--teal)}.sr-only{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}.journey{max-width:460px;margin:22px auto 0;font-size:13px;font-weight:600;color:var(--teal)}progress{display:block;width:100%;height:9px;margin-top:8px;accent-color:var(--teal);border:0}.tools{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:18px}.tools button{font:inherit;font-size:13px;padding:10px 14px;border:1px solid var(--line);border-radius:24px;background:white;color:var(--teal);cursor:pointer}.feedback{min-height:24px;margin-top:18px;font-size:14px;color:var(--teal)}.mm{grid-template-columns:repeat(3,minmax(0,1fr));max-width:440px}.mcard{aspect-ratio:1.15;font:600 22px 'IBM Plex Sans',sans-serif;min-width:0}.mcard .w{overflow-wrap:anywhere}.mcard .icon{display:block;font-size:32px;margin-bottom:4px}.mcard.up,.mcard.done{font-size:clamp(10px,2.7vw,15px)}.mcard.done{border-color:#709879}.htile:disabled{opacity:.5;cursor:default}.htile.next{border:2px solid var(--teal);box-shadow:0 0 0 4px #dfeceb}.htile.on{opacity:1}.cell.hinted{outline:3px solid #c0923f;outline-offset:1px}.cell{padding:0;font-family:inherit}.btn{display:inline-block;text-decoration:none}.btn[hidden]{display:none}.stars-big.preview{font:600 18px system-ui;letter-spacing:0}progress{border-radius:10px;overflow:hidden}progress::-webkit-progress-bar{background:#e0e8e4}progress::-webkit-progress-value{background:var(--teal);border-radius:10px}.who{max-width:60%;overflow-wrap:anywhere}.ws{max-width:100%}@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a href="{{ route('kids') }}">@include('partials._arl') Games</a>
  <div class="who" id="who"></div>
</div>

<main>
  <div class="ref">{{ $level->reference }}</div>
  <h1>{{ $level->title ?: $level->book }}</h1>

  <div class="journey"><span id="progressText" aria-live="polite">Ready for an adventure?</span><progress id="progressBar" value="0" max="1" aria-label="Game progress"></progress></div>
  <div class="tools"><button id="soundToggle" type="button" aria-pressed="true">🔊 Sound on</button>@if($level->game_type==='word_search')<button id="hintButton" type="button">💡 Show a starting letter</button>@endif</div>
  @if ($level->game_type === 'word_search')
    <p class="hint">Drag across a word, or tap its first and last letter. Words can run across, down, or diagonally.</p>
    <div class="ws"><div class="ws-grid" id="grid"></div></div>
    <div class="words" id="wordlist"></div>
  @elseif ($level->game_type === 'memory_match')
    <p class="hint">Turn over two cards. Match the pictures and words, and say them together!</p>
    <div class="mm" id="mm"></div>
  @elseif ($level->game_type === 'hidden_words')
    <p class="hint">Follow the glowing word. Tap, listen, and say it with me!</p>
    <div class="hw" id="hw"></div>
  @endif
<p id="feedback" class="feedback" aria-live="polite"></p><p id="saveStatus" class="small" role="status"></p><button id="retrySave" type="button" class="btn" hidden>Retry saving</button>
</main>

@include('kids.player-gate')

{{-- Win --}}
<div class="ov" id="winOv" role="dialog" aria-modal="true" aria-label="Game complete">
  <div class="card">
    <div class="stars-big pop" id="winStars">⭐️⭐️⭐️</div>
    <h2>You did it!</h2>
    <p>You practiced <b>{{ $level->reference }}</b></p>
    <div class="verse">“{{ $level->verse_text }}”</div>
    <div><button class="btn" id="listenVerse">🔊 Hear the verse</button></div>
    @if($nextLevel)<div><a class="btn" href="{{ route('kids.play',$nextLevel) }}{{ request()->boolean('preview') ? '?preview=1' : '' }}">Next adventure →</a></div>@endif
    <div><a class="btn btn-ghost" href="{{ route('kids') }}">Choose another game</a></div>
    <div><button class="btn btn-ghost" onclick="location.reload()">Play again</button></div>
  </div>
</div>

@php $levelData = ['id' => $level->id, 'type' => $level->game_type, 'band' => $level->age_band, 'reference' => $level->reference, 'verse' => $level->verse_text]; @endphp
<script>
const LEVEL = @json($levelData);
</script>
@include('kids.session')
<script>
let challenge, state, locked=false, sound=true;
const feedback=document.getElementById('feedback');
const icons={SHEPHERD:'🐑',LORD:'✨',WANT:'🌿',LOVE:'💛',LOVETH:'💛',GOD:'✨',CREATED:'🌱',BEGINNING:'🌅',HEAVEN:'☁️',EARTH:'🌍',HONOUR:'🏅',FATHER:'👨',MOTHER:'👩',LIGHT:'🕯️',LAMP:'🏮',PEACE:'🕊️',PEACEMAKERS:'🕊️',CHILDREN:'🧒'};
function speak(text,force=false){if((!sound&&!force) || !('speechSynthesis' in window))return;try{speechSynthesis.cancel();const u=new SpeechSynthesisUtterance(text);u.lang='en-US';u.rate=.8;speechSynthesis.speak(u);}catch(_){}}
document.getElementById('soundToggle').addEventListener('click',e=>{sound=!sound;e.currentTarget.textContent=sound?'🔊 Sound on':'🔇 Sound off';e.currentTarget.setAttribute('aria-pressed',String(sound));if(!sound && 'speechSynthesis'in window)speechSynthesis.cancel();});
document.getElementById('listenVerse').addEventListener('click',()=>speak(LEVEL.verse,true));
function progress(){
  const unit=LEVEL.type==='memory_match'?'pairs': 'words';
  document.getElementById('progressText').textContent=`${state.progress} of ${challenge.total} ${unit} discovered`;
  document.getElementById('progressBar').max=challenge.total;document.getElementById('progressBar').value=state.progress;
}
function finish(){
  if(!state.completed)return;
  const ov=document.getElementById('winOv');ov.classList.add('show');document.getElementById('listenVerse').focus();
  if(GameSession.preview){document.getElementById('winStars').textContent='Practice complete ✨';document.getElementById('winStars').classList.add('preview');}
}
async function act(event){const d=await GameSession.move(event);state=d.state;progress();return d;}
function memory(){
  const root=document.getElementById('mm');
  challenge.cards.forEach((word,i)=>{
    const card=document.createElement('button');card.className='mcard';card.type='button';
    const back=document.createElement('span');back.className='q';back.textContent='✦';
    const front=document.createElement('span');front.className='w';
    const icon=document.createElement('span');icon.className='icon';icon.textContent=icons[word] || ['🌟','🌻','🦋','🌈','🌳','🕊️'][challenge.cards.indexOf(word)%6];icon.setAttribute('aria-hidden','true');
    front.append(icon,document.createTextNode(word));card.append(back,front);root.append(card);
    const matched=(state.matched||[]).includes(i);card.classList.toggle('done',matched);card.disabled=matched;card.setAttribute('aria-label',matched?`${word}, matched`:`Card ${i+1}, face down`);
    if(state.first===i){card.classList.add('up');card.setAttribute('aria-label',word);}
    card.addEventListener('click',async()=>{
      if(locked || card.classList.contains('done') || state.first===i)return;
      locked=true;const previous=state.first;card.classList.add('up');card.setAttribute('aria-label',word);speak(word);
      await act({type:'flip',index:i});
      if(previous!==undefined && previous!==null){
        if((state.matched||[]).includes(i)){
          [previous,i].forEach(n=>{const c=root.children[n];c.classList.remove('up');c.classList.add('done');c.disabled=true;c.setAttribute('aria-label',`${challenge.cards[n]}, matched`);});feedback.textContent='A match! Well remembered.';
        }else{
          feedback.textContent='Keep exploring — remember where those two live.';
          await new Promise(r=>setTimeout(r,900));[previous,i].forEach(n=>{root.children[n].classList.remove('up');root.children[n].setAttribute('aria-label',`Card ${n+1}, face down`);});
        }
      }
      locked=false;finish();
    });
  });
}
function hidden(){
  const root=document.getElementById('hw');
  challenge.words.forEach((word,i)=>{
    const tile=document.createElement('button');tile.className='htile';tile.type='button';root.append(tile);
    tile.addEventListener('click',async()=>{
      if(locked || i!==state.progress)return;
      locked=true;speak(word);await act({type:'reveal',index:i});paint();locked=false;
      feedback.textContent=state.completed?'Now say the whole verse together!':'Wonderful! Follow the next glowing word.';
      if(!state.completed)root.children[state.progress].focus();finish();
    });
  });
  function paint(){Array.from(root.children).forEach((tile,i)=>{tile.textContent=i<state.progress?challenge.words[i]:'•••';tile.disabled=i!==state.progress;tile.classList.toggle('on',i<state.progress);tile.classList.toggle('next',i===state.progress);tile.setAttribute('aria-label',i<state.progress?challenge.words[i]:`Reveal word ${i+1}`);});}
  paint();
}
function wordSearch(){
  const root=document.getElementById('grid'),list=document.getElementById('wordlist'),size=challenge.grid.length;
  root.style.gridTemplateColumns=`repeat(${size},1fr)`;
  function sizeCells(){root.style.setProperty('--cs',Math.min(40,Math.floor((Math.min(window.innerWidth-48,560))/size)-3)+'px');}
  sizeCells();window.addEventListener('resize',sizeCells);
  challenge.grid.forEach((row,r)=>row.forEach((letter,c)=>{
    const cell=document.createElement('button');cell.type='button';cell.className='cell';cell.textContent=letter;cell.dataset.r=r;cell.dataset.c=c;cell.setAttribute('aria-label',`${letter}, row ${r+1}, column ${c+1}`);root.append(cell);
    cell.addEventListener('click',e=>{if(e.detail===0)tap(cell);});
    cell.addEventListener('keydown',e=>{const offsets={ArrowLeft:-1,ArrowRight:1,ArrowUp:-size,ArrowDown:size};if(e.key in offsets){e.preventDefault();const next=root.children[r*size+c+offsets[e.key]];if(next)next.focus();}if(e.key==='Escape'){first=null;clear();}});
  }));
  challenge.words.forEach(w=>{const chip=document.createElement('span');chip.className='word';chip.dataset.w=w;chip.textContent=w;list.append(chip);});
  let first=null,dragStart=null,moved=false,path=[];
  function clear(){root.querySelectorAll('.sel,.hinted').forEach(c=>c.classList.remove('sel','hinted'));path=[];}
  function line(a,b){const r=+a.dataset.r,c=+a.dataset.c,er=+b.dataset.r,ec=+b.dataset.c;if(r!==er&&c!==ec&&Math.abs(er-r)!==Math.abs(ec-c))return [];const dr=Math.sign(er-r),dc=Math.sign(ec-c),n=Math.max(Math.abs(er-r),Math.abs(ec-c))+1;return Array.from({length:n},(_,i)=>root.children[(r+dr*i)*size+c+dc*i]);}
  function show(a,b){clear();path=line(a,b);path.forEach(c=>c.classList.add('sel'));}
  function cellAt(e){const cell=document.elementFromPoint(e.clientX,e.clientY);return cell?.classList.contains('cell')&&root.contains(cell)?cell:null;}
  function paintFound(){
    Array.from(list.children).forEach(chip=>chip.classList.toggle('got',(state.found||[]).includes(chip.dataset.w)));
    (state.found||[]).forEach(w=>{const [a,b]=challenge.positions[w];line(root.children[a[0]*size+a[1]],root.children[b[0]*size+b[1]]).forEach(c=>c.classList.add('found'));});
  }
  async function submit(a,b){
    if(locked)return;
    const selected=line(a,b),word=selected.map(c=>c.textContent).join('');const match=challenge.words.find(w=>w===word||w===Array.from(word).reverse().join(''));
    first=null;
    if(!match || (state.found||[]).includes(match)){clear();feedback.textContent=match?'You already found that word. Try another!':'Try a straight line between the first and last letter.';return;}
    locked=true;
    await act({type:'word',start:[+a.dataset.r,+a.dataset.c],end:[+b.dataset.r,+b.dataset.c]});clear();paintFound();speak(match);feedback.textContent=`You found ${match.toLowerCase()}!`;locked=false;finish();
  }
  function tap(cell){if(locked)return;if(!first){first=cell;clear();cell.classList.add('sel');feedback.textContent='Now choose the last letter.';}else submit(first,cell);}
  root.addEventListener('pointerdown',e=>{if(locked)return;const cell=cellAt(e);if(!cell)return;e.preventDefault();dragStart=cell;moved=false;});
  window.addEventListener('pointermove',e=>{if(!dragStart)return;const cell=cellAt(e);if(cell&&cell!==dragStart){moved=true;show(dragStart,cell);}});
  window.addEventListener('pointerup',e=>{if(!dragStart)return;const a=dragStart,b=cellAt(e);dragStart=null;if(!b){clear();return;}if(moved)submit(a,b);else tap(b);});
  window.addEventListener('pointercancel',()=>{dragStart=null;clear();});
  document.getElementById('hintButton').addEventListener('click',()=>{if(locked)return;const w=challenge.words.find(w=>!(state.found||[]).includes(w));if(!w)return;first=null;clear();const [r,c]=challenge.positions[w][0];root.children[r*size+c].classList.add('hinted');feedback.textContent=`Look for ${w.toLowerCase()} starting at the golden letter.`;});
  paintFound();
}
GameSession.ready().then(data=>{challenge=data.challenge;state=data.state;progress();if(LEVEL.type==='memory_match')memory();else if(LEVEL.type==='hidden_words')hidden();else wordSearch();}).catch(()=>GameSession.message('Please reload to start your game.'));
</script>
</body></html>
