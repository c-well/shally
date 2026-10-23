import {test} from 'node:test';
import assert from 'node:assert/strict';
import {createState,tick,restart,STEP,visibleToGuard} from '../../resources/kids-assets/david-escape/core.js';
function walk(s,x,z,{jump=false,limit=360,crouch=false}={}){
 for(let i=0;i<limit;i++){
  const dx=x-s.p.x,dz=z-s.p.z,d=Math.hypot(dx,dz);
  if(d<.23&&s.p.ground)return;
  if(s.p.hang){tick(s,{x:0,z:0,jump:false});tick(s,{jump:true});continue;}
  tick(s,{x:d>.2?dx/d:0,z:d>.2?dz/d:0,jump,crouch});
  if(s.failure)throw Error(`Failed ${s.failure} on route to ${x},${z} at ${JSON.stringify(s.p)}`);
 }
 throw Error(`Did not reach ${x},${z}: ${JSON.stringify(s.p)}`);
}
function rest(s){for(let i=0;i<8;i++)tick(s,{});}
test('authored rooftop jumps and high ledge are reachable with normal controls',()=>{
 const s=createState();
 walk(s,0,-3.8);rest(s);walk(s,0,-10,{jump:true});rest(s);
 walk(s,1,-11.5);rest(s);walk(s,1,-18,{jump:true});rest(s);
 walk(s,2,-20.3);rest(s);walk(s,2,-26,{jump:true});rest(s);
 walk(s,1.2,-28);rest(s);walk(s,0,-34,{jump:true});
 assert.equal(s.checkpoint,1);assert.equal(s.failure,null);assert.ok(s.p.ground);
});
test('rewind restores position and patrol clock, clears a fall, and has a bounded buffer',()=>{
 const s=createState();for(let i=0;i<400;i++)tick(s,{});assert.equal(s.history.length,300);
 for(let i=0;i<100&&!s.failure;i++)tick(s,{x:1});assert.equal(s.failure,'fall');
 for(let i=0;i<100;i++)tick(s,{rewind:true});assert.equal(s.failure,null);assert.ok(s.p.x<3);assert.ok(s.p.y>=5);
});
test('guards respect sight direction, low cover and crouching',()=>{
 const guard={x:-4,y:1.5,z:-50,dx:1,dz:0};
 assert.equal(visibleToGuard({x:-2,y:1.5,z:-50,crouch:false},guard),true);
 assert.equal(visibleToGuard({x:2,y:1.5,z:-50,crouch:true},guard),false);
 assert.equal(visibleToGuard({x:-6,y:1.5,z:-50,crouch:false},guard),false);
 const front={x:0,y:1.5,z:-53,dx:0,dz:1};
 assert.equal(visibleToGuard({x:0,y:1.5,z:-48.5,crouch:true},front),false);
 assert.equal(visibleToGuard({x:0,y:1.5,z:-48.5,crouch:false},front),true);
});
test('checkpoint reset is safe; courtyard route can be crossed using crouch and cover',()=>{
 const s=createState(1);walk(s,0,-47,{crouch:true,limit:650});walk(s,-7.5,-48,{crouch:true});walk(s,-7.5,-72,{crouch:true,limit:1000});walk(s,0,-78,{crouch:true,limit:600});
 assert.equal(s.checkpoint,2);restart(s);assert.equal(s.p.z,-78);assert.equal(s.alarm,0);
 walk(s,4,-108,{limit:700});tick(s,{interact:true});assert.equal(s.completed,true);
});
test('idle and jump release do not create an endless climb or autojump',()=>{
 const s=createState();tick(s,{jump:true});const up=s.p.vy;tick(s,{jump:false});assert.ok(s.p.vy<up);
 for(let i=0;i<100;i++)tick(s,{});assert.equal(s.p.y,5);assert.equal(s.p.ground,true);
});
