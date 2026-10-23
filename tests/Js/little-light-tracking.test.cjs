const {test} = require('node:test');
const assert = require('node:assert/strict');
const {randomUUID} = require('node:crypto');
const sandbox={module:{exports:{}},URLSearchParams};
require('node:vm').runInNewContext(require('node:fs').readFileSync(require('node:path').join(__dirname,'../../resources/kids-assets/little-light/tracking.js'),'utf8'),sandbox);
const {createTracker}=sandbox.module.exports;
const turn = () => new Promise(r => setImmediate(r));
function setup({blocked=false}={}) {
  let now=0, hidden=false, fail=false;
  const calls=[], values=new Map();
  const tracker=createTracker({crypto:{randomUUID}, now:()=>now, hidden:()=>hidden, search:'?preview=1',
    storage:{getItem:k=>{if(blocked)throw Error();return values.get(k)},setItem:(k,v)=>values.set(k,v)},
    fetch:async(url,options={})=>{if(fail)throw Error('offline');calls.push({url,data:options.body&&JSON.parse(options.body)});return {ok:true,status:200,json:async()=>({csrf:'test-csrf'})}}});
  return {tracker,calls,setFail:v=>fail=v,setHidden:v=>hidden=v,step:(seconds,data={status:'playing',furthest_percent:30},input=true)=>{for(let i=0;i<seconds*10;i++){now+=100;tracker.sample(.1,data,input)}},now:()=>now};
}
test('opening a page is not a play; active, idle, paused and hidden timing differ', async()=>{
  const x=setup();assert.equal(x.calls.length,0);
  x.tracker.begin(1);await turn();assert.equal(x.calls.filter(c=>c.url.endsWith('/start')).length,1);
  x.step(12);await x.tracker.flush();
  let d=x.calls.at(-1).data;assert.ok(d.active_seconds>=11&&d.active_seconds<=12);
  x.step(20,{status:'playing',furthest_percent:30},false);await x.tracker.flush();
  d=x.calls.at(-1).data;assert.ok(d.active_seconds<=17);const before=d.active_seconds;
  x.step(20,{status:'paused',furthest_percent:30});await turn();await x.tracker.flush();assert.equal(x.calls.at(-1).data.active_seconds,before);
  x.setHidden(true);x.step(10);await turn();await x.tracker.flush();assert.equal(x.calls.at(-1).data.active_seconds,before);
});
test('chapter switch starts a new attempt, resume does not; progress never goes backwards',async()=>{
  const x=setup();x.tracker.begin(1);await turn();x.step(11);x.tracker.stumble();await x.tracker.flush();
  x.tracker.begin(1);await turn();assert.equal(x.calls.filter(c=>c.url.endsWith('/start')).length,1);
  x.step(1,{status:'playing',furthest_percent:2});await x.tracker.flush();assert.equal(x.calls.at(-1).data.furthest_percent,30);assert.equal(x.calls.at(-1).data.stumbles,1);
  x.tracker.end();x.tracker.begin(2);await turn();await x.tracker.flush();
  const starts=x.calls.filter(c=>c.url.endsWith('/start'));assert.equal(starts.length,2);assert.notEqual(starts[0].data.run_token,starts[1].data.run_token);assert.equal(starts[0].data.browser_token,starts[1].data.browser_token);assert.equal(starts[1].data.preview,true);
});
test('network and storage failures do not break play; cumulative progress retries',async()=>{
  const x=setup({blocked:true});x.setFail(true);x.tracker.begin(3);await turn();x.step(15,{status:'playing',furthest_percent:70,scrolls:2});x.setFail(false);await x.tracker.flush();
  const d=x.calls.at(-1).data;assert.equal(d.furthest_percent,70);assert.equal(d.scrolls,2);assert.ok(d.active_seconds>=14);
});
test('completion and blur send a final summary without losing discoveries',async()=>{
  const x=setup();x.tracker.begin(3);await turn();x.step(12,{status:'playing',furthest_percent:98,scrolls:3});x.tracker.sample(0,{status:'complete',furthest_percent:98,scrolls:3});await turn();
  assert.equal(x.calls.at(-1).data.status,'complete');x.tracker.suspend();await turn();assert.equal(x.calls.at(-1).data.status,'complete');
});
