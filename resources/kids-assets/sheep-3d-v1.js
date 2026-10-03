// A locally rendered, articulated mascot. The PNG remains the no-WebGL fallback.
const sheepNodes=[...document.querySelectorAll('[data-sheep]')];
const reduced=matchMedia('(prefers-reduced-motion: reduce)');
let engine,frame=0,last=0;
const models=new Map(),pending=new Set();
function visible(el){return !document.hidden&&el.getBoundingClientRect().bottom>0&&el.getBoundingClientRect().top<innerHeight&&getComputedStyle(el).visibility!=='hidden'&&!el.closest('[inert]');}
function still(){return reduced.matches||document.body.classList.contains('mascot-still');}
function schedule(){if(!frame)frame=requestAnimationFrame(tick);}
function tick(now){frame=0;if(now-last<32){schedule();return;}last=now;let again=false;for(const [el,m]of models){if(!visible(el))continue;m.draw(now,still());if(!still()&&!el.classList.contains('celebration-sheep'))again=true;if(!still()&&now<m.hopEnd)again=true;}if(again)schedule();}
async function mount(el){if(models.has(el)||pending.has(el)||!visible(el))return;pending.add(el);try{engine??=import('./vendor/three-0.180.0/three.module.min.js');const T=await engine;if(!visible(el)){pending.delete(el);return;}const model=createSheep(T,el);models.set(el,model);el.classList.add('sheep-3d-ready');schedule();}catch(error){el.classList.remove('sheep-3d-ready');el.dataset.mascotRenderer='image';}finally{pending.delete(el);}}
function createSheep(T,el){
 const renderer=new T.WebGLRenderer({alpha:true,antialias:true,powerPreference:'low-power'});
 renderer.setPixelRatio(Math.min(devicePixelRatio||1,1.7));renderer.setClearColor(0x000000,0);renderer.outputColorSpace=T.SRGBColorSpace;renderer.toneMapping=T.ACESFilmicToneMapping;renderer.toneMappingExposure=1.1;
 renderer.shadowMap.enabled=true;renderer.shadowMap.type=T.PCFSoftShadowMap;
 const canvas=renderer.domElement;canvas.className='sheep-canvas';canvas.setAttribute('aria-hidden','true');el.append(canvas);
 const scene=new T.Scene(),camera=new T.PerspectiveCamera(28,1,.1,30);camera.position.set(0,1.6,6.5);camera.lookAt(0,1.52,0);
 scene.add(new T.HemisphereLight(0xdceaff,0x846441,1.45));
 const sun=new T.DirectionalLight(0xffedce,3.1);sun.position.set(-3,5,5);sun.castShadow=true;sun.shadow.mapSize.set(512,512);Object.assign(sun.shadow.camera,{left:-2,right:2,top:4,bottom:-1,near:.1,far:12});sun.shadow.bias=-.001;sun.shadow.normalBias=.025;scene.add(sun);
 const rim=new T.DirectionalLight(0xc6d4ff,1.7);rim.position.set(3,2,-3);scene.add(rim);
 const mat=(color,roughness=.72)=>new T.MeshStandardMaterial({color,roughness});
 const wool=mat(0xfff3d9),skin=mat(0xf2c99c),pink=mat(0xe28b86),hoof=mat(0x4c332a),coral=mat(0xe86f4e),strap=mat(0x923d31),gold=mat(0xf3c166,.35),white=mat(0xfffcf4,.3),iris=mat(0x583021,.25),black=mat(0x170e14,.18);
 const fleeceCanvas=document.createElement('canvas');fleeceCanvas.width=fleeceCanvas.height=128;const fleeceBrush=fleeceCanvas.getContext('2d');fleeceBrush.fillStyle='#888';fleeceBrush.fillRect(0,0,128,128);for(let n=0;n<1900;n++){const x=(n*73)%128,y=(n*n*19+n*31)%128;fleeceBrush.fillStyle=n%2?'#aaa':'#666';fleeceBrush.beginPath();fleeceBrush.arc(x,y,.65,0,Math.PI*2);fleeceBrush.fill();}const fleeceTexture=new T.CanvasTexture(fleeceCanvas);fleeceTexture.wrapS=fleeceTexture.wrapT=T.RepeatWrapping;fleeceTexture.repeat.set(3,3);wool.bumpMap=fleeceTexture;wool.bumpScale=.009;wool.roughness=.95;
 const sphere=new T.SphereGeometry(1,24,18),puffGeo=new T.SphereGeometry(1,12,9);
 function ball(parent,material,x,y,z,sx,sy,sz){const m=new T.Mesh(sphere,material);m.position.set(x,y,z);m.scale.set(sx,sy,sz);m.castShadow=true;m.receiveShadow=true;parent.add(m);return m;}
 function curve(parent,points,material,r=.013){const c=new T.CatmullRomCurve3(points.map(p=>new T.Vector3(...p)));const m=new T.Mesh(new T.TubeGeometry(c,20,r,6,false),material);parent.add(m);return m;}
 const puppet=new T.Group();scene.add(puppet);
 const torso=new T.Group();torso.position.y=1.18;puppet.add(torso);
 ball(torso,wool,0,0,0,.55,.58,.43);
 function fleece(parent,count,rx,ry,rz,radius,filter=()=>true){const poses=[];const q=new T.Object3D();for(let i=0;i<count;i++){const y=1-2*(i+.5)/count,a=i*2.399963,rad=Math.sqrt(1-y*y),x=Math.cos(a)*rad,z=Math.sin(a)*rad;if(!filter(x,y,z))continue;const r=radius*(.88+.15*Math.sin(i*7.1));q.position.set(x*rx,y*ry,z*rz);q.rotation.set(i*.2,i*.31,i*.1);q.scale.set(r,r*.91,r);q.updateMatrix();poses.push(q.matrix.clone());}const mesh=new T.InstancedMesh(puffGeo,wool,poses.length);poses.forEach((m,i)=>mesh.setMatrixAt(i,m));mesh.castShadow=true;mesh.receiveShadow=true;parent.add(mesh);}
 fleece(torso,100,.51,.52,.40,.155);
 // Two separate legs and hoof halves make the leap an articulated action.
 const legs=[];for(const side of [-1,1]){const leg=new T.Group();leg.position.set(side*.24,.76,0);puppet.add(leg);ball(leg,wool,0,-.19,.01,.17,.29,.17);ball(leg,hoof,-.065,-.48,.06,.092,.17,.18);ball(leg,hoof,.065,-.48,.06,.092,.17,.18);legs.push(leg);}
 const arms=[];for(const side of [-1,1]){const arm=new T.Group();arm.position.set(side*.47,1.47,.08);arm.rotation.z=side*.2;puppet.add(arm);ball(arm,wool,0,-.12,.05,.15,.27,.16);ball(arm,hoof,0,-.34,.085,.13,.135,.14);arms.push(arm);}
 // Rounded coral backpack with real straps, pocket, buckles and rolled blanket.
 const pack=new T.Group();pack.position.set(0,1.32,-.39);puppet.add(pack);ball(pack,coral,0,0,-.14,.44,.50,.22);ball(pack,coral,0,-.14,-.32,.31,.23,.105);
 for(const x of [-.22,.22]){ball(pack,strap,x,-.10,-.414,.045,.28,.015);const buckle=new T.Mesh(new T.TorusGeometry(.059,.016,6,16),gold);buckle.position.set(x,-.10,-.438);pack.add(buckle);curve(torso,[[x,.43,-.25],[x,.48,.11],[x,.28,.4],[x,-.30,.4]],coral,.035);}
 const roll=new T.Mesh(new T.CylinderGeometry(.12,.12,.66,20),coral);roll.rotation.z=Math.PI/2;roll.position.set(0,.43,-.18);pack.add(roll);for(const x of [-.335,.335]){const spiral=new T.Mesh(new T.TorusGeometry(.073,.015,7,20),strap);spiral.rotation.y=Math.PI/2;spiral.position.set(x,.43,-.18);pack.add(spiral);}
 const head=new T.Group();head.position.set(0,2.04,.08);head.scale.setScalar(1.10);puppet.add(head);
 ball(head,wool,0,.04,0,.57,.58,.47);fleece(head,110,.54,.54,.43,.14,(x,y,z)=>z<.40||y>.55||Math.abs(x)>.8);
 ball(head,skin,0,-.03,.32,.46,.425,.30);
 const ears=[];for(const side of [-1,1]){const ear=new T.Group();ear.position.set(side*.50,.03,.01);ear.rotation.z=side*-.24;head.add(ear);ball(ear,wool,side*.19,0,0,.32,.14,.13);ball(ear,pink,side*.20,-.007,.104,.235,.087,.027);ears.push(ear);}
 // Sculpted forehead curls stay above the brows, leaving the face open.
 for(let i=-2;i<=2;i++){ball(head,wool,i*.17,.43+(.09*(1-Math.abs(i)/2)),.31,.155,.17,.15);ball(head,wool,i*.15,.58,.12,.155,.16,.16);}
 const eyes=[];for(const side of [-1,1]){const eye=new T.Group();eye.position.set(side*.205,.065,.565);eye.rotation.y=side*.17;head.add(eye);ball(eye,white,0,0,0,.142,.168,.080);ball(eye,iris,-side*.01,-.005,.062,.097,.121,.047);ball(eye,black,-side*.013,-.005,.096,.060,.090,.025);ball(eye,white,-.027,.045,.116,.025,.030,.013);ball(eye,white,.030,-.035,.121,.010,.013,.006);eyes.push(eye);curve(head,[[side*.31,.265,.545],[side*.22,.30,.574],[side*.12,.276,.555]],hoof,.019);}
 ball(head,skin,0,-.185,.558,.24,.15,.13);ball(head,pink,0,-.13,.697,.073,.044,.033);
 curve(head,[[0,-.16,.697],[0,-.215,.693]],hoof,.012);curve(head,[[-.14,-.22,.661],[-.07,-.267,.692],[0,-.273,.702],[.07,-.259,.692],[.14,-.215,.661]],hoof,.012);
 const tail=ball(puppet,wool,0,1.11,-.55,.18,.18,.18);
 const ground=new T.Mesh(new T.PlaneGeometry(6,6),new T.ShadowMaterial({opacity:.22}));ground.rotation.x=-Math.PI/2;ground.position.y=.025;ground.receiveShadow=true;scene.add(ground);
 const shadowCanvas=document.createElement('canvas');shadowCanvas.width=shadowCanvas.height=64;const brush=shadowCanvas.getContext('2d');const shade=brush.createRadialGradient(32,32,3,32,32,31);shade.addColorStop(0,'rgba(44,24,38,.38)');shade.addColorStop(1,'rgba(44,24,38,0)');brush.fillStyle=shade;brush.fillRect(0,0,64,64);const shadowTexture=new T.CanvasTexture(shadowCanvas);const contact=new T.Mesh(new T.PlaneGeometry(1.25,.85),new T.MeshBasicMaterial({map:shadowTexture,transparent:true,depthWrite:false}));contact.rotation.x=-Math.PI/2;contact.position.set(0,.034,.03);scene.add(contact);
 let targetX=0,targetY=0,lookX=0,lookY=0,width=0,height=0,hopStart=-5000;
 const api={hopEnd:0,draw(now,paused){const rect=el.getBoundingClientRect();if(rect.width<1||rect.height<1)return;if(width!==rect.width||height!==rect.height){width=rect.width;height=rect.height;renderer.setSize(width,height,false);camera.aspect=width/height;camera.updateProjectionMatrix();}
 const t=now/1000,u=(now-hopStart)/1050,hopping=!paused&&u>=0&&u<1;
 lookX+=(targetX-lookX)*.10;lookY+=(targetY-lookY)*.10;
 puppet.rotation.y=paused?-.70:-.70+Math.sin(t*.8)*.17+(hopping?Math.sin(u*Math.PI*2)*.45:0);
 puppet.position.y=hopping?Math.sin(u*Math.PI)*.38:0;contact.material.opacity=hopping?1-Math.sin(u*Math.PI)*.55:1;torso.scale.y=paused?1:1+Math.sin(t*2)*.015;
 head.rotation.y=paused?.28:.28+lookX*.32+Math.sin(t*.7)*.08;head.rotation.x=paused?-.035:-.035-lookY*.13;head.rotation.z=paused?0:Math.sin(t*.9)*.035;
 arms.forEach((arm,i)=>{arm.rotation.z=(i?1:-1)*(.20+(hopping?Math.sin(u*Math.PI)*.7:0));arm.rotation.x=hopping?-.28:Math.sin(t*1.3+i)*.035;});
 legs.forEach((leg,i)=>{leg.rotation.x=hopping?Math.sin(u*Math.PI)*(i?.25:-.25):0;});ears.forEach((ear,i)=>ear.rotation.z=(i?1:-1)*(-.24+(hopping?Math.sin(u*Math.PI*3)*.13:Math.sin(t*1.2)*.025)));
 const blink=!paused&&t%4.8>4.60?Math.max(.06,Math.abs((t%4.8-4.7)*10)):1;eyes.forEach(eye=>eye.scale.y=blink);tail.rotation.x=t*.3;renderer.render(scene,camera);
 }};
 el.addEventListener('pointermove',e=>{if(still())return;const r=el.getBoundingClientRect();targetX=(e.clientX-r.left)/r.width*2-1;targetY=1-(e.clientY-r.top)/r.height*2;schedule();});el.addEventListener('pointerleave',()=>{targetX=targetY=0;schedule();});
 el.addEventListener('sheep:cheer',()=>{if(still())return;hopStart=performance.now();api.hopEnd=hopStart+1050;schedule();});
 canvas.addEventListener('webglcontextlost',e=>{e.preventDefault();models.delete(el);el.classList.remove('sheep-3d-ready');el.dataset.mascotRenderer='image';canvas.remove();renderer.dispose();});
 if(el.classList.contains('cheer')&&!still()){hopStart=performance.now();api.hopEnd=hopStart+1050;}
 el.dataset.mascotRenderer='webgl';return api;
}
const observer=new IntersectionObserver(entries=>{for(const entry of entries)if(entry.isIntersecting)mount(entry.target);schedule();},{rootMargin:'30px'});sheepNodes.forEach(el=>observer.observe(el));
let queued=false;new MutationObserver(records=>{if(!records.some(r=>r.target===document.body||r.target.classList?.contains('ov')))return;if(queued)return;queued=true;requestAnimationFrame(()=>{queued=false;sheepNodes.forEach(mount);schedule();});}).observe(document.body,{attributes:true,attributeFilter:['class'],subtree:true});
document.addEventListener('visibilitychange',()=>{if(!document.hidden){sheepNodes.forEach(mount);schedule();}});window.addEventListener('resize',schedule);reduced.addEventListener('change',schedule);window.addEventListener('pagehide',()=>{cancelAnimationFrame(frame);frame=0;});
