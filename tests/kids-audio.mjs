import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const source=fs.readFileSync(new URL('../resources/views/kids/audio.blade.php',import.meta.url),'utf8').split('<script>')[1].split('</script>')[0];
const windows=[{days:[6],from:'09:15',until:'10:50'},{days:[6],from:'10:50',until:'14:30'},{days:[1,2,3,4,5],from:'04:45',until:'06:15'},{days:[3],from:'18:45',until:'20:15'}];
function setup(iso,saved='on',schedule=windows){
 let now=Date.parse(iso),tones=0,stops=0;const storage=new Map([['cop_game_sound',saved]]),timers=[],events={};
 const button={textContent:'',setAttribute(k,v){this[k]=v}},notice={hidden:true,textContent:''};
 class Clock extends Date{static now(){return now}}
 class Audio{currentTime=0;state='running';destination={};createOscillator(){return{frequency:{},connect(){},start(){tones++},stop(){stops++}}}createGain(){return{gain:{setValueAtTime(){},linearRampToValueAtTime(){},exponentialRampToValueAtTime(){}},connect(){}}}}
 const ctx=vm.createContext({Date:Clock,Intl,window:{AudioContext:Audio},document:{getElementById:()=>button,querySelectorAll:()=>[notice],addEventListener:(name,fn)=>events[name]=fn},localStorage:{getItem:key=>storage.get(key),setItem:(key,value)=>storage.set(key,value)},setInterval:fn=>timers.push(fn)});
 vm.runInContext(source.replace('@json($churchAudio)',JSON.stringify({serverNow:now,timezone:'America/New_York',windows:schedule})),ctx);
 return{button,notice,storage,run:s=>vm.runInContext(s,ctx),advance:iso=>now=Date.parse(iso),tick:()=>timers[0](),tones:()=>tones,stops:()=>stops};
}
// Saved sound-on must be overridden across all published service windows and DST.
for(const date of ['2026-10-03T13:15:00Z','2026-10-03T14:50:00Z','2026-01-03T14:15:00Z','2026-10-05T08:45:00Z','2026-10-07T22:45:00Z']){
 const g=setup(date);assert.equal(g.button.textContent,'Sound off');assert.equal(g.notice.hidden,false);g.run("GameAudio.play('win')");assert.equal(g.tones(),0);assert.equal(g.storage.get('cop_game_sound'),'on');
}
const outside=setup('2026-10-03T13:14:59Z');assert.equal(outside.button.textContent,'Sound on');assert.equal(outside.notice.hidden,true);outside.run("GameAudio.play('win')");assert.equal(outside.tones(),4);
outside.advance('2026-10-03T13:15:00Z');outside.run("GameAudio.play('match')");assert.equal(outside.tones(),4);assert.equal(outside.stops(),8);assert.equal(outside.button.textContent,'Sound off');
// Explicit headphone choice works during a service without persisting across pages.
assert.equal(outside.run('GameAudio.toggle()'),true);assert.equal(outside.tones(),6);assert.match(outside.notice.textContent,/sound is on/);
assert.equal(outside.run('GameAudio.toggle()'),false);assert.equal(outside.storage.get('cop_game_sound'),'on');
outside.advance('2026-10-03T18:30:00Z');outside.tick();assert.equal(outside.notice.hidden,true);assert.equal(outside.button.textContent,'Sound off');
const muted=setup('2026-10-03T15:00:00Z','off');muted.run('GameAudio.toggle()');assert.equal(muted.storage.get('cop_game_sound'),'off');
const night=setup('2026-10-04T04:15:00Z','on',[{days:[6],from:'23:00',until:'01:00'}]);assert.equal(night.button.textContent,'Sound off');
const noServices=setup('2026-10-03T15:00:00Z','on',[]);assert.equal(noServices.notice.hidden,true);assert.equal(noServices.button.textContent,'Sound on');
console.log('PASS: service windows, winter/summer time, saved sound override, boundary muting, queued cue cancellation, headphone opt-in, no automatic unmute, overnight windows, and empty schedule.');
