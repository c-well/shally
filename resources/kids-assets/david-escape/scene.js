import * as T from '/kids-assets/vendor/three-0.180.0/three.module.min.js';
import {platforms,obstacles,guardPositions,JONATHAN} from './world.js';
const color={sand:0xb9a17d,trim:0xd9c394,stone:0x8b9282,grass:0x566e57,wall:0x9d8a6e,crate:0x65513b,rock:0x737d70};
export function createScene(canvas){
 const renderer=new T.WebGLRenderer({canvas,antialias:true,powerPreference:'high-performance'});
 renderer.setPixelRatio(Math.min(devicePixelRatio,matchMedia('(pointer:coarse)').matches?1.25:1.6));renderer.shadowMap.enabled=true;renderer.shadowMap.type=T.PCFSoftShadowMap;renderer.outputColorSpace=T.SRGBColorSpace;renderer.toneMapping=T.ACESFilmicToneMapping;renderer.toneMappingExposure=1.15;
 const scene=new T.Scene();const sky=document.createElement('canvas');sky.width=4;sky.height=256;const sk=sky.getContext('2d'),gradient=sk.createLinearGradient(0,0,0,256);gradient.addColorStop(0,'#183950');gradient.addColorStop(.55,'#8a9c9e');gradient.addColorStop(1,'#e6b485');sk.fillStyle=gradient;sk.fillRect(0,0,4,256);scene.background=new T.CanvasTexture(sky);scene.fog=new T.FogExp2(0x8e9994,.012);
 const stoneCanvas=document.createElement('canvas');stoneCanvas.width=stoneCanvas.height=256;const stoneContext=stoneCanvas.getContext('2d');stoneContext.fillStyle='#bcae94';stoneContext.fillRect(0,0,256,256);for(let row=0;row<8;row++)for(let col=-1;col<5;col++){const x=col*64+(row%2)*32,y=row*32;const shade=165+Math.floor(rng(row*13+col+20)*30);stoneContext.fillStyle=`rgb(${shade+22},${shade+12},${shade})`;stoneContext.fillRect(x+1,y+1,62,30);stoneContext.fillStyle='#ffffff09';stoneContext.fillRect(x+2,y+2,60,1);}const masonry=new T.CanvasTexture(stoneCanvas);masonry.colorSpace=T.SRGBColorSpace;masonry.wrapS=masonry.wrapT=T.RepeatWrapping;masonry.anisotropy=4;
 const glowCanvas=document.createElement('canvas');glowCanvas.width=glowCanvas.height=64;const gc=glowCanvas.getContext('2d'),gl=gc.createRadialGradient(32,32,0,32,32,32);gl.addColorStop(0,'#fff1bdcc');gl.addColorStop(.2,'#ffd58755');gl.addColorStop(1,'#ffd58700');gc.fillStyle=gl;gc.fillRect(0,0,64,64);const glowTexture=new T.CanvasTexture(glowCanvas);
 const camera=new T.PerspectiveCamera(57,1,.1,180);
 scene.add(new T.HemisphereLight(0xc0e2f3,0x655e48,2.5));
 const sun=new T.DirectionalLight(0xffd3a0,3.4);sun.position.set(-14,28,12);sun.castShadow=true;sun.shadow.mapSize.set(1024,1024);Object.assign(sun.shadow.camera,{left:-24,right:24,top:24,bottom:-24,near:1,far:75});sun.shadow.bias=-.001;sun.shadow.normalBias=.03;scene.add(sun,sun.target);
 const mats=new Map(),solidMeshes=[];
 function mat(c,extra={}){const key=c+'|'+(extra.map?.uuid||'')+'|'+(extra.emissive||'')+'|'+(extra.emissiveIntensity||'');if(!mats.has(key))mats.set(key,new T.MeshStandardMaterial({color:c,roughness:.91,...extra}));return mats.get(key);}
 const cube=new T.BoxGeometry(1,1,1),sphere=new T.SphereGeometry(1,10,7),cylinder=new T.CylinderGeometry(1,1,1,10);
 function box(x,y,z,w,h,d,c,parent=scene,solid=false){const m=new T.Mesh(cube,mat(c));m.position.set(x,y,z);m.scale.set(w,h,d);m.receiveShadow=true;m.castShadow=solid;parent.add(m);if(solid)solidMeshes.push(m);return m;}
 function orb(x,y,z,sx,sy,sz,c,parent=scene){const m=new T.Mesh(sphere,mat(c));m.position.set(x,y,z);m.scale.set(sx,sy,sz);parent.add(m);return m;}
 function pole(x,y,z,r,h,c,parent=scene){const m=new T.Mesh(cylinder,mat(c));m.position.set(x,y,z);m.scale.set(r,h,r);parent.add(m);return m;}
 function rng(n){return Math.abs(Math.sin(n*173.17+21.5)*43758.5453)%1;}
 for(const p of [...platforms,...obstacles]){
  const h=p.top-p.bottom,c=color[p.kind]||color.sand;
  const structure=box(p.x,p.bottom+h/2,p.z,p.w,h,p.d,c,scene,true);if(['roof','wall','stone'].includes(p.kind))structure.material=mat(c,{map:masonry});
  if(p.kind==='roof'){
   box(p.x,p.top+.025,p.z,p.w+.18,.05,p.d+.18,color.trim);
   // Blockwork gives roof edges depth and scale without a flat illustrated facade.
   for(let row=0;row<1;row++)for(let col=0;col<Math.floor(p.w/1.1);col++)box(p.x-p.w/2+.6+col*1.1,p.top-1-row*1.25,p.z+p.d/2+.02,.98,1.05,.06,row%2?0xa18d71:0xac9879);
   for(let col=0;col<Math.floor(p.d/1.3);col++)box(p.x+p.w/2+.025,p.top-.5,p.z-p.d/2+.7+col*1.3,.06,.6,1.12,0xb5a181);
  }
  if(p.kind==='crate'){
   for(const dx of [-p.w*.4,p.w*.4])box(p.x+dx,p.bottom+h/2,p.z+p.d/2+.04,.12,h,.09,0xb89863);
   box(p.x,p.top+.035,p.z,p.w+.08,.07,p.d+.08,0x947750);
  }
 }
 // Michal's doorway and a visible rope mark the starting point.
 box(-2.1,6.75,4.65,1.8,3.5,.5,0xaf9879);box(2.1,6.75,4.65,1.8,3.5,.5,0xaf9879);box(0,8.4,4.65,2.4,.45,.5,0xd5ba8c);
 pole(1.1,6.8,4.1,.035,3.7,0xd9c18d);box(0,5.03,3.5,2.2,.05,1.5,0x935b48);
 // Courtyard gate. Its opening is part of the navigable route.
 box(0,5.4,-74,4,.5,1.3,0xc4ae85);box(0,5.9,-74,3.6,.5,1.5,0xb09b77);
 // Roof pottery, courtyard banners, and distant houses give the route a lived-in setting.
 for(const p of platforms.filter(p=>p.kind==='roof')){const x=p.x-p.w/2+.6,z=p.z-1;const pot=pole(x,p.top+.36,z,.3,.65,0x8e6047);pot.scale.x=.37;orb(x,p.top+.52,z,.28,.2,.28,0x9e7052);}
 for(let i=0;i<10;i++){const z=-46-i*3;box(-8.97,3.9,z,.12,2.8,.24,0xd1b68c);box(8.97,3.9,z,.12,2.8,.24,0xd1b68c);}
 for(const x of [-8.8,8.8])for(const z of [-51,-64]){const banner=box(x,4.2,z,.07,1.7,.8,0x3d6572);banner.rotation.x=.08;pole(x,5.15,z,.04,1.1,0xbeb18e).rotation.x=Math.PI/2;}
 box(0,-7,-48,180,.5,240,0x8e836b);
 for(let i=0;i<44;i++){const side=i%2?1:-1,x=side*(12+rng(i)*25),z=14-i*2.3,h=3+rng(i+50)*6,w=3+rng(i+20)*4,d=4+rng(i+9)*3;const house=box(x,h/2-3,z,w,h,d,[0x9f9985,0xa7987f,0xafa185][i%3]);house.material=mat(0xb7ab91,{map:masonry});box(x,h-2.94,z,w+.2,.15,d+.2,0xc4b796);for(const dx of [-w*.28,w*.28]){box(x+dx,h-4.1,z+d/2+.025,.55,.95,.04,0x4e5b59);box(x+dx,h-3.55,z+d/2+.07,.72,.12,.14,0xc1b295);}for(const xx of [-w/2,w/2])box(x+xx,h-2.65,z,.16,.6,d,0xb8ab8f);if(i%3===0)orb(x,h-2.85,z,w*.35,w*.25,w*.35,0xb2a588);}
 // A few suspended cloth shades and timber beams break up the stone silhouettes.
 for(const [x,z] of [[-11,-12],[12,-28],[-12,-43]]){const shade=box(x,3,z,5,.04,3,0x8c674f);shade.rotation.z=.09;pole(x-2,1,z-1,.06,4,0x655842);pole(x+2,1.3,z+1,.06,4.6,0x655842);}
 const lanterns=[];
 function lantern(x,y,z){pole(x,y+.45,z,.045,.9,0x433b32);const lamp=box(x,y+.85,z,.25,.36,.25,0xffd69b);lamp.material=mat(0xffcc77,{emissive:0xffa648,emissiveIntensity:1.6});const glow=new T.Sprite(new T.SpriteMaterial({map:glowTexture,transparent:true,depthWrite:false}));glow.position.set(x,y+.85,z);glow.scale.set(1.7,1.7,1);scene.add(glow);lanterns.push(lamp);orb(x,y+.06,z,.5,.015,.5,0xccad72);}
 [[-2.2,5,1],[-1.8,5,-9],[.2,6.8,-17],[.2,6.8,-26],[3.2,5,-34],[-7.8,1.5,-48],[7.7,1.5,-61],[-2.7,1.5,-74]].forEach(v=>lantern(...v));
 // Field path: irregular stepping stones, olive trees and grass clusters.
 for(let i=0;i<23;i++){const z=-77-i*1.45,x=Math.sin(i*.22)*2;const m=box(x,1.53,z,1.4+rng(i),.08,1.1,0x94947b);m.rotation.y=rng(i)*.6;}
 function olive(x,z,scale=1){const g=new T.Group();g.position.set(x,1.5,z);g.scale.setScalar(scale);scene.add(g);const trunk=pole(0,1.4,0,.16,2.8,0x66604b,g);trunk.rotation.z=.11;for(let j=0;j<6;j++){const a=j*Math.PI/3;orb(Math.cos(a)*.7,2.7+rng(j)*.7,Math.sin(a)*.65,1.05,.75,.95,j%2?0x6f8260:0x889477,g);}return g;}
 [[-8,-82,1.2],[8,-89,1.3],[-7,-102,1.1],[6.8,-111,1.4],[9,-79,.7],[-10,-112,1.5]].forEach(p=>olive(...p));
 // Rolling hills frame the meeting rather than ending at an empty horizon.
 for(let i=0;i<14;i++){const x=(i-7)*9,z=-145-rng(i+90)*22;orb(x,-1,z,15+rng(i)*6,6+rng(i+1)*8,13, i%2?0x7c8d6d:0x8b9977);}
 for(let i=0;i<16;i++){const side=i%2?1:-1;olive(side*(13+rng(i+45)*12),-81-rng(i+19)*60,.65+rng(i+51)*.7);}
 // The arrow is a visual reminder of Jonathan's warning, not a combat collectible.
 const shaft=pole(-1.4,1.72,-91,.017,.85,0xcbb287);shaft.rotation.z=.65;const feathers=box(-1.66,2.03,-91,.12,.12,.025,0xdfd9b9);feathers.rotation.z=.65;
 const grassGeo=new T.ConeGeometry(.1,.55,3),grass=new T.InstancedMesh(grassGeo,mat(0x8b9868),220),dummy=new T.Object3D();
 for(let i=0;i<220;i++){let x=(rng(i+600)-.5)*22,z=-77-rng(i+900)*38;if(Math.abs(x)<2.2)x+=x>0?3:-3;dummy.position.set(x,1.76,z);dummy.rotation.y=rng(i)*6;dummy.scale.setScalar(.6+rng(i+10));dummy.updateMatrix();grass.setMatrixAt(i,dummy.matrix);}scene.add(grass);
 function person({tunic=0xc5b18a,cape=0x385e73,hair=0x342b25,guard=false}={}){
  const root=new T.Group(),body=new T.Group();root.add(body);scene.add(root);
  const hips=new T.Group();hips.position.y=.68;body.add(hips);
  const tunicMesh=new T.Mesh(new T.CylinderGeometry(.24,.31,.65,8),mat(tunic));tunicMesh.position.y=.93;tunicMesh.castShadow=true;body.add(tunicMesh);
  pole(0,.78,0,.27,.09,0x614b34,body);box(0,.79,.267,.075,.09,.04,0xcbb178,body);
  orb(0,1.46,0,.225,.26,.205,0xae805e,body);pole(0,1.22,0,.1,.15,0xae805e,body);
  for(let i=0;i<13;i++){const a=i*2.4;orb(Math.cos(a)*.17,1.61+(i%3)*.035,Math.sin(a)*.15,.09,.095,.09,hair,body);}
  // Small facial details; the camera mostly sees David's silhouette and cape.
  for(const x of [-.075,.075])orb(x,1.48,.191,.018,.018,.013,0x302d29,body);
  orb(0,1.42,.204,.035,.04,.03,0xbe906e,body);
  const cloth=box(0,.97,-.24,.45,.7,.07,cape,body);cloth.rotation.x=-.12;
  const limbs=[];
  for(const side of [-1,1]){
   const arm=new T.Group();arm.position.set(side*.29,1.17,0);body.add(arm);pole(0,-.16,0,.075,.32,tunic,arm);pole(0,-.4,.015,.055,.23,0xae805e,arm);orb(0,-.54,.025,.065,.07,.065,0xae805e,arm);
   const leg=new T.Group();leg.position.set(side*.12,.63,0);body.add(leg);pole(0,-.2,0,.083,.38,0x887557,leg);pole(0,-.46,.01,.08,.22,0x594c3c,leg);box(0,-.58,.055,.17,.12,.29,0x473d32,leg);limbs.push({arm,leg,side});
  }
  if(guard){pole(.42,.95,0,.025,1.9,0x705b43,root);const spear=new T.Mesh(new T.ConeGeometry(.075,.22,4),mat(0x9ca7a1));spear.position.set(.42,1.98,0);root.add(spear);}
  return {root,body,limbs,cloth};
 }
 const david=person(),jonathan=person({tunic:0x8fa3a1,cape:0xb49150});jonathan.root.position.set(JONATHAN.x,JONATHAN.y,JONATHAN.z);jonathan.root.rotation.y=.1;
 const guards=[person({tunic:0x596067,cape:0x7c6354,guard:true}),person({tunic:0x596067,cape:0x7c6354,guard:true})];
 const coneGeo=new T.CircleGeometry(7.4,24,-.98,1.96);coneGeo.rotateX(-Math.PI/2);
 const cones=guards.map(()=>{const m=new T.Mesh(coneGeo,new T.MeshBasicMaterial({color:0xeac681,transparent:true,opacity:.12,depthWrite:false,side:T.DoubleSide}));scene.add(m);return m;});
 const shadow=new T.Mesh(new T.CircleGeometry(.38,20),new T.MeshBasicMaterial({color:0x132c31,transparent:true,opacity:.32,depthWrite:false}));shadow.rotation.x=-Math.PI/2;scene.add(shadow);
 // Soft dust particles are pooled so jumps do not allocate render objects.
 const dust=Array.from({length:18},()=>{const m=new T.Mesh(sphere,new T.MeshBasicMaterial({color:0xe2c492,transparent:true,opacity:0,depthWrite:false}));m.scale.setScalar(.06);scene.add(m);return {mesh:m,life:0,vx:0,vz:0};});let dustIndex=0;
 function puff(p){for(let i=0;i<6;i++){const q=dust[dustIndex++%dust.length],a=i*Math.PI/3;q.mesh.position.set(p.x,p.y+.07,p.z);q.life=.5;q.vx=Math.cos(a)*.9;q.vz=Math.sin(a)*.9;}}
 let yaw=0,cameraReady=false,anim=0;const target=new T.Vector3(),desired=new T.Vector3(),ray=new T.Raycaster();
 function animatePerson(person,speed,t,crouch=false,hang=false,air=false){
  const wave=Math.sin(t*10)*Math.min(speed/5.6,1);person.body.position.y=crouch?-.24:Math.abs(wave)*.025;person.body.rotation.x=crouch?.35:0;
  person.limbs.forEach(({arm,leg,side})=>{arm.rotation.x=hang?-2.7:air?-1.0:-wave*side*.8;arm.rotation.z=hang?side*.25:side*.08;leg.rotation.x=crouch?-.7:hang?.2:air?.4:wave*side*.85;});
  person.cloth.rotation.x=-.12-Math.min(speed/5.6,1)*.3+Math.sin(t*4)*.035;
 }
 function resize(){const w=canvas.clientWidth,h=canvas.clientHeight;renderer.setSize(w,h,false);camera.aspect=w/h;camera.updateProjectionMatrix();}resize();
 function render(s,dt){
  anim+=dt;const p=s.p;david.root.position.set(p.x,p.y,p.z);let delta=p.face-david.root.rotation.y;delta=Math.atan2(Math.sin(delta),Math.cos(delta));david.root.rotation.y+=delta*Math.min(1,dt*14);
  animatePerson(david,Math.hypot(p.vx,p.vz),anim,p.crouch,!!p.hang||!!p.climb,!p.ground&&!p.hang&&!p.climb);
  animatePerson(jonathan,0,anim);jonathan.root.rotation.y=Math.atan2(p.x-JONATHAN.x,p.z-JONATHAN.z);
  guardPositions(s.time).forEach((g,i)=>{guards[i].root.position.set(g.x,g.y,g.z);guards[i].root.rotation.y=Math.atan2(g.dx,0);animatePerson(guards[i],2,anim+i);cones[i].position.set(g.x,1.52,g.z);cones[i].rotation.z=g.dx>0?0:Math.PI;cones[i].material.opacity=s.checkpoint===1?.3:0;});
  if(s.events.includes('land')||s.events.includes('jump'))puff(p);
  for(const q of dust){q.life=Math.max(0,q.life-dt);q.mesh.material.opacity=q.life*.55;q.mesh.position.x+=q.vx*dt;q.mesh.position.z+=q.vz*dt;q.mesh.position.y+=dt*.3;}
  let floor=1.5;for(const b of platforms){if(Math.abs(p.x-b.x)<=b.w/2&&Math.abs(p.z-b.z)<=b.d/2&&b.top<=p.y+.1)floor=b.top;}
  shadow.position.set(p.x,floor+.025,p.z);shadow.visible=p.y-floor<4;shadow.scale.setScalar(1+Math.max(0,p.y-floor)*.12);
  target.set(p.x,p.y+1.3,p.z);desired.set(p.x+Math.sin(yaw)*7.8,p.y+4.8,p.z+Math.cos(yaw)*7.8);
  const direction=desired.clone().sub(target),length=direction.length();ray.set(target,direction.normalize());const hit=ray.intersectObjects(solidMeshes,false).find(h=>h.distance>.3&&h.distance<length);
  if(hit)desired.copy(target).addScaledVector(direction,Math.max(1.25,hit.distance-.3));
  if(!cameraReady||camera.position.distanceTo(desired)>18){camera.position.copy(desired);cameraReady=true;}else camera.position.lerp(desired,1-Math.exp(-dt*8));
  camera.lookAt(p.x,p.y+1.1,p.z-1.6);sun.position.set(p.x-14,p.y+28,p.z+12);sun.target.position.set(p.x,p.y,p.z-8);
  lanterns.forEach((l,i)=>l.material.emissiveIntensity=1.4+Math.sin(anim*4+i)*.15);
  renderer.render(scene,camera);
 }
 return {render,resize,rotate:d=>{yaw+=d;},recenter:()=>{yaw=0;cameraReady=false;},yaw:()=>yaw,dispose:()=>renderer.dispose()};
}
