import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const source=fs.readFileSync(new URL('../resources/views/kids/tetris.blade.php',import.meta.url),'utf8');
const script=source.split('<script>').at(-1).split('</script>')[0];
class Element {
 constructor(){this.children=[];this.textContent='';this.style={};this.dataset={};this.classes=new Set();this.classList={add:(x)=>this.classes.add(x),remove:(x)=>this.classes.delete(x),contains:(x)=>this.classes.has(x),toggle:(x,on)=>on?this.classes.add(x):this.classes.delete(x)};}
 append(...xs){this.children.push(...xs)} appendChild(x){this.append(x)} replaceChildren(...xs){this.children=xs} addEventListener(){} setAttribute(k,v){this[k]=v} focus(){} get innerHTML(){return ''} set innerHTML(v){this.children=[]}
}
const elements=new Map(),timers=[],moves=[];
const get=id=>{if(!elements.has(id))elements.set(id,new Element());return elements.get(id)};
const context=vm.createContext({console,Math,LEVEL:{reference:'John 3:16',verse:'For God so loved the world that he gave his only begotten Son'},MISSION:{notes:['Love','Giving','Trust']},versePhrases:['For God so loved','the world that he','gave his only begotten','Son'],document:{getElementById:get,querySelector:get,createElement:()=>new Element(),createTextNode:x=>({textContent:x}),addEventListener(){}},GameSession:{move:async e=>{moves.push(e)},ready:()=>new Promise(()=>{})},KidsMascot:{cheer(){}},GameAudio:{play(){},toggle(){return false}},setTimeout:(fn)=>{timers.push(fn);return timers.length},clearTimeout(){}});
vm.runInContext(script,context);
vm.runInContext('startGame();',context);
// A full seven-piece bag always includes every shape once.
assert.equal(new Set(vm.runInContext('pieceBag=[];Array.from({length:7},()=>takeType())',context)).size,7);
// Crossing the former fifth-piece trigger keeps gameplay running.
vm.runInContext('pieceCount=4;afterLock();',context);
assert.equal(vm.runInContext('pieceCount',context),5);
assert.equal(vm.runInContext('paused',context),false);
assert.equal(elements.has('qOv'),false);
// Clear a real filled row through the lock/save/animation path.
async function clearRow(){
 timers.length=0;
 await vm.runInContext('board=newBoard();board[17]=[0,0,0,0,1,1,1,1,1,1];cur={m:[[1,1,1,1]],r:17,x:0,c:1};lockPiece();',context);
 const settle=timers.shift();assert.ok(settle);settle();
}
await clearRow();
assert.equal(vm.runInContext('lines',context),1);
assert.equal(vm.runInContext('revealed',context),4);
assert.equal(vm.runInContext('tokens',context),0);
assert.equal(get('verse').children[0].textContent,'For God so loved ');
await clearRow();
assert.equal(vm.runInContext('tokens',context),1);
assert.equal(vm.runInContext('revealed',context),8);
assert.equal(get('missionNote').textContent,'Giving');
await clearRow();await clearRow();
assert.equal(vm.runInContext('over',context),true);
assert.equal(vm.runInContext('revealed',context),13);
while(timers.length)timers.shift()();
assert.equal(get('winOv').classList.contains('show'),true);
assert.equal(moves.length,4);
assert.ok(moves.every(e=>e.type==='piece'&&e.lines===1));
assert.equal(get('verseProgress').textContent,'4 of 4 phrases · John 3:16');
console.log('PASS: fair piece bag, uninterrupted fifth piece, saved line clears, phrase reveals, meaning notes, power-ups, and verse completion.');
