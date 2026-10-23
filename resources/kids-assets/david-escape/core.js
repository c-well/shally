import {START,platforms,obstacles,checkpoints,guardPositions,JONATHAN,bounds} from './world.js';
export const STEP=1/60;
const R=.28, HEIGHT=1.65, GRAVITY=21, JUMP=8.7;
const colliders=[...platforms,...obstacles];
const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));
const copy=p=>({...p,hang:p.hang?{...p.hang}:null,climb:p.climb?{...p.climb}:null});
export function createState(checkpoint=0){
 const c=checkpoints[clamp(Number.isInteger(checkpoint)?checkpoint:0,0,2)];
 return {p:{x:c.x,y:c.y,z:c.z,vx:0,vy:0,vz:0,face:Math.PI,climb:null,ground:true,coyote:.12,jumpBuffer:0,hang:null,crouch:false},
 time:0,checkpoint:c.id,alarm:0,history:[],failure:null,completed:false,rewinding:false,events:[],jumpWas:false,climbWas:false,stumbles:0};
}
export function restart(s){const n=createState(s.checkpoint);n.stumbles=s.stumbles;Object.assign(s,n);}
function horizontalOverlap(p,b,pad=R){const q=bounds(b);return p.x+pad>q.minX&&p.x-pad<q.maxX&&p.z+pad>q.minZ&&p.z-pad<q.maxZ;}
function moveAxis(p,axis,amount){
 p[axis]+=amount;const h=p.crouch?.95:HEIGHT;
 for(const b of colliders){if(p.y>=b.top-.035||p.y+h<=b.bottom+.02||!horizontalOverlap(p,b))continue;
  const q=bounds(b);if(axis==='x'){if(amount>0)p.x=q.minX-R;else if(amount<0)p.x=q.maxX+R;p.vx=0;}
  else {if(amount>0)p.z=q.minZ-R;else if(amount<0)p.z=q.maxZ+R;p.vz=0;}
 }
}
function catchLedge(s,input){
 const p=s.p;if(p.ground||p.vy>1||p.crouch||Math.hypot(input.x,input.z)<.2)return;
 for(const b of platforms){if(p.y>b.top-.22||p.y<b.top-1.55)continue;const q=bounds(b),inX=p.x>q.minX+.15&&p.x<q.maxX-.15,inZ=p.z>q.minZ+.15&&p.z<q.maxZ-.15;
  let target;
  if(inX&&Math.abs(p.z-R-q.maxZ)<.3&&input.z<-.2)target={x:p.x,z:q.maxZ-.5,face:Math.PI};
  if(inX&&Math.abs(p.z+R-q.minZ)<.3&&input.z>.2)target={x:p.x,z:q.minZ+.5,face:0};
  if(inZ&&Math.abs(p.x-R-q.maxX)<.3&&input.x<-.2)target={x:q.maxX-.5,z:p.z,face:-Math.PI/2};
  if(inZ&&Math.abs(p.x+R-q.minX)<.3&&input.x>.2)target={x:q.minX+.5,z:p.z,face:Math.PI/2};
  if(target){p.hang={...target,y:b.top};p.face=target.face;p.y=b.top-1.45;p.vx=p.vy=p.vz=0;s.events.push('ledge');return;}
 }
}
function coverBlocks(g,p){
 for(const b of obstacles){const q=bounds(b),eye=p.y+(p.crouch?.65:1.45);if(b.top<eye)continue;
  // Slab segment intersection in the horizontal plane; cover must lie between eyes.
  let lo=0,hi=1;
  for(const [a,min,max] of [['x',q.minX,q.maxX],['z',q.minZ,q.maxZ]]){const d=p[a]-g[a];if(Math.abs(d)<.0001){if(g[a]<min||g[a]>max){lo=2;break;}}else{const t1=(min-g[a])/d,t2=(max-g[a])/d;lo=Math.max(lo,Math.min(t1,t2));hi=Math.min(hi,Math.max(t1,t2));}}
  if(lo<=hi&&hi>.02&&lo<.98)return true;
 }return false;
}
export function visibleToGuard(p,g){
 const dx=p.x-g.x,dz=p.z-g.z,d=Math.hypot(dx,dz);
 if(d>(p.crouch?4.8:7.4)||d<.001||Math.abs(p.y-g.y)>2)return false;
 if((dx*g.dx+dz*g.dz)/d<.55&&d>1.15)return false;
 return !coverBlocks(g,p);
}
export function tick(s,input={},dt=STEP){
 const a={x:0,z:0,jump:false,climb:false,crouch:false,rewind:false,interact:false,...input};dt=clamp(dt,0,STEP);s.events=[];
 if(a.rewind&&s.history.length>1&&!s.completed){
  const snap=s.history.pop();Object.assign(s,{p:copy(snap.p),time:snap.time,alarm:snap.alarm,failure:null,rewinding:true});s.jumpWas=!!a.jump;s.climbWas=!!a.climb;return;
 }
 s.rewinding=false;if(s.failure||s.completed)return;
 s.history.push({p:copy(s.p),time:s.time,alarm:s.alarm});if(s.history.length>300)s.history.shift();
 s.time+=dt;const p=s.p;
 const jumpPressed=a.jump&&!s.jumpWas,climbPressed=a.climb&&!s.climbWas;s.jumpWas=!!a.jump;s.climbWas=!!a.climb;
 if(p.climb){const c=p.climb;c.t=Math.min(1,c.t+dt/.32);const u=c.t*c.t*(3-2*c.t);p.x=c.x+(c.tx-c.x)*u;p.y=c.y+(c.ty-c.y)*u;p.z=c.z+(c.tz-c.z)*u;if(c.t===1){p.climb=null;p.ground=true;p.coyote=.12;}return;}
 if(p.hang){if(jumpPressed||climbPressed){const h=p.hang;p.climb={x:p.x,y:p.y,z:p.z,tx:h.x,ty:h.y,tz:h.z,t:0};p.hang=null;p.ground=false;s.events.push('climb');}else if(a.crouch){p.hang=null;p.vy=-2;p.y-=.15;}return;}
 p.crouch=!!a.crouch&&p.ground;
 if(jumpPressed)p.jumpBuffer=.15;else p.jumpBuffer=Math.max(0,p.jumpBuffer-dt);
 p.coyote=p.ground?.12:Math.max(0,p.coyote-dt);
 if(p.jumpBuffer>0&&p.coyote>0&&!p.crouch){p.vy=JUMP;p.ground=false;p.coyote=0;p.jumpBuffer=0;s.events.push('jump');}
 if(!a.jump&&p.vy>3.7)p.vy=3.7;
 const mag=Math.hypot(a.x,a.z),speed=p.crouch?2.25:5.6;
 const tx=mag>.05?a.x/Math.max(1,mag)*speed:0,tz=mag>.05?a.z/Math.max(1,mag)*speed:0;
 const smoothing=1-Math.exp(-dt*(p.ground?18:7));p.vx+=(tx-p.vx)*smoothing;p.vz+=(tz-p.vz)*smoothing;
 if(mag>.1)p.face=Math.atan2(a.x,a.z);
 moveAxis(p,'x',p.vx*dt);moveAxis(p,'z',p.vz*dt);
 const oldY=p.y;p.vy-=GRAVITY*dt;p.y+=p.vy*dt;const wasGround=p.ground;p.ground=false;
 for(const b of colliders){if(!horizontalOverlap(p,b))continue;
  if(p.vy<=0&&oldY>=b.top-.06&&p.y<=b.top){p.y=b.top;p.vy=0;p.ground=true;if(!wasGround)s.events.push('land');}
  else if(p.vy>0&&oldY+HEIGHT<=b.bottom&&p.y+HEIGHT>b.bottom){p.y=b.bottom-HEIGHT;p.vy=0;}
 }
 catchLedge(s,a);
 if(p.y<-4){s.failure='fall';s.stumbles++;s.events.push('fall');return;}
 const seen=s.checkpoint===1&&guardPositions(s.time).some(g=>visibleToGuard(p,g));
 s.alarm=clamp(s.alarm+(seen?dt*1.05:-dt*1.9),0,1);
 if(s.alarm>=1){s.failure='caught';s.stumbles++;s.events.push('caught');return;}
 if(p.ground&&s.checkpoint===0&&p.z<=-33&&p.z>=-37&&Math.abs(p.x)<4){s.checkpoint=1;s.history=[];s.alarm=0;s.events.push('checkpoint');}
 if(p.ground&&s.checkpoint===1&&p.z<=-77){s.checkpoint=2;s.history=[];s.alarm=0;s.events.push('checkpoint');}
 if(s.checkpoint===2&&a.interact&&Math.hypot(p.x-JONATHAN.x,p.z-JONATHAN.z)<2.6){s.completed=true;s.events.push('complete');}
}
