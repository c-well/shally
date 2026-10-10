'use strict';
const canvas=document.querySelector('#canvas'),ctx=canvas.getContext('2d'),$=s=>document.querySelector(s);
const levels=[
{name:'The Young Shepherd',ref:'1 Samuel 16; 17:12–37',theme:'CARE & FAITHFULNESS',title:'Great courage<br>starts small.',desc:'Explore the hills of Bethlehem as young David. Gather three sheep and find hidden scrolls about the shepherd who would become king.',hint:'Move close to each sheep. Tap ACT, press E, or use X / □ to guide it home.',goal:'Gather 3 sheep, then reach home.',color:['#7dbec4','#dce8c4','#557e72'],facts:[['The youngest brother','David was Jesse’s youngest son. When Samuel came to his family, David was out caring for the sheep.','1 Samuel 16:10–11'],['More than appearances','God told Samuel that people look at outward appearance, but the Lord looks at the heart. Samuel anointed David.','1 Samuel 16:7, 12–13'],['A musician and a protector','David played the lyre for Saul. As a shepherd, he also protected his flock from a lion and a bear.','1 Samuel 16:23; 17:34–37']],end:'The flock is home! Before David faced a giant, he cared for sheep and played music. Small responsibilities were part of his story.'},
{name:'David & Goliath',ref:'1 Samuel 17',theme:'COURAGE & FAITH',title:'Courage comes<br>in every size.',desc:'Explore the valley as David. Collect five smooth stones and discover the shepherd behind the famous story.',hint:'Gather five stones, then use ACT, E, or X / □ near Goliath.',goal:'Gather 5 stones. Reach Goliath and press E.',color:['#dfbd83','#f7e4ab','#a18358'],facts:[['A shepherd first','David cared for his father’s sheep. He described protecting them from a lion and a bear.','1 Samuel 17:34–37'],['An unexpected errand','David’s father sent him to the army camp with food for his brothers and a gift for their commander.','1 Samuel 17:17–20'],['His own kind of courage','David took off Saul’s armor because he was not used to it. He chose five smooth stones and trusted God.','1 Samuel 17:38–47']],end:'The giant has fallen! David used his sling and a stone. His confidence came from trusting God, not from being the biggest.'},
{name:'The Cave of Mercy',ref:'1 Samuel 24',theme:'RESTRAINT & MERCY',title:'Being strong.<br>Choosing mercy.',desc:'Explore the wilderness of En Gedi. Discover what happened when David had a chance to hurt Saul—and chose to spare him.',hint:'Find three scrolls, then use ACT, E, or X / □ near Saul to choose mercy.',goal:'Discover 3 scrolls. Reach Saul and press E.',color:['#728fa3','#bcc8c0','#677981'],facts:[['Hiding in the wilderness','Saul searched for David near the rocky crags of En Gedi. David and his men were hiding deep inside a cave.','1 Samuel 24:1–3'],['A choice in the cave','David secretly cut off a corner of Saul’s robe, then felt troubled about it. He would not let his men attack Saul.','1 Samuel 24:4–7'],['Proof of mercy','David showed Saul the piece of his robe as proof that he had spared him. Saul acknowledged that David had treated him well.','1 Samuel 24:8–18']],end:'You chose mercy. David showed Saul the corner of his robe and explained that he had spared his life. Courage can mean holding back.'}
];
const input=new GameControls();
let saved={};try{const data=JSON.parse(localStorage.getItem('little-light-journal')||'{}');for(let i=0;i<3;i++){const v=data?.[i];if(v&&Array.isArray(v.facts))saved[i]={facts:[...new Set(v.facts.filter(n=>Number.isInteger(n)&&n>=0&&n<3))],complete:v.complete===true};}}catch{}
let li=0,state='intro',p,platforms,lights,scrolls,animals,stones,particles=[],camera=0,t=0,hearts=3,collected=0,companions=0,stoneCount=0,discovered=new Set(),invuln=0,checkpoint=80,sound=false,audioCtx,toastTimer,coyote=0,landSquash=0,overlayAction=null,ending=0;
let WIDTH=1200;const HEIGHT=600,WORLD=3600;
const scenery=new Image();let sceneryReady=false;scenery.onload=()=>{sceneryReady=true};scenery.src='assets/david-worlds.png';
const reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const music=new Audio();music.loop=true;music.volume=.45;music.preload='metadata';let musicWanted=false,musicUrl=null;
function syncMusic(){if(musicWanted&&state==='playing'&&!document.hidden&&music.src){music.play().catch(()=>{musicWanted=false;$('#music-toggle').textContent='Play music';toast('Tap Music, then Play to enable your song.');});}else music.pause();}
function unlockAudio(){if(sound){try{audioCtx??=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==='suspended')audioCtx.resume().catch(()=>{});}catch{}}}
function persist(){try{localStorage.setItem('little-light-journal',JSON.stringify(saved))}catch{}}
function beep(freq=650){if(!sound)return;try{unlockAudio();const o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type='triangle';o.connect(g);g.connect(audioCtx.destination);o.frequency.setValueAtTime(freq,audioCtx.currentTime);o.frequency.exponentialRampToValueAtTime(freq*1.3,audioCtx.currentTime+.1);g.gain.setValueAtTime(.065,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(.001,audioCtx.currentTime+.2);o.start();o.stop(audioCtx.currentTime+.2)}catch{}}
function actionLabel(){return input.mode==='gamepad'?'X / □':input.mode==='touch'?'ACT':'E';}
function clearInput(){input.clear();document.querySelectorAll('[data-action]').forEach(b=>b.classList.remove('pressed'));}
function sizeCanvas(){const box=canvas.getBoundingClientRect();if(!box.width||!box.height)return;WIDTH=HEIGHT*box.width/box.height;const dpr=Math.min(2,window.devicePixelRatio||1);canvas.width=Math.round(box.width*dpr);canvas.height=Math.round(box.height*dpr);camera=Math.max(0,Math.min(WORLD-WIDTH,camera));}
function toast(s){$('#toast').textContent=s;$('#toast').style.opacity=1;clearTimeout(toastTimer);toastTimer=setTimeout(()=>$('#toast').style.opacity=0,3000)}
function setOverlay(over,title,desc,button,hint,action){clearInput();$('#description').style.whiteSpace='normal';$('#description').style.maxHeight='none';$('#description').style.overflowY='visible';$('#overline').textContent=over;$('#title').innerHTML=title;$('#description').textContent=desc;$('#start').innerHTML=button+' <span>→</span>';$('#hint').textContent=hint;$('#overlay').classList.remove('hidden');$('.game').classList.remove('is-playing');overlayAction=action;$('#start').onclick=()=>{unlockAudio();action()};$('#restart').hidden=state==='intro'||state==='complete';$('#journal').hidden=state==='journal';syncMusic();$('#start').focus({preventScroll:true});}
function resume(){state='playing';$('#overlay').classList.add('hidden');$('.game').classList.add('is-playing');clearInput();canvas.focus({preventScroll:true});syncMusic();}
function init(index){li=index;const l=levels[li];state='intro';p={x:80,y:390,vx:0,vy:0,w:34,h:57,ground:false,face:1};camera=0;hearts=3;collected=0;companions=0;stoneCount=0;discovered=new Set();particles=[];checkpoint=80;invuln=0;coyote=0;ending=0;landSquash=0;clearInput();
platforms=[{x:0,y:492,w:650},{x:760,y:492,w:590},{x:1480,y:492,w:620},{x:2220,y:492,w:1380},{x:370,y:395,w:155},{x:870,y:388,w:160},{x:1160,y:330,w:135},{x:1580,y:388,w:160},{x:1860,y:330,w:135},{x:2300,y:392,w:165},{x:2630,y:355,w:150}];
lights=[220,425,570,805,925,1070,1210,1515,1650,1810,1900,2050,2300,2370,2510,2690,2860,3020].map((x,i)=>({x,y:(platforms.find(q=>q.x<=x&&q.x+q.w>=x&&q.y<490)?.y||492)-50,taken:false}));
scrolls=[{x:435,y:355,id:0},{x:1650,y:348,id:1},{x:2700,y:315,id:2}];animals=[{x:590,y:464,kind:'sheep',found:false},{x:1130,y:464,kind:'sheep',found:false},{x:2020,y:464,kind:'sheep',found:false}];stones=[270,970,1540,2400,2890].map(x=>({x,y:468,taken:false}));
$('#chapter').textContent=`0${li+1} / ${l.name.toUpperCase()}`;$('#objective').textContent=l.goal;document.querySelectorAll('[data-level]').forEach((b,i)=>b.classList.toggle('selected',i===li));setOverlay(`CHAPTER ${li+1} · ${l.ref.toUpperCase()}`,l.title,l.desc,'Begin the adventure',l.hint,resume);hud();}
function hud(){$('#score').textContent=`✦ ${collected}  ·  ▤ ${discovered.size}/3`;$('#hearts').textContent='♥ '.repeat(hearts)+'♡ '.repeat(3-hearts);$('#progress').style.width=Math.min(100,p.x/(WORLD-250)*100)+'%';$('#objective').textContent=li===0?`Sheep friends ${companions}/3 · Find home`:li===1?`Smooth stones ${stoneCount}/5 · ${actionLabel()} to sling`: `Story scrolls ${discovered.size}/3 · Find Saul`;}
function discover(id){discovered.add(id);saved[li]??={facts:[],complete:false};if(!saved[li].facts.includes(id))saved[li].facts.push(id);persist();hud();beep(900);state='discovery';const f=levels[li].facts[id];setOverlay('STORY DISCOVERED · '+f[2],f[0],f[1],'Keep exploring',`Character journal: ${saved[li].facts.length} of 3 discoveries saved`,resume)}
function journal(){const entries=Object.entries(saved).flatMap(([i,v])=>(v.facts||[]).map(id=>`${levels[i].name} — ${levels[i].facts[id][0]}: ${levels[i].facts[id][1]} (${levels[i].facts[id][2]})`));state='journal';setOverlay('YOUR CHARACTER JOURNAL','Every hero<br>has a story.',entries.length?entries.join('\n\n'):'Find glowing scrolls on the high paths to unlock real discoveries about each Bible character.','Back to adventure','Discoveries are saved on this device.',resume);$('#description').style.whiteSpace='pre-line';$('#description').style.maxHeight='210px';$('#description').style.overflowY='auto';}
function complete(){saved[li]??={facts:[],complete:false};saved[li].complete=true;persist();state='complete';beep(1000);setOverlay('CHAPTER COMPLETE · '+levels[li].ref,li===2?'A heart full<br>of discoveries.':'A little courage.<br>A big adventure.',levels[li].end+(discovered.size<3?' There are still hidden story scrolls to find!':''),li<2?'Explore the next chapter':'Play the stories again',`Collected ${collected} lights · Discovered ${discovered.size}/3 character facts`,()=>init((li+1)%3));}
function hurt(){if(invuln>0)return;hearts--;beep(160);if(hearts<=0){state='retry';setOverlay('EVERY HERO TRIES AGAIN','Take a breath.<br>Try again.', 'Your journal discoveries are safe. A fresh start is part of every adventure.','Try this chapter again','Tip: hold a direction and tap jump to cross gaps.',()=>{init(li);resume()});}else{p.x=checkpoint;p.y=370;p.vy=0;invuln=2;toast('A little stumble. Keep going!')}hud();}
function interact(){if(state!=='playing')return;if(li===0){animals.forEach(a=>{if(!a.found&&Math.abs(p.x-a.x)<90&&Math.abs(p.y+p.h-a.y)<85){a.found=true;companions++;beep();toast(['One sheep safe! Look for the next one.','Two sheep together! One more to find.','The flock is together! Guide them home.'][companions-1]);hud()}})}if(p.x>3190&&p.y+p.h>370){if(li===0&&companions>=3)complete();else if(li===1&&stoneCount>=5){burst(3330,380,35);state='ending';ending=1.2;beep(440)}else if(li===2&&discovered.size===3)complete();else toast(li===0?'Find all three sheep first.':li===1?'Find five smooth stones along the path.':'Discover all three scrolls on the higher paths.');}}
function burst(x,y,n=12){for(let i=0;i<n;i++)particles.push({x,y,vx:(Math.random()-.5)*180,vy:-Math.random()*200,life:1})}
function update(dt){
 t+=dt;particles.forEach(q=>{q.x+=q.vx*dt;q.y+=q.vy*dt;q.vy+=300*dt;q.life-=dt});particles=particles.filter(q=>q.life>0);
 if(state==='ending'){ending-=dt;if(ending<=0)complete();return;}if(state!=='playing')return;
 invuln=Math.max(0,invuln-dt);landSquash=Math.max(0,landSquash-dt*4);coyote=p.ground?.11:Math.max(0,coyote-dt);
 const target=input.move*265;p.vx+=(target-p.vx)*Math.min(1,dt*(p.ground?20:12));if(Math.abs(p.vx)<.5)p.vx=0;if(Math.abs(p.vx)>3)p.face=Math.sign(p.vx);
 if(input.jumpBuffer>0&&coyote>0){p.vy=-660;p.ground=false;coyote=0;input.consumeJump();burst(p.x+17,p.y+p.h,5);beep(350);}
 input.tick(dt);const oldBottom=p.y+p.h,wasGround=p.ground;p.vy+=1450*dt;p.x=Math.max(0,Math.min(WORLD-60,p.x+p.vx*dt));p.y+=p.vy*dt;p.ground=false;
 for(const q of platforms){if(p.x+p.w>q.x&&p.x<q.x+q.w&&oldBottom<=q.y+.5&&p.y+p.h>=q.y&&p.vy>=0){p.y=q.y-p.h;if(!wasGround&&p.vy>100){landSquash=.25;burst(p.x+17,q.y,4)}p.vy=0;p.ground=true;}}
 if(p.y>700){hurt();return;}if(p.ground&&p.x>780&&p.x<1300)checkpoint=790;if(p.ground&&p.x>1510&&p.x<2060)checkpoint=1510;if(p.ground&&p.x>2240)checkpoint=2240;
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
 ctx.save();ctx.translate(x+17,y+57);const moving=Math.abs(p.vx)>8&&p.ground&&state==='playing';const walk=moving?Math.sin(t*15)*6:0,bob=moving?Math.abs(Math.sin(t*15))*2:Math.sin(t*2)*.7;
 ctx.scale(scale*p.face*(1+landSquash*.3),scale*(1-landSquash*.25));ctx.translate(0,-29-bob);
 ellipse(0,31,23,4,'#142d2866');
 // Flowing scarf and a little satchel follow the movement.
 shape(li===2?'#8587af':'#b85c43',[[-10,-7],[-24,-6],[-35-Math.abs(p.vx)*.025,-2+Math.sin(t*7)*3],[-26,6],[-7,1]]);
 rect(-12,19+walk,10,14,'#483b30',4);rect(3,19-walk,10,14,'#483b30',4);line(-10,29+walk,-2,29+walk,'#c1a16d',3);line(5,29-walk,13,29-walk,'#c1a16d',3);
 const tunic=ctx.createLinearGradient(-15,0,18,0);tunic.addColorStop(0,li===2?'#57778b':'#ad7546');tunic.addColorStop(.5,li===2?'#8aabbb':'#e6be77');tunic.addColorStop(1,li===2?'#385768':'#a36a3d');
 shape(tunic,[[-13,-5],[11,-6],[18,23],[-17,23]]);line(-11,19,12,19,'#ead8a5',2);rect(-16,7,32,5,'#5e4833',2);rect(-4,7,7,5,'#dbb65e',1);line(-10,-4,13,12,'#664c33',3);rect(-22,5,12,15,'#7d5639',4);
 ellipse(0,-16,17,20,'#d99c66');ellipse(4,-18,13,17,'#f1c18b');ellipse(-15,-13,5,7,'#dc9f69');ellipse(9,-17,2.8,3.8,'#283b39');ellipse(10,-18,1,1,'#fff4c7');line(4,-5,10,-6,'#a26745',1.5);ellipse(11,-9,3,1.8,'#db9875');
 shape('#44362d',[[-17,-12],[-20,-26],[-13,-36],[1,-38],[14,-31],[18,-24],[8,-24],[4,-30],[-4,-23],[-10,-24],[-11,-11]]);for(let i=0;i<4;i++)ellipse(-12+i*7,-31-Math.sin(i)*3,6,5,'#604635');line(-15,-31,5,-36,'#8b633f',2);
 if(li===2)shape('#624832',[[-11,-5],[-6,9],[5,9],[13,-5],[5,0],[-3,-1]]);
 rect(-23,-1-walk*.5,9,17,'#d9a16e',5);rect(15,-1+walk*.5,9,18,'#efbd84',5);
 if(li===0){line(27,-21,27,33,'#4b3b2d',4);ctx.strokeStyle='#9b7a4b';ctx.lineWidth=3;ctx.beginPath();ctx.arc(22,-21,5,Math.PI,Math.PI*2);ctx.stroke();}if(li===1){ctx.strokeStyle='#684e38';ctx.lineWidth=2.5;ctx.beginPath();ctx.arc(28,2,9,-1,2.5);ctx.stroke();}ctx.restore();
}
function animal(a){let x=a.x,y=a.y;if(a.found){const idx=animals.filter(v=>v.found).indexOf(a);x=p.x-40-idx*40;y=p.y+p.h-12}ellipse(x,y+10,17,4,'#26474620');if(a.kind==='dove'){ellipse(x,y,13,9,'#f8f2d6');ellipse(x+10,y-8,6,7,'#fff8e6');shape('#c49f5a',[[x+14,y-9],[x+23,y-6],[x+14,y-5]]);line(x-4,y-2,x-17,y-13+Math.sin(t*5)*5,'#d9e9df',5)}else{ellipse(x,y,17,12,'#eee6cf');ellipse(x+12,y-9,9,10,'#f8edcf');if(a.kind==='rabbit'){ellipse(x+9,y-24,3,13,'#f5e8cd');ellipse(x+17,y-23,3,12,'#e4d8bb')}else{ellipse(x-8,y-5,12,12,'#faf1d9');line(x-10,y+7,x-10,y+15,'#605e48',3);line(x+10,y+7,x+10,y+15,'#605e48',3)}ellipse(x+15,y-10,2,2,'#354c42')}if(!a.found&&Math.abs(p.x-a.x)<100){rect(x-24,y-65,48,25,'#f9f0d6',8);ctx.fillStyle='#45634f';ctx.font='12px sans-serif';ctx.fillText(actionLabel()+' ♥',x-18,y-48)}}
function destination(){const x=3240;if(li===0){rect(x+40,353,245,142,'#c9b58c',7);shape('#806d51',[[x+20,359],[x+161,276],[x+305,359]]);rect(x+133,411,53,83,'#6b654a',25);rect(x+68,389,36,35,'#eee0af',4);for(let i=0;i<7;i++)line(x+10+i*43,455,x+10+i*43,508,'#92774e',7);line(x,469,x+300,469,'#a18b5f',7);}if(li===1){ctx.save();if(state==='ending'){ctx.translate(x+112,489);ctx.rotate((1.2-ending)*1.2);ctx.translate(-x-112,-489);}ellipse(x+110,489,46,8,'#3a4c3533');rect(x+83,421,23,70,'#5b4d40',7);rect(x+119,421,23,70,'#5b4d40',7);rect(x+65,329,95,100,'#717d72',17);rect(x+68,366,90,18,'#c4b37a');ellipse(x+112,303,32,34,'#c99c73');shape('#677873',[[x+76,303],[x+80,270],[x+112,256],[x+145,274],[x+147,303]]);rect(x+143,353,54,78,'#a68559',24);line(x+48,277,x+48,489,'#665442',5);shape('#bbc3ac',[[x+37,282],[x+48,247],[x+60,282]]);ctx.restore();}if(li===2){ellipse(x+140,386,190,150,'#6c7775');ellipse(x+105,414,100,105,'#364d50');ellipse(x+150,481,34,8,'#253d3e');rect(x+131,418,40,69,'#b48c62',12);ellipse(x+151,401,18,22,'#dcb78d');shape('#cdb77a',[[x+133,384],[x+130,371],[x+144,377],[x+151,365],[x+159,377],[x+171,371],[x+169,387]]);rect(x+131,443,40,8,'#e5c780');} if(p.x>3120){ctx.font='bold 13px sans-serif';ctx.fillStyle='#fff9dd';ctx.fillText(li===1?actionLabel()+' · Use your sling':li===2?actionLabel()+' · Choose mercy':'Home →',x-55,230)}}
function draw(){
 ctx.setTransform(canvas.width/WIDTH,0,0,canvas.height/HEIGHT,0,0);ctx.clearRect(0,0,WIDTH,HEIGHT);const l=levels[li];
 const gr=ctx.createLinearGradient(0,0,0,600);gr.addColorStop(0,l.color[0]);gr.addColorStop(1,l.color[1]);ctx.fillStyle=gr;ctx.fillRect(0,0,WIDTH,HEIGHT);
 if(sceneryReady){const bh=scenery.naturalHeight/3,bw=scenery.naturalWidth,sw=Math.min(bw,bh*WIDTH/HEIGHT),sx=(bw-sw)*(.12+.76*camera/Math.max(1,WORLD-WIDTH));ctx.drawImage(scenery,sx,li*bh+.5,sw,bh-1,0,0,WIDTH,HEIGHT);}
 else{for(let k=0;k<2;k++){ctx.beginPath();ctx.moveTo(0,600);for(let x=0;x<=WIDTH+20;x+=20)ctx.lineTo(x,290+k*80+Math.sin((x+camera*(.13+k*.12))/220)*50);ctx.lineTo(WIDTH,600);ctx.fillStyle=k?'#527e7155':'#729d8455';ctx.fill();}}
 const mist=ctx.createLinearGradient(0,250,0,560);mist.addColorStop(0,'#bedbc000');mist.addColorStop(1,li===2?'#678fa34d':'#ffecc033');ctx.fillStyle=mist;ctx.fillRect(0,250,WIDTH,350);
 if(!reducedMotion){for(let i=0;i<17;i++){const x=((i*131+t*(5+i%3)-camera*.35)%(WIDTH+60)+WIDTH+60)%(WIDTH+60)-30;const y=110+(i*61)%320+Math.sin(t*.6+i)*16;ellipse(x,y,li===2?2:1.3,li===2?2:1.3,li===2?'#eafab899':'#ffefc977');}}
 ctx.save();ctx.translate(-camera,0);destination();for(const q of platforms){if(q.x>camera+WIDTH+50||q.x+q.w<camera-50)continue;drawPlatform(q);}
for(const a of lights)if(!a.taken){ellipse(a.x,a.y,16,16,'#ffedb218');star(a.x,a.y+Math.sin(t*3+a.x)*4,11)}for(const s of scrolls)if(!discovered.has(s.id)){const y=s.y+Math.sin(t*2)*4;ellipse(s.x,y,28,28,'#fff4bd25');rect(s.x-12,y-16,24,32,'#f6dfad',3);rect(s.x-15,y-17,29,5,'#b78b4f',3);for(let i=0;i<3;i++)line(s.x-6,y-7+i*6,s.x+6,y-7+i*6,'#b58e59',2);star(s.x+16,y-19,7)}if(li===0)animals.forEach(animal);if(li===1)for(const s of stones)if(!s.taken){ellipse(s.x,s.y,10,7,'#d8e2d1');ellipse(s.x-2,s.y-3,5,2,'#f8f1d2')}
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
function pause(){if(state==='playing'){state='paused';setOverlay('TAKE A LITTLE BREAK','Your adventure<br>can wait.',levels[li].hint,'Keep playing','Controller: A / × continues · shoulders switch chapters',resume)}else if(state==='paused')resume()}
function pollController(){
 let pads=[];try{pads=navigator.getGamepads?navigator.getGamepads():[];}catch{}
 const actions=input.poll(pads);const status=$('#controller-status');
 const message=actions.connected?'Controller ready · A / × jump · X / □ act · Start pause':actions.unsupported?'This controller needs standard mapping. Touch and keyboard still work.':'Controller: pair with your device, then press a button';if(status.textContent!==message)status.textContent=message;
 if(actions.disconnected&&state==='playing'){pause();toast('Controller disconnected. Reconnect or use touch controls.');}
 if(document.hidden||$('#music-panel').open)return;
 if(actions.pause){pause();return;}
 if(state==='playing'){if(actions.interact)interact();if(actions.journal)journal();}
 else if(state!=='ending'){if(actions.jump&&overlayAction)overlayAction();else if(actions.back&&state==='paused')resume();else if(actions.journal&&state!=='journal')journal();else if(actions.prev)init((li+2)%3);else if(actions.next)init((li+1)%3);}
}
const gameKeys=['ArrowLeft','ArrowRight','ArrowUp','KeyA','KeyD','KeyW','Space','KeyE','KeyP','Escape','KeyJ'];
window.addEventListener('keydown',e=>{
 if($('#music-panel').open||/INPUT|TEXTAREA|SELECT/.test(e.target.tagName))return;
 if(!gameKeys.includes(e.code))return;
 if(state==='playing'||e.code==='Escape'||e.code==='KeyP')e.preventDefault();if(e.repeat)return;
 input.press(e.code);unlockAudio();
 if(e.code==='KeyE')interact();if(e.code==='KeyP'||e.code==='Escape')pause();if(e.code==='KeyJ')journal();
});window.addEventListener('keyup',e=>input.release(e.code));
function suspend(){clearInput();if(state==='playing')pause();music.pause();if(audioCtx?.state==='running')audioCtx.suspend().catch(()=>{});}
window.addEventListener('blur',suspend);document.addEventListener('visibilitychange',()=>{if(document.hidden)suspend()});window.addEventListener('pagehide',suspend);
$('#pause').onclick=pause;$('#sound').onclick=()=>{sound=!sound;$('#sound').textContent=sound?'Effects on':'Effects off';$('#sound').setAttribute('aria-pressed',String(sound));unlockAudio();beep()};
$('#journal').onclick=$('#journal-outside').onclick=journal;
$('#restart').onclick=()=>{init(li);resume()};
$('#expand').onclick=async()=>{const focused=document.body.classList.toggle('game-focus');$('#expand').textContent=focused?'✕':'⛶';$('#expand').setAttribute('aria-label',focused?'Exit expanded game':'Expand game');requestAnimationFrame(sizeCanvas);};
for(const b of document.querySelectorAll('[data-level]'))b.onclick=()=>init(+b.dataset.level);
for(const b of document.querySelectorAll('[data-action]')){
 b.addEventListener('pointerdown',e=>{if(state!=='playing')return;e.preventDefault();unlockAudio();b.setPointerCapture(e.pointerId);input.touch(e.pointerId,b.dataset.action);b.classList.add('pressed');if(b.dataset.action==='interact')interact();});
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
init(0);sizeCanvas();requestAnimationFrame(frame);
