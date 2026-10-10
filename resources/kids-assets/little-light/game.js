'use strict';
const canvas=document.querySelector('#canvas'),ctx=canvas.getContext('2d'),$=s=>document.querySelector(s);
const levels=[
{name:'The Young Shepherd',ref:'1 Samuel 16; 17:12–37',theme:'CARE & FAITHFULNESS',title:'Great courage<br>starts small.',desc:'Gather David’s sheep and protect the flock. Find sapphire prayers, pray to kindle your aura, and use its light to fend off wolves and lions. Every prayer gives five defenses.',hint:'Pray: Q / B / ○ / PRAY. Defend or gather sheep: E / X / □ / ACT. The aura and wolves are imaginative gameplay.',goal:'Gather 3 sheep, then reach home.',color:['#7dbec4','#dce8c4','#557e72'],facts:[['The youngest brother','David was Jesse’s youngest son. When Samuel came to his family, David was out caring for the sheep.','1 Samuel 16:10–11'],['More than appearances','God told Samuel that people look at outward appearance, but the Lord looks at the heart. Samuel anointed David.','1 Samuel 16:7, 12–13'],['A musician and a protector','David played the lyre for Saul. As a shepherd, he also protected his flock from a lion and a bear.','1 Samuel 16:23; 17:34–37']],end:'The flock is home! Before David faced a giant, he cared for sheep and played music. Small responsibilities were part of his story.'},
{name:'David & Goliath',ref:'1 Samuel 17',theme:'COURAGE & FAITH',title:'Courage comes<br>in every size.',desc:'Explore the valley as David. Collect five smooth stones and discover the shepherd behind the famous story.',hint:'Gather five stones, then use ACT, E, or X / □ near Goliath.',goal:'Gather 5 stones. Reach Goliath and press E.',color:['#dfbd83','#f7e4ab','#a18358'],facts:[['A shepherd first','David cared for his father’s sheep. He described protecting them from a lion and a bear.','1 Samuel 17:34–37'],['An unexpected errand','David’s father sent him to the army camp with food for his brothers and a gift for their commander.','1 Samuel 17:17–20'],['His own kind of courage','David took off Saul’s armor because he was not used to it. He chose five smooth stones and trusted God.','1 Samuel 17:38–47']],end:'The giant has fallen! David used his sling and a stone. His confidence came from trusting God, not from being the biggest.'},
{name:'The Cave of Mercy',ref:'1 Samuel 24',theme:'RESTRAINT & MERCY',title:'Being strong.<br>Choosing mercy.',desc:'Explore the wilderness of En Gedi. Discover what happened when David had a chance to hurt Saul—and chose to spare him.',hint:'Find three scrolls, then use ACT, E, or X / □ near Saul to choose mercy.',goal:'Discover 3 scrolls. Reach Saul and press E.',color:['#728fa3','#bcc8c0','#677981'],facts:[['Hiding in the wilderness','Saul searched for David near the rocky crags of En Gedi. David and his men were hiding deep inside a cave.','1 Samuel 24:1–3'],['A choice in the cave','David secretly cut off a corner of Saul’s robe, then felt troubled about it. He would not let his men attack Saul.','1 Samuel 24:4–7'],['Proof of mercy','David showed Saul the piece of his robe as proof that he had spared him. Saul acknowledged that David had treated him well.','1 Samuel 24:8–18']],end:'You chose mercy. David showed Saul the corner of his robe and explained that he had spared his life. Courage can mean holding back.'}
];
const input=new GameControls();
let saved={};try{const data=JSON.parse(localStorage.getItem('little-light-journal')||'{}');for(let i=0;i<3;i++){const v=data?.[i];if(v&&Array.isArray(v.facts))saved[i]={facts:[...new Set(v.facts.filter(n=>Number.isInteger(n)&&n>=0&&n<3))],complete:v.complete===true};}}catch{}
let li=0,state='intro',p,platforms,lights,scrolls,animals,stones,particles=[],camera=0,t=0,hearts=3,collected=0,companions=0,stoneCount=0,discovered=new Set(),invuln=0,checkpoint=80,sound=false,audioCtx,toastTimer,coyote=0,landSquash=0,overlayAction=null,ending=0;
// Tune encounters here without changing combat or prayer rules.
const SHEPHERD_ENCOUNTERS=[{x:520,kind:'wolf'},{x:960,kind:'wolf'},{x:1240,kind:'lion'},{x:1670,kind:'wolf'},{x:1990,kind:'wolf'},{x:2380,kind:'lion'},{x:2780,kind:'wolf'},{x:3040,kind:'wolf'}];
const DEFENSES_PER_PRAYER=5;
let prayerGems=[],predators=[],prayerTokens=0,prayerPower=0,prayersOffered=0,prayerGlow=0,prayerPulse=0,defensePulse=0,defenseCooldown=0,trail=[],trailDistance=0;
let WIDTH=1200;const HEIGHT=600,WORLD=3600;
const animalSprite=new Image();let animalSpriteReady=false;animalSprite.onload=()=>{animalSpriteReady=true};animalSprite.src='assets/animals-natural.png';
const goliathSprite=new Image();let goliathReady=false;goliathSprite.onload=()=>{goliathReady=true};goliathSprite.src='assets/goliath.png';
let boss,shot=null,slingWind=0,slingCooldown=0;
const davidSprite=new Image();let davidSpriteReady=false;davidSprite.onload=()=>{davidSpriteReady=true};davidSprite.src='assets/david-character.png';
const scenery=new Image();let sceneryReady=false;scenery.onload=()=>{sceneryReady=true};scenery.src='assets/david-worlds.png';
const reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const music=new Audio();music.loop=true;music.volume=.45;music.preload='metadata';let musicWanted=false,musicUrl=null;
function syncMusic(){if(musicWanted&&state==='playing'&&!document.hidden&&music.src){music.play().catch(()=>{musicWanted=false;$('#music-toggle').textContent='Play music';toast('Tap Music, then Play to enable your song.');});}else music.pause();}
function unlockAudio(){if(sound){try{audioCtx??=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==='suspended')audioCtx.resume().catch(()=>{});}catch{}}}
function persist(){try{localStorage.setItem('little-light-journal',JSON.stringify(saved))}catch{}}
function beep(freq=650){if(!sound)return;try{unlockAudio();const o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type='triangle';o.connect(g);g.connect(audioCtx.destination);o.frequency.setValueAtTime(freq,audioCtx.currentTime);o.frequency.exponentialRampToValueAtTime(freq*1.3,audioCtx.currentTime+.1);g.gain.setValueAtTime(.065,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(.001,audioCtx.currentTime+.2);o.start();o.stop(audioCtx.currentTime+.2)}catch{}}
function actionLabel(){return input.mode==='gamepad'?'X / □':input.mode==='touch'?'ACT':'E';}
function clearInput(){input.clear();document.querySelectorAll('[data-action]').forEach(b=>b.classList.remove('pressed'));}
function sizeCanvas(){const box=canvas.getBoundingClientRect();if(!box.width||!box.height)return;WIDTH=HEIGHT*box.width/box.height;const dpr=Math.min(2,window.devicePixelRatio||1);canvas.width=Math.round(box.width*dpr);canvas.height=Math.round(box.height*dpr);camera=Math.max(0,Math.min(WORLD-WIDTH,camera));positionScrollTip();}
function toast(s){$('#toast').textContent=s;$('#toast').style.opacity=1;clearTimeout(toastTimer);toastTimer=setTimeout(()=>$('#toast').style.opacity=0,3000)}
function setOverlay(over,title,desc,button,hint,action){$('#scroll-tip').hidden=true;clearInput();$('#description').style.whiteSpace='normal';$('#description').style.maxHeight='none';$('#description').style.overflowY='visible';$('#overline').textContent=over;$('#title').innerHTML=title;$('#description').textContent=desc;$('#start').innerHTML=button+' <span>→</span>';$('#hint').textContent=hint;$('#overlay').classList.remove('hidden');$('.game').classList.remove('is-playing');overlayAction=action;$('#start').onclick=()=>{unlockAudio();action()};$('#restart').hidden=state==='intro'||state==='complete';$('#journal').hidden=state==='journal';syncMusic();$('#start').focus({preventScroll:true});}
function resume(){$('#scroll-tip').hidden=true;state='playing';$('#overlay').classList.add('hidden');$('.game').classList.add('is-playing');clearInput();canvas.focus({preventScroll:true});syncMusic();}
function init(index){boss={x:3350,mode:'waiting',timer:0,ammo:5};shot=null;slingWind=0;slingCooldown=0;li=index;const l=levels[li];state='intro';p={x:80,y:390,vx:0,vy:0,w:34,h:57,ground:false,face:1};camera=0;prayerTokens=0;prayerPower=0;prayersOffered=0;prayerGlow=0;prayerPulse=0;defensePulse=0;defenseCooldown=0;hearts=3;collected=0;companions=0;stoneCount=0;discovered=new Set();particles=[];checkpoint=80;invuln=0;coyote=0;ending=0;landSquash=0;clearInput();
platforms=[{x:0,y:492,w:650},{x:760,y:492,w:590},{x:1480,y:492,w:620},{x:2220,y:492,w:1380},{x:370,y:395,w:155},{x:870,y:388,w:160},{x:1160,y:330,w:135},{x:1580,y:388,w:160},{x:1860,y:330,w:135},{x:2300,y:392,w:165},{x:2630,y:355,w:150}];
lights=[220,425,570,805,925,1070,1210,1515,1650,1810,1900,2050,2300,2370,2510,2690,2860,3020].map((x,i)=>({x,y:(platforms.find(q=>q.x<=x&&q.x+q.w>=x&&q.y<490)?.y||492)-50,taken:false}));
scrolls=[{x:435,y:355,id:0},{x:1650,y:348,id:1},{x:2700,y:315,id:2}];animals=[{x:590,y:464,kind:'sheep',found:false},{x:1130,y:464,kind:'sheep',found:false},{x:2020,y:464,kind:'sheep',found:false}];stones=[270,970,1540,2400,2890].map(x=>({x,y:468,taken:false}));
animals.forEach((a,i)=>Object.assign(a,{vx:0,phase:i*2.3,face:1,speed:0,restY:a.y}));resetTrail();
prayerGems=li===0?[{x:260,y:453,taken:false},{x:1515,y:453,taken:false}]:[];
predators=li===0?SHEPHERD_ENCOUNTERS.map((e,i)=>({...e,home:e.x,y:468,face:-1,phase:i*1.9,mode:'patrol',timer:0,vx:0,alpha:1})):[];
$('#prayer-hud').hidden=li!==0;document.querySelector('[data-action="pray"]').hidden=li!==0;
$('#chapter').textContent=`0${li+1} / ${l.name.toUpperCase()}`;$('#objective').textContent=l.goal;document.querySelectorAll('[data-level]').forEach((b,i)=>b.classList.toggle('selected',i===li));setOverlay(`CHAPTER ${li+1} · ${l.ref.toUpperCase()}`,l.title,l.desc,'Begin the adventure',l.hint,resume);hud();}
function hud(){$('#prayer-hud').textContent=`◆ ${prayerTokens} prayers · ${prayerPower} defenses`;$('#prayer-hud').setAttribute('aria-label',`${prayerTokens} prayer collectibles, ${prayerPower} defenses remaining`);$('#score').textContent=`✦ ${collected}  ·  ▤ ${discovered.size}/3`;$('#hearts').textContent='♥ '.repeat(hearts)+'♡ '.repeat(3-hearts);$('#progress').style.width=Math.min(100,p.x/(WORLD-250)*100)+'%';$('#objective').textContent=li===0?`Sheep friends ${companions}/3 · Find home`:li===1?`Smooth stones ${stoneCount}/5 · ${actionLabel()} to sling`: `Story scrolls ${discovered.size}/3 · Find Saul`;}
function positionScrollTip(){const card=$('#scroll-tip');if(card.hidden)return;const box=canvas.getBoundingClientRect(),w=Math.min(310,box.width-24);card.style.width=w+'px';card.style.left=Math.max(12,Math.min(box.width-w-12,(p.x+17-camera)/WIDTH*box.width-w*.5))+'px';card.style.top=Math.max(90,Math.min(box.height-240,(p.y-160)/HEIGHT*box.height))+'px';}
function discover(id){discovered.add(id);saved[li]??={facts:[],complete:false};if(!saved[li].facts.includes(id))saved[li].facts.push(id);persist();hud();beep(900);state='discovery';clearInput();syncMusic();$('.game').classList.remove('is-playing');const f=levels[li].facts[id];$('#scroll-title').textContent=f[0];$('#scroll-copy').textContent=f[1];$('#scroll-ref').textContent=f[2];$('#scroll-tip').hidden=false;overlayAction=resume;positionScrollTip();$('#scroll-continue').focus({preventScroll:true});}
function journal(){const entries=Object.entries(saved).flatMap(([i,v])=>(v.facts||[]).map(id=>`${levels[i].name} — ${levels[i].facts[id][0]}: ${levels[i].facts[id][1]} (${levels[i].facts[id][2]})`));state='journal';setOverlay('YOUR CHARACTER JOURNAL','Every hero<br>has a story.',entries.length?entries.join('\n\n'):'Find glowing scrolls on the high paths to unlock real discoveries about each Bible character.','Back to adventure','Discoveries are saved on this device.',resume);$('#description').style.whiteSpace='pre-line';$('#description').style.maxHeight='210px';$('#description').style.overflowY='auto';}
function complete(){saved[li]??={facts:[],complete:false};saved[li].complete=true;persist();state='complete';beep(1000);setOverlay('CHAPTER COMPLETE · '+levels[li].ref,li===2?'A heart full<br>of discoveries.':'A little courage.<br>A big adventure.',levels[li].end+(discovered.size<3?' There are still hidden story scrolls to find!':''),li<2?'Explore the next chapter':'Play the stories again',`Collected ${collected} lights · Discovered ${discovered.size}/3 character facts`,()=>init((li+1)%3));}
function hurt(){if(invuln>0)return;hearts--;beep(160);if(hearts<=0){state='retry';setOverlay('EVERY HERO TRIES AGAIN','Take a breath.<br>Try again.', 'Your journal discoveries are safe. A fresh start is part of every adventure.','Try this chapter again','Tip: hold a direction and tap jump to cross gaps.',()=>{init(li);resume()});}else{p.x=checkpoint;p.y=370;p.vy=0;p.vx=0;invuln=2;resetTrail();animals.filter(a=>a.found).forEach((a,i)=>{a.x=p.x-40-i*40;a.y=480;});toast('A little stumble. Keep going!')}hud();}
function interact(){if(state!=='playing')return;if(li===1&&p.x>2900){useSling();return;}if(li===0){if(defend())return;animals.forEach(a=>{if(!a.found&&Math.abs(p.x-a.x)<90&&Math.abs(p.y+p.h-a.y)<85){a.found=true;companions++;beep();toast(['One sheep safe! Look for the next one.','Two sheep together! One more to find.','The flock is together! Guide them home.'][companions-1]);hud()}})}if(p.x>3190&&p.y+p.h>370){if(li===0&&companions>=3)complete();else if(li===2&&discovered.size===3)complete();else toast(li===0?'Find all three sheep first.':li===1?'Find five smooth stones along the path.':'Discover all three scrolls on the higher paths.');}}
function burst(x,y,n=12){for(let i=0;i<n;i++)particles.push({x,y,vx:(Math.random()-.5)*180,vy:-Math.random()*200,life:1})}
function update(dt){
 if(state==='playing'||state==='intro'||state==='ending')t+=dt;particles.forEach(q=>{q.x+=q.vx*dt;q.y+=q.vy*dt;q.vy+=300*dt;q.life-=dt});particles=particles.filter(q=>q.life>0);
 if(state==='ending'){ending-=dt;if(ending<=0)complete();return;}if(state!=='playing')return;
 prayerPulse=Math.max(0,prayerPulse-dt);defensePulse=Math.max(0,defensePulse-dt);defenseCooldown=Math.max(0,defenseCooldown-dt);prayerGlow+=(Math.min(1,prayerPower/10)-prayerGlow)*Math.min(1,dt*3);
 invuln=Math.max(0,invuln-dt);landSquash=Math.max(0,landSquash-dt*4);coyote=p.ground?.11:Math.max(0,coyote-dt);
 const target=(prayerPulse>.9?0:input.move)*265;p.vx+=(target-p.vx)*Math.min(1,dt*(p.ground?20:12));if(Math.abs(p.vx)<.5)p.vx=0;if(Math.abs(p.vx)>3)p.face=Math.sign(p.vx);
 if(input.jumpBuffer>0&&coyote>0&&prayerPulse<=.9){p.vy=-660;p.ground=false;coyote=0;input.consumeJump();burst(p.x+17,p.y+p.h,5);beep(350);}
 input.tick(dt);const oldBottom=p.y+p.h,wasGround=p.ground;p.vy+=1450*dt;p.x=Math.max(0,Math.min(WORLD-60,p.x+p.vx*dt));p.y+=p.vy*dt;p.ground=false;
 for(const q of platforms){if(p.x+p.w>q.x&&p.x<q.x+q.w&&oldBottom<=q.y+.5&&p.y+p.h>=q.y&&p.vy>=0){p.y=q.y-p.h;if(!wasGround&&p.vy>100){landSquash=.25;burst(p.x+17,q.y,4)}p.vy=0;p.ground=true;}}
 if(p.y>700){hurt();return;}if(p.ground&&p.x>780&&p.x<1300)checkpoint=790;if(p.ground&&p.x>1510&&p.x<2060)checkpoint=1510;if(p.ground&&p.x>2240)checkpoint=2240;
 updateFlock(dt);
 if(li===1){updateGoliath(dt);if(state!=='playing')return;}
 for(const gem of prayerGems)if(!gem.taken&&Math.hypot(p.x+17-gem.x,p.y+30-gem.y)<38){gem.taken=true;prayerTokens++;burst(gem.x,gem.y,18);beep(920);hud();toast('Sapphire prayer found! Tap PRAY, Q, or B / ○ to kindle your aura.');}
 if(li===0){updatePredators(dt);if(state!=='playing')return;}
 for(const l of lights)if(!l.taken&&Math.hypot(p.x+17-l.x,p.y+27-l.y)<38){l.taken=true;collected++;burst(l.x,l.y);beep();hud()}
 if(li===1)for(const s of stones)if(!s.taken&&Math.hypot(p.x+17-s.x,p.y+40-s.y)<38){s.taken=true;stoneCount++;beep(480);toast(`Smooth stone ${stoneCount} of 5`);hud()}
 for(const s of scrolls)if(!discovered.has(s.id)&&Math.hypot(p.x+17-s.x,p.y+25-s.y)<43){discover(s.id);return}
 if(p.x>3260&&li===0&&companions===3)complete();camera+=(Math.max(0,Math.min(WORLD-WIDTH,p.x-WIDTH*.34))-camera)*Math.min(1,dt*6);$('#progress').style.width=Math.min(100,p.x/(WORLD-250)*100)+'%';
}
function shape(fill,points){ctx.fillStyle=fill;ctx.beginPath();points.forEach(([x,y],i)=>i?ctx.lineTo(x,y):ctx.moveTo(x,y));ctx.closePath();ctx.fill()}
function ellipse(x,y,rx,ry,c){ctx.fillStyle=c;ctx.beginPath();ctx.ellipse(x,y,rx,ry,0,0,Math.PI*2);ctx.fill()}
function rect(x,y,w,h,c,r=0){ctx.fillStyle=c;ctx.beginPath();ctx.roundRect(x,y,w,h,r);ctx.fill()}
function line(x,y,x2,y2,c,w=2){ctx.strokeStyle=c;ctx.lineWidth=w;ctx.beginPath();ctx.moveTo(x,y);ctx.lineTo(x2,y2);ctx.stroke()}
function star(x,y,r=9){ctx.save();ctx.translate(x,y);ctx.rotate(Math.PI/4);rect(-r/2,-r/2,r,r,'#ffe4a0',2);ctx.restore()}
function tree(x,y,s=1){ctx.save();ctx.translate(x,y);ctx.scale(s,s);shape('#6b6450',[[-7,0],[8,0],[3,-125],[-4,-125]]);line(0,-75,-27,-115,'#6b6450',6);ellipse(-24,-130,37,40,'#537c68');ellipse(17,-153,43,49,'#678f70');ellipse(44,-117,34,30,'#739777');ellipse(-10,-168,31,33,'#86a583');ctx.restore()}
function cloud(x,y,s=1){ellipse(x,y,68*s,16*s,'#ffffff35');ellipse(x-24*s,y-9*s,30*s,19*s,'#ffffff35');ellipse(x+19*s,y-15*s,37*s,23*s,'#ffffff35')}
function hero(x,y,scale=1){
 const moving=Math.abs(p.vx)>8&&p.ground&&state==='playing';let frame=0;
 if(slingWind>0)frame=7;else if(prayerPulse>.9)frame=6;else if(defensePulse>0)frame=7;else if(!p.ground&&state==='playing')frame=4;else if(landSquash>.08)frame=5;else if(moving)frame=[1,2,3,2][Math.floor(t*10)%4];
 ctx.save();ctx.translate(x+17,y+p.h);ctx.scale(scale*p.face,scale);ellipse(0,1,20,4,'#142d2855');
 if(davidSpriteReady){const w=davidSprite.naturalWidth/4,h=davidSprite.naturalHeight/2,bob=moving?Math.sin(t*20)*.7:Math.sin(t*2)*.4;ctx.drawImage(davidSprite,frame%4*w,Math.floor(frame/4)*h,w,h,-43,-83+bob,86,84);}
 else{rect(-12,-30,25,27,'#d3ae6b',5);ellipse(0,-49,15,19,'#77462d');ellipse(0,-65,15,8,'#231f1e');ellipse(7,-50,2,3,'#201e1a');rect(-12,-5,9,8,'#60432c',3);rect(3,-5,9,8,'#60432c',3);}
 ctx.restore();
}
function naturalAnimal(kind,x,feet,face,moving,phase,alpha=1){
 const col=kind==='sheep'?0:kind==='wolf'?1:2,w=kind==='sheep'?69:kind==='wolf'?99:116,h=w*.75;
 const gait=reducedMotion?0:t*(kind==='sheep'?7:9)+phase,row=moving?Math.floor(gait)%2:0;
 ctx.save();ctx.globalAlpha=alpha;ctx.translate(x,feet);ctx.scale(face,1);
 ellipse(0,1,w*.32,3.5,'#192e2840');
 if(animalSpriteReady){const sw=animalSprite.naturalWidth/3,sh=animalSprite.naturalHeight/2;
 const breath=reducedMotion?0:Math.sin(t*2+phase)*.003,bob=moving&&!reducedMotion?Math.sin(gait*Math.PI*2)*.45:0;
 ctx.scale(1,1+breath);ctx.drawImage(animalSprite,col*sw,row*sh,sw,sh,-w/2,-h*.93+bob,w,h);
 }
 ctx.restore();
}
function animal(a){
 naturalAnimal('sheep',a.x,a.y+16,a.face,a.speed>8,a.phase);
 if(!a.found&&Math.abs(p.x-a.x)<100){rect(a.x-26,a.y-63,55,25,'#f9f0d6',8);ctx.fillStyle='#45634f';ctx.font='12px sans-serif';ctx.fillText(actionLabel()+' ♥',a.x-19,a.y-46)}
}
function useSling(){
 if(stoneCount<5){toast('Find all five smooth stones before facing Goliath.');return;}
 if(slingCooldown>0||shot||slingWind>0)return;
 if(boss.mode==='waiting'){toast('Approach carefully. Watch his warning, then keep your distance.');return;}
 if(boss.ammo<=0){boss.ammo=5;boss.x=3350;boss.mode='warn';boss.timer=1.8;toast('Try the encounter again. Five stones ready.');return;}
 slingWind=.55;slingCooldown=1;beep(330);
}
function updateGoliath(dt){
 slingCooldown=Math.max(0,slingCooldown-dt);
 if(p.x<2900){boss.x=3350;boss.mode='waiting';shot=null;slingWind=0;boss.ammo=5;return;}
 if(boss.mode==='waiting'){boss.mode='warn';boss.timer=1.8;toast('Goliath approaches! Step back when he raises his spear. Sling when he pauses.');}
 if(slingWind>0){slingWind=Math.max(0,slingWind-dt);if(slingWind===0){boss.ammo--;shot={x:p.x+17,y:p.y+12,tx:boss.x-15,ty:263,flight:0,hit:boss.mode==='open'&&Math.abs(p.x+17-boss.x)>100&&Math.abs(p.x+17-boss.x)<460};beep(650);}}
 if(shot){shot.flight+=dt;const u=Math.min(1,shot.flight/.38);if(u===1){if(shot.hit){burst(boss.x-15,263,18);state='ending';ending=1.2;shot=null;return;}shot=null;toast(boss.ammo?'Wait for his pause and give your sling room.':actionLabel()+' · Try again with five stones');}}
 boss.timer-=dt;
 if(boss.mode==='warn'&&boss.timer<=0){boss.mode='advance';boss.timer=.85;}
 else if(boss.mode==='advance'){
  boss.x+=Math.sign(p.x+17-boss.x)*105*dt;boss.x=Math.max(3110,Math.min(3410,boss.x));
  if(Math.abs(p.x+17-boss.x)<100&&p.y+p.h>410&&invuln<=0){hurt();boss.mode='waiting';boss.x=3350;shot=null;slingWind=0;return;}
  if(boss.timer<=0){boss.mode='open';boss.timer=2.4;toast('Now! '+actionLabel()+' · Sling toward his forehead');}
 }else if(boss.mode==='open'&&boss.timer<=0){boss.mode='warn';boss.timer=1.4;}
}
function drawGoliath(){
 const x=boss.x,feet=492;ellipse(x,feet,49,7,'#35281c44');
 ctx.save();ctx.translate(x,feet);
 if(state==='ending'){const u=Math.min(1,(1.2-ending)/1.05);ctx.rotate(-u*Math.PI*.48);}
 else if(!reducedMotion){ctx.rotate(boss.mode==='advance'?Math.sin(t*8)*.012:Math.sin(t*1.7)*.004);}
 if(goliathReady)ctx.drawImage(goliathSprite,-98,-268,179,281);
 ctx.restore();
 if(state==='playing'&&p.x>2900){
  const open=boss.mode==='open';rect(x-89,198,178,29,open?'#244d3ee6':'#623d2ee6',9);ctx.textAlign='center';ctx.font='bold 13px sans-serif';ctx.fillStyle='#fff3d4';ctx.fillText(open?'SLING NOW · '+actionLabel():boss.mode==='advance'?'KEEP YOUR DISTANCE':'WATCH HIS SPEAR',x,217);ctx.textAlign='left';
  if(open){ctx.strokeStyle='#ffe5a0';ctx.lineWidth=2;ctx.beginPath();ctx.arc(x-15,263,9,0,Math.PI*2);ctx.stroke();}
  ctx.fillStyle='#fff3d4';ctx.font='12px sans-serif';ctx.fillText('Sling stones: '+boss.ammo,x-45,184);
 }
 if(slingWind>0){const hx=p.x+17,hy=p.y+8,angle=t*30;line(hx,hy,hx+Math.cos(angle)*27,hy+Math.sin(angle)*15,'#8d6944',2);ellipse(hx+Math.cos(angle)*27,hy+Math.sin(angle)*15,4,3,'#eee2c3');}
 if(shot){const u=Math.min(1,shot.flight/.38);ellipse(shot.x+(shot.tx-shot.x)*u,shot.y+(shot.ty-shot.y)*u-Math.sin(u*Math.PI)*20,4,3,'#fff0cb');}
}
function destination(){const x=3240;if(li===0){rect(x+40,353,245,142,'#c9b58c',7);shape('#806d51',[[x+20,359],[x+161,276],[x+305,359]]);rect(x+133,411,53,83,'#6b654a',25);rect(x+68,389,36,35,'#eee0af',4);for(let i=0;i<7;i++)line(x+10+i*43,455,x+10+i*43,508,'#92774e',7);line(x,469,x+300,469,'#a18b5f',7);}if(li===1)drawGoliath();if(li===2){ellipse(x+140,386,190,150,'#6c7775');ellipse(x+105,414,100,105,'#364d50');ellipse(x+150,481,34,8,'#253d3e');rect(x+131,418,40,69,'#b48c62',12);ellipse(x+151,401,18,22,'#dcb78d');shape('#cdb77a',[[x+133,384],[x+130,371],[x+144,377],[x+151,365],[x+159,377],[x+171,371],[x+169,387]]);rect(x+131,443,40,8,'#e5c780');} if(p.x>3120){ctx.font='bold 13px sans-serif';ctx.fillStyle='#fff9dd';ctx.fillText(li===1?actionLabel()+' · Sling when he pauses':li===2?actionLabel()+' · Choose mercy':'Home →',x-55,230)}}
function draw(){
 ctx.setTransform(canvas.width/WIDTH,0,0,canvas.height/HEIGHT,0,0);ctx.clearRect(0,0,WIDTH,HEIGHT);const l=levels[li];
 const gr=ctx.createLinearGradient(0,0,0,600);gr.addColorStop(0,l.color[0]);gr.addColorStop(1,l.color[1]);ctx.fillStyle=gr;ctx.fillRect(0,0,WIDTH,HEIGHT);
 if(sceneryReady){const bh=scenery.naturalHeight/3,bw=scenery.naturalWidth,sw=Math.min(bw,bh*WIDTH/HEIGHT),sx=(bw-sw)*(.12+.76*camera/Math.max(1,WORLD-WIDTH));drawLivingBackdrop(sx,sw,bh);}
 else{for(let k=0;k<2;k++){ctx.beginPath();ctx.moveTo(0,600);for(let x=0;x<=WIDTH+20;x+=20)ctx.lineTo(x,290+k*80+Math.sin((x+camera*(.13+k*.12))/220)*50);ctx.lineTo(WIDTH,600);ctx.fillStyle=k?'#527e7155':'#729d8455';ctx.fill();}}
 drawDistantLife();
 const mist=ctx.createLinearGradient(0,250,0,560);mist.addColorStop(0,'#bedbc000');mist.addColorStop(1,li===2?'#678fa34d':'#ffecc033');ctx.fillStyle=mist;ctx.fillRect(0,250,WIDTH,350);
 if(!reducedMotion){for(let i=0;i<17;i++){const x=((i*131+t*(5+i%3)-camera*.35)%(WIDTH+60)+WIDTH+60)%(WIDTH+60)-30;const y=110+(i*61)%320+Math.sin(t*.6+i)*16;ellipse(x,y,li===2?2:1.3,li===2?2:1.3,li===2?'#eafab899':'#ffefc977');}}
 ctx.save();ctx.translate(-camera,0);destination();for(const q of platforms){if(q.x>camera+WIDTH+50||q.x+q.w<camera-50)continue;drawPlatform(q);}
for(const a of lights)if(!a.taken){ellipse(a.x,a.y,16,16,'#ffedb218');star(a.x,a.y+Math.sin(t*3+a.x)*4,11)}for(const s of scrolls)if(!discovered.has(s.id)){const y=s.y+Math.sin(t*2)*4;ellipse(s.x,y,28,28,'#fff4bd25');rect(s.x-12,y-16,24,32,'#f6dfad',3);rect(s.x-15,y-17,29,5,'#b78b4f',3);for(let i=0;i<3;i++)line(s.x-6,y-7+i*6,s.x+6,y-7+i*6,'#b58e59',2);star(s.x+16,y-19,7)}if(li===0){prayerGems.forEach(drawPrayerGem);predators.forEach(drawPredator);animals.forEach(animal);drawPrayerAura();}if(li===1)for(const s of stones)if(!s.taken){ellipse(s.x,s.y,10,7,'#d8e2d1');ellipse(s.x-2,s.y-3,5,2,'#f8f1d2')}
if(invuln<=0||Math.floor(t*10)%2)hero(p.x,p.y);for(const a of particles){ctx.globalAlpha=a.life;star(a.x,a.y,6)}ctx.globalAlpha=1;
ctx.restore();const water=ctx.createLinearGradient(0,540,0,600);water.addColorStop(0,'#73b6b650');water.addColorStop(1,'#3f869aaa');ctx.fillStyle=water;ctx.fillRect(0,551,WIDTH,49);for(let i=0;i<Math.ceil(WIDTH/40);i++){const x=(i*67+t*15-camera*.5)%(WIDTH+60);line(x,564+(i%4)*11,x+24,564+(i%4)*11,'#d0ece34a',2)}}
function drawPlatform(q){
 const elevated=q.y<490,x=q.x,y=q.y,w=q.w;
 const rock=ctx.createLinearGradient(0,y,0,y+120);rock.addColorStop(0,li===2?'#596777':li===1?'#af7a49':'#777252');rock.addColorStop(1,li===2?'#283e51':li===1?'#614932':'#3d4938');
 if(elevated){shape(rock,[[x,y+4],[x+w,y+4],[x+w-8,y+28],[x+w*.7,y+46],[x+w*.25,y+36],[x+8,y+21]]);line(x+6,y+16,x+w-7,y+16,'#c7af7e55',2);for(let i=0;i<3;i++){const xx=x+20+i*(w-40)/3;line(xx,y+27,xx+5,y+55+i*11,li===2?'#486d66':'#697b40',2);ellipse(xx+4,y+44+i*8,6,2,li===2?'#69907d':'#819351');}}
 else{rect(x,y,w,150,rock,5);for(let xx=Math.max(x+6,Math.floor(camera/48)*48);xx<x+w&&xx<camera+WIDTH+48;xx+=48){const n=Math.sin(xx*.237)*.5+.5;shape(li===2?'#53617166':'#d3b27b28',[[xx,y+25+n*17],[xx+29,y+21],[xx+40,y+46],[xx+19,y+65]]);line(xx+8,y+70,xx+23,y+96,'#202f302b',2);}}
 rect(x-2,y-1,w+4,12,li===2?'#869178':li===1?'#c4ac65':'#9aae62',5);line(x+4,y+2,x+w-4,y+2,li===2?'#bfccaa':'#e3d999',2);
 for(let xx=Math.max(x+5,Math.floor(camera/15)*15);xx<x+w-4&&xx<camera+WIDTH+20;xx+=15){const n=Math.sin(xx*.17);line(xx,y+5,xx-3+n*2,y-5-Math.abs(n)*8,li===2?'#8faa8d':li===1?'#ded089':'#bdca80',1.5);if(Math.floor(xx)%7===0){line(xx,y,xx+1,y-15,'#74894c',1.5);ellipse(xx+1,y-16,3,2,li===2?'#b5b6e1':'#f4d99a');}}
}
function pause(){if(state==='discovery'){resume();return;}if(state==='playing'){state='paused';setOverlay('TAKE A LITTLE BREAK','Your adventure<br>can wait.',levels[li].hint,'Keep playing','Controller: A / × continues · shoulders switch chapters',resume)}else if(state==='paused')resume()}
function pollController(){
 let pads=[];try{pads=navigator.getGamepads?navigator.getGamepads():[];}catch{}
 const actions=input.poll(pads);const status=$('#controller-status');
 const message=actions.connected?'Controller ready · A / × jump · X / □ act · B / ○ pray · Start pause':actions.unsupported?'This controller needs standard mapping. Touch and keyboard still work.':'Controller: pair with your device, then press a button';if(status.textContent!==message)status.textContent=message;
 if(actions.disconnected&&state==='playing'){pause();toast('Controller disconnected. Reconnect or use touch controls.');}
 if(document.hidden||$('#music-panel').open)return;
 if(actions.pause){pause();return;}
 if(state==='playing'){if(actions.interact)interact();if(actions.back)pray();if(actions.journal)journal();}
 else if(state!=='ending'){if(actions.jump&&overlayAction)overlayAction();else if(actions.back&&(state==='paused'||state==='discovery'))resume();else if(actions.journal&&state!=='journal')journal();else if(actions.prev)init((li+2)%3);else if(actions.next)init((li+1)%3);}
}
const gameKeys=['ArrowLeft','ArrowRight','ArrowUp','KeyA','KeyD','KeyW','Space','KeyE','KeyP','Escape','KeyJ','KeyQ'];
window.addEventListener('keydown',e=>{
 if($('#music-panel').open||/INPUT|TEXTAREA|SELECT/.test(e.target.tagName))return;
 if(!gameKeys.includes(e.code))return;if(state==='discovery'&&['Space','KeyE','Escape'].includes(e.code)){e.preventDefault();resume();return;}
 if(state==='playing'||e.code==='Escape'||e.code==='KeyP')e.preventDefault();if(e.repeat)return;
 input.press(e.code);unlockAudio();
 if(e.code==='KeyE')interact();if(e.code==='KeyQ')pray();if(e.code==='KeyP'||e.code==='Escape')pause();if(e.code==='KeyJ')journal();
});window.addEventListener('keyup',e=>input.release(e.code));
function suspend(){clearInput();if(state==='playing')pause();music.pause();if(audioCtx?.state==='running')audioCtx.suspend().catch(()=>{});}
window.addEventListener('blur',suspend);document.addEventListener('visibilitychange',()=>{if(document.hidden)suspend()});window.addEventListener('pagehide',suspend);
$('#scroll-continue').onclick=resume;$('#pause').onclick=pause;$('#sound').onclick=()=>{sound=!sound;$('#sound').textContent=sound?'Effects on':'Effects off';$('#sound').setAttribute('aria-pressed',String(sound));unlockAudio();beep()};
$('#journal').onclick=$('#journal-outside').onclick=journal;
$('#restart').onclick=()=>{init(li);resume()};
$('#expand').onclick=async()=>{const focused=document.body.classList.toggle('game-focus');$('#expand').textContent=focused?'✕':'⛶';$('#expand').setAttribute('aria-label',focused?'Exit expanded game':'Expand game');requestAnimationFrame(sizeCanvas);};
for(const b of document.querySelectorAll('[data-level]'))b.onclick=()=>init(+b.dataset.level);
for(const b of document.querySelectorAll('[data-action]')){
 b.addEventListener('pointerdown',e=>{if(state!=='playing')return;e.preventDefault();unlockAudio();b.setPointerCapture(e.pointerId);input.touch(e.pointerId,b.dataset.action);b.classList.add('pressed');if(b.dataset.action==='interact')interact();if(b.dataset.action==='pray')pray();});
 const release=e=>{input.releaseTouch(e.pointerId);if(!input.has(b.dataset.action))b.classList.remove('pressed');};
 b.addEventListener('pointerup',release);b.addEventListener('pointercancel',release);b.addEventListener('lostpointercapture',release);
 b.addEventListener('contextmenu',e=>e.preventDefault());
}
$('#music-open').onclick=()=>{if(state==='playing')pause();$('#music-panel').showModal();};
$('#music-panel').addEventListener('close',()=>syncMusic());
$('#song-file').onchange=async e=>{
 const file=e.target.files[0];if(!file)return;if(file.size>80*1024*1024){$('#song-name').textContent='Choose an audio file smaller than 80 MB.';return;}
 if(!file.type.startsWith('audio/')&&!/\.(mp3|m4a|wav|ogg|aac|flac)$/i.test(file.name)){$('#song-name').textContent='Choose an MP3, M4A, WAV, or another audio file.';return;}
 music.pause();if(musicUrl)URL.revokeObjectURL(musicUrl);musicUrl=URL.createObjectURL(file);music.src=musicUrl;music.load();musicWanted=true;$('#song-name').textContent=file.name;$('#music-toggle').disabled=false;$('#music-toggle').textContent='Pause music';
 try{await music.play()}catch{musicWanted=false;$('#music-toggle').textContent='Play music';$('#song-name').textContent=file.name+' — tap Play, or try MP3 if this format is unsupported.';}
};
music.addEventListener('error',()=>{musicWanted=false;$('#music-toggle').textContent='Play music';$('#song-name').textContent='This audio could not be played. Try an MP3 or WAV file.';});
$('#music-toggle').onclick=async()=>{musicWanted=!musicWanted;if(musicWanted){try{await music.play()}catch{musicWanted=false;$('#song-name').textContent='Unable to play this file. Try MP3 or WAV.';}}else music.pause();$('#music-toggle').textContent=musicWanted?'Pause music':'Play music';};
$('#volume').oninput=e=>music.volume=Number(e.target.value);
window.addEventListener('resize',()=>{sizeCanvas();clearInput()});if(window.visualViewport)window.visualViewport.addEventListener('resize',sizeCanvas);if(window.ResizeObserver)new ResizeObserver(sizeCanvas).observe(canvas);
let last=performance.now(),accumulator=0;const STEP=1/120;
function frame(now){const elapsed=Math.min(.1,Math.max(0,(now-last)/1000));last=now;pollController();accumulator+=elapsed;while(accumulator>=STEP){update(STEP);accumulator-=STEP;}draw();requestAnimationFrame(frame)}
function resetTrail(){trailDistance=0;trail=[{x:p.x+17,y:492,d:0}];}
function updateFlock(dt){
 if(li!==0)return;
 // Lambs wait below while David explores raised platforms or jumps.
 const onLowerGround=p.ground&&Math.abs(p.y+p.h-492)<1;
 if(!onLowerGround){animals.forEach(a=>{a.speed=0});return;}
 const last=trail[trail.length-1],x=p.x+17,y=492,step=Math.abs(x-last.x);
 if(step>1){trailDistance+=step;trail.push({x,y,d:trailDistance});while(trail.length>2&&trail[1].d<trailDistance-700)trail.shift();}
 let order=0;
 for(const a of animals){if(!a.found){a.speed=0;continue;}const distance=Math.max(0,trailDistance-(48+order*43));order++;let left=trail[0],right=left;
 for(let j=1;j<trail.length;j++){right=trail[j];if(right.d>=distance)break;left=right;}
 const mix=right.d===left.d?0:Math.max(0,Math.min(1,(distance-left.d)/(right.d-left.d)));const tx=left.x+(right.x-left.x)*mix,ty=left.y+(right.y-left.y)*mix-16;
 const old=a.x,follow=1-Math.exp(-dt*(6.2+order*.8));a.x+=(tx-a.x)*follow;a.y=476;
 // A short hop carries the flock across ground gaps, never onto coin platforms.
 const ground=platforms.filter(q=>q.y===492).sort((a,b)=>a.x-b.x);
 for(let j=1;j<ground.length;j++){const edge=ground[j-1].x+ground[j-1].w,end=ground[j].x;if(a.x>edge&&a.x<end)a.y-=Math.sin((a.x-edge)/(end-edge)*Math.PI)*30;}
 a.speed=Math.abs(a.x-old)/dt;if(a.speed>5)a.face=Math.sign(tx-a.x)||a.face;
 }
}
function pray(){
 if(state!=='playing'||li!==0)return;
 if(prayerTokens<1){toast('Find a glowing sapphire prayer first.');return;}
 prayerTokens--;prayerPower+=DEFENSES_PER_PRAYER;prayersOffered++;prayerPulse=1.5;invuln=Math.max(invuln,.9);burst(p.x+17,p.y+25,22);beep(1040);hud();toast(`David prays. His light grows — ${prayerPower} defenses ready.`);
}
function nearestPredator(){return predators.filter(e=>e.mode!=='flee'&&e.mode!=='gone'&&Math.hypot(e.x-p.x-17,e.y-p.y-30)<135).sort((a,b)=>Math.abs(a.x-p.x)-Math.abs(b.x-p.x))[0];}
function defend(){
 const enemy=nearestPredator();if(!enemy)return false;
 if(prayerPower<=0){toast(prayerTokens?'Pray first: Q / B / ○ / PRAY.':'No aura yet. Find a sapphire prayer, or jump past the predator.');return true;}
 if(defenseCooldown>0)return true;
 prayerPower--;enemy.mode='flee';enemy.face=Math.sign(enemy.x-p.x-17)||p.face;enemy.timer=1.2;defensePulse=.55;defenseCooldown=.3;invuln=Math.max(invuln,.45);burst(enemy.x,enemy.y,18);beep(760);hud();toast(`The ${enemy.kind} retreats! ${prayerPower} defenses left.`);return true;
}
function updatePredators(dt){
 for(const e of predators){
  if(e.mode==='gone')continue;
  if(e.mode==='flee'){e.x+=e.face*280*dt;e.timer-=dt;e.alpha=Math.max(0,e.timer/1.2);if(e.timer<=0)e.mode='gone';continue;}
  // Each predator stays on its own solid ground. A visible wind-up precedes a lunge.
  const floor=platforms.find(q=>q.y===492&&e.home>=q.x&&e.home<q.x+q.w),dx=p.x+17-e.x,close=Math.abs(dx)<175&&p.y+p.h>390;
  if(!floor)continue;
  if(e.mode==='patrol'){e.vx=Math.sin(t*.65+e.phase)*22;if(close){e.mode='warn';e.timer=.85;e.face=Math.sign(dx)||1;}}
  else if(e.mode==='warn'){e.vx=0;e.timer-=dt;if(e.timer<=0){e.mode='lunge';e.timer=.5;e.face=Math.sign(dx)||e.face;}}
  else if(e.mode==='lunge'){e.vx=e.face*(e.kind==='lion'?160:190);e.timer-=dt;if(e.timer<=0){e.mode='rest';e.timer=1.4;}}
  else{e.vx=0;e.timer-=dt;if(e.timer<=0)e.mode='patrol';}
  e.x=Math.max(floor.x+28,Math.min(floor.x+floor.w-28,e.x+e.vx*dt));if(Math.abs(e.vx)>1)e.face=Math.sign(e.vx);
  if(e.mode==='lunge'&&Math.abs(dx)<36&&Math.abs(p.y+p.h-492)<52&&invuln<=0){hurt();return;}
 }
}
function drawPrayerGem(gem){
 if(gem.taken)return;const y=gem.y+(reducedMotion?0:Math.sin(t*2.4+gem.x)*4),pulse=reducedMotion?1:.85+Math.sin(t*2)*.15;
 const glow=ctx.createRadialGradient(gem.x,y,2,gem.x,y,32);glow.addColorStop(0,'#d3faffaa');glow.addColorStop(.45,'#499cff66');glow.addColorStop(1,'#267dff00');ctx.fillStyle=glow;ctx.fillRect(gem.x-32,y-32,64,64);
 ctx.save();ctx.translate(gem.x,y);ctx.scale(pulse,1);ellipse(0,0,12,16,'#0e4ea1');ellipse(0,0,10,14,'#5bc6ff');shape('#bcecff',[[0,-12],[7,-5],[4,8],[0,12],[-6,5],[-7,-5]]);shape('#297bd0',[[0,-12],[0,12],[4,8],[7,-5]]);line(-4,-7,0,-10,'#f5ffff',2);ctx.restore();
}
function drawPrayerAura(){
 if(prayerGlow<.005&&prayerPulse<=0&&defensePulse<=0)return;
 const x=p.x+17,feet=p.y+p.h,power=Math.min(1,prayerGlow+prayerPulse*.35),clock=reducedMotion?0:t;
 ctx.save();ctx.globalCompositeOperation='screen';
 // Feathered light columns disappear into the air; no enclosing ring or shell.
 for(let i=0;i<3;i++){
  ctx.save();ctx.translate(x+(i-1)*15,feet-27-i*9);ctx.scale(.42,1);
  const r=48+power*24,g=ctx.createRadialGradient(0,8,0,0,0,r);
  g.addColorStop(0,`rgba(136,218,255,${power*.22})`);g.addColorStop(.4,`rgba(45,132,255,${power*.11})`);g.addColorStop(1,'rgba(35,115,255,0)');
  ctx.fillStyle=g;ctx.fillRect(-r,-r,r*2,r*2);ctx.restore();
 }
 if(!reducedMotion)for(let i=0;i<12;i++){
  const u=(clock*(.22+i*.006)+i*.083)%1,fade=Math.sin(u*Math.PI)*power,px=x+Math.sin(i*2.4+clock*.6)*(15+u*14),py=feet-u*105;
  ellipse(px,py,.7+(i%3)*.3,1.2,`rgba(175,233,255,${fade*.8})`);
 }
 if(defensePulse>0){const fade=defensePulse/.55;for(let i=0;i<7;i++){const dx=(1-fade)*100+i*8;ellipse(x+p.face*dx,feet-30+Math.sin(i*2)*19,2+fade*2,.8,`rgba(184,239,255,${fade*.7})`);}}
 ctx.restore();
}
function drawPredator(e){
 if(e.mode==='gone'||e.x<camera-100||e.x>camera+WIDTH+100)return;
 naturalAnimal(e.kind,e.x,e.y+24,e.face,Math.abs(e.vx)>3||e.mode==='flee',e.phase,e.alpha);
 if(e.mode==='warn'){ctx.fillStyle='#ffdd93';ctx.font='bold 23px sans-serif';ctx.fillText('!',e.x-4,e.y-64);}
}
function drawLivingBackdrop(sx,sw,bh){
 ctx.drawImage(scenery,sx,li*bh+.5,sw,bh-1,0,0,WIDTH,HEIGHT);
 if(reducedMotion)return;
 // Only the leafy upper edge moves; distant buildings and cliffs remain anchored.
 for(let row=0;row<130;row+=10){const strength=(1-row/130)*1.4,wind=Math.sin(t*.65+row*.014)*strength;
 ctx.drawImage(scenery,sx,li*bh+.5+row/HEIGHT*(bh-1),sw,10/HEIGHT*(bh-1),wind,row,WIDTH,10.4);}
}
function drawDistantLife(){
 const windTime=reducedMotion?0:t;
 // Two layers of little olive trees move independently with wind and camera depth.
 for(let layer=0;layer<2;layer++)for(let i=0;i<8;i++){
  const spacing=220+layer*70,x=i*spacing-camera*(.12+layer*.13),y=360+layer*60+Math.sin(i*1.4)*18;
  if(x< -50||x>WIDTH+50)continue;const scale=.28+layer*.12,sway=Math.sin(windTime*.7+i*1.8)*.035;
  ctx.save();ctx.globalAlpha=layer?.22:.15;ctx.translate(x,y);ctx.rotate(sway);tree(0,0,scale);ctx.restore();
 }
 if(reducedMotion)return;
 for(let i=0;i<3;i++){const x=((i*430+t*(7+i*3)-camera*.09)%(WIDTH+300)+WIDTH+300)%(WIDTH+300)-150;const fog=ctx.createRadialGradient(x,345+i*49,1,x,345+i*49,180);fog.addColorStop(0,li===2?'#b5e4ef0d':'#fff5d410');fog.addColorStop(1,'#ffffff00');ctx.fillStyle=fog;ctx.fillRect(x-180,165+i*49,360,360);}
 for(let i=0;i<3;i++){const x=((i*360+t*18-camera*.07)%(WIDTH+100)+WIDTH+100)%(WIDTH+100)-50,y=135+i*18+Math.sin(t*.35+i)*10,wing=Math.sin(t*4+i)*3;line(x-5,y+wing,x,y,'#243e4944',1.3);line(x,y,x+5,y+wing,'#243e4944',1.3);}
}

init(0);sizeCanvas();requestAnimationFrame(frame);
