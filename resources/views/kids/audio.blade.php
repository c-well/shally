@php
    // Follow the same published service windows the church already manages.
    $churchAudio = [
        'serverNow' => now()->getTimestampMs(),
        'timezone' => \App\Models\ServiceTime::TZ,
        'windows' => \App\Models\ServiceTime::published()->filter(fn ($service) => $service->live_from && $service->live_until)->map(fn ($service) => [
            'days' => array_map('intval', $service->days ?? []),
            'from' => substr($service->live_from, 0, 5),
            'until' => substr($service->live_until, 0, 5),
        ])->values()->all(),
    ];
@endphp
<script>
const CHURCH_AUDIO = @json($churchAudio);
// Opt-in musical cues only. Church hours override a remembered sound-on preference.
const GameAudio=(()=>{
  let enabled=false,ctx,quiet=false;const active=new Set(),clientStart=Date.now();
  try{enabled=localStorage.getItem('cop_game_sound')==='on';}catch(_){}
  const clock=new Intl.DateTimeFormat('en-US',{timeZone:CHURCH_AUDIO.timezone,weekday:'short',hour:'2-digit',minute:'2-digit',hourCycle:'h23'});
  function isChurchTime(){
    const parts=Object.fromEntries(clock.formatToParts(new Date(CHURCH_AUDIO.serverNow+Date.now()-clientStart)).map(p=>[p.type,p.value]));
    const day=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].indexOf(parts.weekday),minute=Number(parts.hour)*60+Number(parts.minute);
    const minutes=s=>{const [h,m]=s.split(':').map(Number);return h*60+m;};
    return CHURCH_AUDIO.windows.some(w=>{const from=minutes(w.from),until=minutes(w.until);return from<=until?w.days.includes(day)&&minute>=from&&minute<until:(w.days.includes(day)&&minute>=from)||(w.days.includes((day+6)%7)&&minute<until);});
  }
  function silence(){enabled=false;active.forEach(o=>{try{o.stop();}catch(_){}});active.clear();}
  function sync(){
    const button=document.getElementById('soundToggle');if(button){button.textContent=enabled?'Sound on':'Sound off';button.setAttribute('aria-pressed',String(enabled));}
    const message=enabled?'Church hours: sound is on. Please use headphones.':'Church hours: sound starts off. Use headphones if you turn it on.';
    document.querySelectorAll('[data-quiet-notice]').forEach(notice=>{notice.hidden=!quiet;if(notice.textContent!==message)notice.textContent=message;});
  }
  function refresh(){const next=isChurchTime();if(next&&!quiet)silence();quiet=next;sync();}
  function play(kind){refresh();if(!enabled)return;try{ctx??=new(window.AudioContext||window.webkitAudioContext)();if(ctx.state==='suspended')ctx.resume();const notes=kind==='win'?[523,659,784,1047]:kind==='match'?[659,880]:[440];notes.forEach((hz,i)=>{const o=ctx.createOscillator(),g=ctx.createGain(),t=ctx.currentTime+i*.085;o.type='sine';o.frequency.value=hz;g.gain.setValueAtTime(0,t);g.gain.linearRampToValueAtTime(.055,t+.012);g.gain.exponentialRampToValueAtTime(.001,t+.19);o.connect(g);g.connect(ctx.destination);active.add(o);o.onended=()=>active.delete(o);o.start(t);o.stop(t+.2);});}catch(_){} }
  function toggle(){refresh();if(enabled)silence();else enabled=true;
    // During a service, unmuting is an explicit choice for this page only.
    if(!quiet)try{localStorage.setItem('cop_game_sound',enabled?'on':'off');}catch(_){}
    sync();if(enabled)play('match');return enabled;
  }
  refresh();setInterval(refresh,15000);document.addEventListener('visibilitychange',refresh);
  return{play,toggle};
})();
</script>
