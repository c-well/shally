<script>
const KidsMascot=(()=>{
  const reduced=window.matchMedia('(prefers-reduced-motion: reduce)');let stopped=false;try{stopped=localStorage.getItem('cop_mascot_motion')==='off';}catch(_){}
  function sync(){const off=stopped||reduced.matches;document.body.classList.toggle('mascot-still',off);document.querySelectorAll('[data-mascot-motion]').forEach(button=>{button.textContent=reduced.matches?'Motion reduced':stopped?'Play mascot motion':'Pause mascot motion';button.disabled=reduced.matches;button.setAttribute('aria-pressed',String(!off));});}
  function cheer(){document.querySelectorAll('[data-sheep]').forEach(sheep=>{if(sheep.classList.contains('cheer'))return;sheep.classList.add('cheer');sheep.dispatchEvent(new Event('sheep:cheer'));setTimeout(()=>sheep.classList.remove('cheer'),1100);});}
  document.querySelectorAll('[data-sheep]').forEach(sheep=>sheep.addEventListener('click',()=>{sheep.classList.add('hello');cheer();setTimeout(()=>sheep.classList.remove('hello'),2000);}));
  document.querySelectorAll('[data-mascot-motion]').forEach(button=>button.addEventListener('click',()=>{stopped=!stopped;try{localStorage.setItem('cop_mascot_motion',stopped?'off':'on');}catch(_){}sync();}));
  reduced.addEventListener('change',sync);sync();return{cheer};
})();
</script>

<script type="module" src="/kids-assets/sheep-3d-v1.js"></script>
