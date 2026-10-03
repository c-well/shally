<script>
// Opt-in musical cues only. No synthesized speech or autoplay.
const GameAudio=(()=>{let enabled=false,ctx;try{enabled=localStorage.getItem('cop_game_sound')==='on';}catch(_){}
function play(kind){if(!enabled)return;try{ctx??=new(window.AudioContext||window.webkitAudioContext)();if(ctx.state==='suspended')ctx.resume();const notes=kind==='win'?[523,659,784,1047]:kind==='match'?[659,880]:[440];notes.forEach((hz,i)=>{const o=ctx.createOscillator(),g=ctx.createGain(),t=ctx.currentTime+i*.085;o.type='sine';o.frequency.value=hz;g.gain.setValueAtTime(0,t);g.gain.linearRampToValueAtTime(.055,t+.012);g.gain.exponentialRampToValueAtTime(.001,t+.19);o.connect(g);g.connect(ctx.destination);o.start(t);o.stop(t+.2);});}catch(_){} }
function toggle(){enabled=!enabled;try{localStorage.setItem('cop_game_sound',enabled?'on':'off');}catch(_){}if(enabled)play('match');return enabled;}
const button=document.getElementById('soundToggle');if(button){button.textContent=enabled?'Sound on':'Sound off';button.setAttribute('aria-pressed',String(enabled));}
return{play,toggle};})();
</script>
