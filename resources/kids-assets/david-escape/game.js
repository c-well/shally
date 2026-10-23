import {createState,tick,restart,STEP} from './core.js';
import {checkpoints,JONATHAN,VERSION} from './world.js';
import {createScene} from './scene.js';
const $=s=>document.querySelector(s),canvas=$('#world'),menu=$('#menu'),primary=$('#menu-primary'),secondary=$('#menu-secondary');
let saved=0;try{const v=JSON.parse(localStorage.getItem('david-escape-checkpoint')||'null');if(v?.version===VERSION&&Number.isInteger(v.checkpoint))saved=Math.max(0,Math.min(2,v.checkpoint));}catch{}
let s=createState(saved),scene,mode='intro',last=0,accumulator=0,pendingTalk=false,crouch=false,rewindBurst=0,primaryAction,secondaryAction;
const keys=new Set(),touch={x:0,y:0,jump:false,rewind:false},tracker=window.LittleLightTracking;
let trackedChapter=null,storyRead=false,gamepadPrevious=[],gamepadPresent=false,lastAnnounced='';
function announce(text){if(lastAnnounced!==text){$('#context').textContent=text;lastAnnounced=text;}}
function clearInput(){keys.clear();touch.x=touch.y=0;touch.jump=touch.rewind=false;pendingTalk=false;$('#thumb').style.transform='';}
function save(){try{localStorage.setItem('david-escape-checkpoint',JSON.stringify({version:VERSION,checkpoint:s.checkpoint}));}catch{}}
function report(status,dt=0,active=false,forceFinish=false){
 const starts=[3,-34,-78],ends=[-34,-78,-109];
 const chapter=trackedChapter??s.checkpoint;
 const progress=Math.max(0,Math.min(99,Math.floor((starts[chapter]-s.p.z)/(starts[chapter]-ends[chapter])*100)));
 tracker?.sample(dt,{status,furthest_percent:forceFinish?99:progress,scrolls:storyRead?1:0,lights:0,sheep:0,stones:0},active);
}
function startTracking(){if(trackedChapter===null){trackedChapter=s.checkpoint;tracker?.begin(s.checkpoint+1);}}
function endTracking(complete=false){if(trackedChapter!==null){report(complete?'complete':'left',0,false,complete);tracker?.end();trackedChapter=null;}}
function play(){menu.close();mode='playing';clearInput();startTracking();canvas.focus({preventScroll:true});scene?.recenter();}
function dialog(title,copy,button,action,{eyebrow='DAVID · A WAY THROUGH',second=null,note='Your checkpoint saves on this device.',controls=false}={}){
 clearInput();mode='menu';$('#menu-title').textContent=title;$('#menu-copy').textContent=copy;$('#menu-eyebrow').textContent=eyebrow;$('#menu-note').textContent=note;$('#quick-controls').hidden=!controls;primary.textContent=button;primary.disabled=false;primaryAction=action;secondary.hidden=!second;if(second){secondary.textContent=second.label;secondaryAction=second.action;}
 if(!menu.open)menu.showModal();primary.focus();
}
function resetAll(){crouch=false;$('#crouch-touch').setAttribute('aria-pressed','false');endTracking();s=createState();storyRead=false;save();scene.recenter();play();updateHUD();}
function pause(){if(mode!=='playing')return;report('paused');dialog('Take a breath.', 'David is safe while the adventure is paused.', 'Keep going',play,{controls:true,second:{label:'Restart at this checkpoint',action:()=>{endTracking();restart(s);play();}}});}
function story(){if(mode==='playing')report('reading');const c=checkpoints[s.checkpoint];storyRead=true;dialog(c.title,c.story,'Back to adventure',play,{eyebrow:c.ref,note:'The story spans separate events in 1 Samuel 19–20. The rooftop route, guards’ patrols and rewind are imagined gameplay.'});}
function failure(){report('retry');const canRewind=s.history.length>20;dialog(s.failure==='caught'?'They spotted David.':'A second chance.',s.failure==='caught'?'Break their line of sight. Walls and crates can hide you.':'Hold jump a little longer. David will catch a ledge if he can reach it.',canRewind?'Rewind and try again':'Return to checkpoint',()=>{if(canRewind){menu.close();mode='playing';rewindBurst=Math.min(2.4,s.history.length*STEP-.02);clearInput();}else{endTracking();restart(s);play();}},{second:canRewind?{label:'Return to checkpoint',action:()=>{endTracking();restart(s);play();}}:null,note:'Rewind is a retry tool, not a power in the Bible story.'});}
function updateHUD(){const labels=['01 · THE ROOFTOPS','02 · THE COURTYARD','03 · THE PROMISE'];$('#section-label').textContent=labels[s.checkpoint];$('#objective').textContent=checkpoints[s.checkpoint].goal;$('#mission-detail').textContent=s.checkpoint===0?'Hold jump across gaps. Catch a ledge, then jump to climb.':s.checkpoint===1?'Crouch behind low cover. Watch the gold sight cones.':'An arrow was the warning. A friend kept his promise.';}
function finish(){endTracking(true);report('complete');try{localStorage.setItem('david-escape-finished','1');}catch{};GameAudio.play('win');dialog('“Go in peace.”','Jonathan and David kept their promise of friendship, even when Saul’s anger made it dangerous. David left; Jonathan returned to the city.','Play the escape again',resetAll,{eyebrow:'JONATHAN · 1 SAMUEL 20:42',note:'Think about it: what does a faithful friend do when helping is difficult?',second:{label:'Choose another adventure',action:()=>location.assign('/kids')}});}
$('#pause').onclick=pause;$('#story').onclick=story;$('#soundToggle').onclick=()=>GameAudio.toggle();primary.onclick=()=>primaryAction?.();secondary.onclick=()=>secondaryAction?.();
menu.addEventListener('cancel',e=>{e.preventDefault();if(scene&&!s.failure&&!s.completed)play();});
const known=['KeyW','KeyA','KeyS','KeyD','ArrowUp','ArrowDown','ArrowLeft','ArrowRight','Space','KeyC','KeyR','KeyE','KeyV','Escape','KeyP'];
window.addEventListener('keydown',e=>{if(!known.includes(e.code))return;
 if(menu.open){if(e.code==='KeyR'&&s.failure&&s.history.length>20){e.preventDefault();menu.close();mode='playing';keys.add('KeyR');}return;}
 e.preventDefault();if(e.code==='Escape'||e.code==='KeyP'){pause();return;}
 keys.add(e.code);if(!e.repeat){if(e.code==='KeyC'){crouch=!crouch;$('#crouch-touch').setAttribute('aria-pressed',String(crouch));}if(e.code==='KeyV')scene.recenter();if(e.code==='KeyE')pendingTalk=true;}
});window.addEventListener('keyup',e=>keys.delete(e.code));
window.addEventListener('blur',()=>{clearInput();pause();});document.addEventListener('visibilitychange',()=>{if(document.hidden){clearInput();pause();}});
window.addEventListener('pagehide',()=>{report('paused');tracker?.suspend();});
function hold(button,key){button.addEventListener('pointerdown',e=>{e.preventDefault();button.setPointerCapture(e.pointerId);touch[key]=true;});for(const event of ['pointerup','pointercancel','lostpointercapture'])button.addEventListener(event,()=>touch[key]=false);}
hold($('#jump-touch'),'jump');hold($('#rewind-touch'),'rewind');
$('#crouch-touch').onclick=()=>{crouch=!crouch;$('#crouch-touch').setAttribute('aria-pressed',String(crouch));};$('#talk-touch').onclick=()=>pendingTalk=true;
const stick=$('#stick');let stickPointer=null;
function moveStick(e){const b=stick.getBoundingClientRect(),x=e.clientX-b.left-b.width/2,y=e.clientY-b.top-b.height/2,m=Math.hypot(x,y),scale=Math.min(1,34/Math.max(1,m));touch.x=x*scale/34;touch.y=y*scale/34;$('#thumb').style.transform=`translate(${x*scale}px,${y*scale}px)`;}
stick.addEventListener('pointerdown',e=>{e.preventDefault();stickPointer=e.pointerId;stick.setPointerCapture(e.pointerId);moveStick(e);});stick.addEventListener('pointermove',e=>{if(e.pointerId===stickPointer)moveStick(e);});for(const event of ['pointerup','pointercancel','lostpointercapture'])stick.addEventListener(event,()=>{stickPointer=null;touch.x=touch.y=0;$('#thumb').style.transform='';});
let look=null;canvas.addEventListener('pointerdown',e=>{if(mode!=='playing')return;look={id:e.pointerId,x:e.clientX};canvas.setPointerCapture(e.pointerId);});canvas.addEventListener('pointermove',e=>{if(look?.id!==e.pointerId)return;scene.rotate((look.x-e.clientX)*.006);look.x=e.clientX;});for(const event of ['pointerup','pointercancel'])canvas.addEventListener(event,()=>look=null);
function controls(){
 let dx=Number(keys.has('KeyD')||keys.has('ArrowRight'))-Number(keys.has('KeyA')||keys.has('ArrowLeft'))+touch.x;
 let forward=Number(keys.has('KeyW')||keys.has('ArrowUp'))-Number(keys.has('KeyS')||keys.has('ArrowDown'))-touch.y;
 let jump=keys.has('Space')||touch.jump,rewind=keys.has('KeyR')||touch.rewind||rewindBurst>0,duck=crouch;
 const pad=Array.from(navigator.getGamepads?.()||[]).find(p=>p?.mapping==='standard');
 if(pad){const down=pad.buttons.map(b=>b.pressed);if(Math.abs(pad.axes[0])>.18)dx+=pad.axes[0];if(Math.abs(pad.axes[1])>.18)forward-=pad.axes[1];jump||=down[0];duck||=down[1];rewind||=down[4];if(down[2]&&!gamepadPrevious[2])pendingTalk=true;if(down[9]&&!gamepadPrevious[9])pause();if(Math.abs(pad.axes[2])>.2)scene.rotate(-pad.axes[2]*.025);gamepadPrevious=down;gamepadPresent=true;}
 else if(gamepadPresent){gamepadPresent=false;gamepadPrevious=[];pause();}
 const yaw=scene.yaw();return {x:dx*Math.cos(yaw)-forward*Math.sin(yaw),z:-dx*Math.sin(yaw)-forward*Math.cos(yaw),jump,climb:pendingTalk,crouch:duck,rewind,interact:pendingTalk};
}
function step(){const input=controls();if(mode!=='playing')return;
 const before=s.checkpoint;tick(s,input);pendingTalk=false;if(rewindBurst>0)rewindBurst=Math.max(0,rewindBurst-STEP);
 document.body.classList.toggle('rewinding',s.rewinding);
 if(s.events.includes('jump')||s.events.includes('climb'))GameAudio.play('tap');
 if(s.events.includes('fall')||s.events.includes('caught')){tracker?.stumble();failure();return;}
 if(s.checkpoint!==before){endTracking(true);save();storyRead=true;updateHUD();const c=checkpoints[s.checkpoint];dialog(c.title,c.story,'Keep going',play,{eyebrow:c.ref,note:'Checkpoint saved. Your route continues beyond the lantern.'});return;}
 if(s.completed){finish();return;}
 report('playing',STEP,Math.hypot(input.x,input.z)>.1||input.jump||input.interact);
}
function drawHUD(){
 $('#rewind-time').value=s.history.length*STEP;$('#alert').value=s.alarm;$('#sight').hidden=s.alarm<.03;
 const near=s.checkpoint===2&&Math.hypot(s.p.x-JONATHAN.x,s.p.z-JONATHAN.z)<2.6;$('#talk-touch').hidden=!near;
 if(mode!=='playing'){announce('');return;}
 if(s.rewinding)announce('Rewinding · release when you are safe');else if(s.p.hang)announce('Ledge caught! Press jump to climb up.');else if(near)announce('Jonathan is here. Press E or tap Talk.');
 else if(s.checkpoint===0&&s.p.z>-4)announce('Move toward the lantern. Hold jump across the first gap.');
 else if(s.checkpoint===0&&s.p.z<-11&&s.p.z>-16)announce('Jump toward the high ledge. David can catch the edge.');
 else if(s.checkpoint===1&&s.p.z>-50)announce('C / crouch hides you behind low cover.');
 else if(s.checkpoint===2){const d=Math.round(Math.hypot(s.p.x-JONATHAN.x,s.p.z-JONATHAN.z));announce(`Jonathan · ${d} steps ahead`);}else announce('');
}
function frame(ms){const dt=Math.min(.05,last?(ms-last)/1000:STEP);last=ms;
 const menuPad=mode!=='playing'?Array.from(navigator.getGamepads?.()||[]).find(p=>p?.mapping==='standard'):null;
 if(menuPad){const down=menuPad.buttons.map(b=>b.pressed);$('#pad-hint').hidden=false;if((down[0]&&!gamepadPrevious[0])||(down[9]&&!gamepadPrevious[9]))primaryAction?.();gamepadPrevious=down;}
 if(mode==='playing'){accumulator+=dt;while(accumulator>=STEP){step();accumulator-=STEP;if(mode!=='playing'){accumulator=0;break;}}}else accumulator=0;
 drawHUD();scene.render(s,dt);requestAnimationFrame(frame);
}
window.addEventListener('resize',()=>scene?.resize());canvas.addEventListener('webglcontextlost',e=>{e.preventDefault();pause();dialog('The view needs a restart.','Your last checkpoint is safe. Reload to continue.','Reload adventure',()=>location.reload());});
try{
 scene=createScene(canvas);updateHUD();primary.disabled=false;primary.textContent=saved?'Continue the escape →':'Begin the escape →';primaryAction=play;
 if(saved){$('#menu-copy').textContent=`Continue from ${checkpoints[saved].title.toLowerCase()}. Your checkpoint is saved on this device.`;secondary.hidden=false;secondary.textContent='Start from Michal’s window';secondaryAction=resetAll;}
 menu.showModal();requestAnimationFrame(frame);
}catch(error){console.error('Adventure renderer unavailable',error);dialog('Try another adventure.','This device could not start the 3D scene. Little Light and the other games are still available.','Play Little Light',()=>location.assign('/kids-adventures/little-light/'),{note:'This game needs WebGL 2 graphics support.'});}
