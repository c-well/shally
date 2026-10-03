import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const source=fs.readFileSync(new URL('../resources/views/kids/tetris.blade.php',import.meta.url),'utf8');
const script=source.split('<script>').at(-1).split('</script>')[0];
class Element{
 constructor(){this.children=[];this.textContent='';this.style={};this.dataset={};this.classes=new Set();this.classList={add:x=>this.classes.add(x),remove:x=>this.classes.delete(x),contains:x=>this.classes.has(x),toggle:(x,on)=>on?this.classes.add(x):this.classes.delete(x)};}
 append(...xs){this.children.push(...xs)}appendChild(x){this.append(x)}replaceChildren(...xs){this.children=xs}addEventListener(){}setAttribute(k,v){this[k]=v}focus(){}get innerHTML(){return ''}set innerHTML(v){this.children=[]}
}
const steps=[{word_index:0,letters:'God',finishes_word:true},{word_index:1,letters:'love',finishes_word:false},{word_index:1,letters:'s',finishes_word:true},{word_index:2,letters:'us',finishes_word:true}];
function setup(){const elements=new Map(),timers=[],moves=[];let score=0;const get=id=>{if(!elements.has(id))elements.set(id,new Element());return elements.get(id)};const context=vm.createContext({console,Math,LEVEL:{reference:'1 John 4:8',verse:'God loves us.'},MISSION:{notes:['Love','Giving','Trust']},versePhrases:[],letterSteps:steps,initialState:{},document:{getElementById:get,querySelector:get,createElement:()=>new Element(),createTextNode:x=>({textContent:x}),addEventListener(){}},GameSession:{move:async e=>{moves.push(e);score+=[0,40,100,300,1200][e.lines];return{state:{progress:moves.length,words_built:steps.slice(0,moves.length).filter(s=>s.finishes_word).length,score,completed:moves.length===steps.length}};},ready:()=>new Promise(()=>{})},KidsMascot:{cheer(){}},GameAudio:{play(){},toggle(){return false}},setTimeout:fn=>{timers.push(fn);return timers.length},clearTimeout(){}});vm.runInContext(script,context);vm.runInContext('startGame()',context);return{context,get,timers,moves,run:s=>vm.runInContext(s,context)}}
const g=setup();
assert.equal(g.get('verse').children.map(x=>x.textContent).join('').trim(),'God loves us.');
assert.equal(g.get('verse').children[0].className,'vw current');
assert.equal(g.run("cur.letters.join('')"),'GOD');
assert.equal(new Set(g.run('pieceBag=[];Array.from({length:7},()=>takeType())')).size,7);
g.run('pieceCount=4;afterLock()');assert.equal(g.run('pieceCount'),5);assert.equal(g.run('paused'),false);
// Rotate numbered squares without changing which letter belongs to each square.
assert.equal(g.run("let m=pieceMatrix(SHAPES.T.m),before=JSON.stringify(m);for(let i=0;i<4;i++)m=rotate(m);JSON.stringify(m)===before"),true);
async function land(g,clear=false){g.timers.length=0;await g.run(`board=newBoard();letterBoard=Array.from({length:ROWS},()=>Array(COLS).fill(''));${clear?'board[17]=[0,0,0,0,1,1,1,1,1,1];':''}cur={m:[[1,2,3,4]],letters:Array.from(letterSteps[builtSteps].letters.toUpperCase()),r:17,x:0,c:1};lockPiece();`);if(clear)g.timers.shift()();}
await land(g);assert.equal(g.run('lines'),0);assert.equal(g.run('revealed'),1);assert.equal(g.run("letterBoard[17].join('')"),'GOD');assert.equal(g.get('verse').children[0].className,'vw got');assert.equal(g.run("cur.letters.join('')"),'LOVE');
await land(g);assert.equal(g.run('revealed'),1);assert.equal(g.get('letterSlots').children.filter(el=>el.className==='letter built').length,4);assert.equal(g.run("cur.letters.join('')"),'S');
// A fresh board retains the unfinished word and server progress.
g.run('resetBoard()');assert.equal(g.run('builtSteps'),2);assert.equal(g.run('revealed'),1);assert.equal(g.run("cur.letters.join('')"),'S');
await land(g);assert.equal(g.run('revealed'),2);await land(g);assert.equal(g.run('over'),true);while(g.timers.length)g.timers.shift()();assert.equal(g.get('winOv').classList.contains('show'),true);assert.equal(g.get('verseProgress').textContent,'3 of 3 words built');
const scoring=setup();await land(scoring,true);await land(scoring,true);assert.equal(scoring.run('lines'),2);assert.equal(scoring.run('score'),80);assert.equal(scoring.run('tokens'),1);assert.equal(scoring.run('revealed'),1);
console.log('PASS: verse visible immediately, letters attached to blocks, first-piece learning, long words, rotation, fresh-board continuation, scoring, swaps, and completion.');
