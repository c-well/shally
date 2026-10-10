/* Shared input state: keyboard, independent touch pointers, and standard gamepads. */
(function(root){
  'use strict';
  class Controls {
    constructor(){this.keys=new Set();this.pointers=new Map();this.padMove=0;this.padJump=false;this.previous=[];this.padIndex=null;this.jumpBuffer=0;this.mode='keyboard';}
    clear(){this.keys.clear();this.pointers.clear();this.padMove=0;this.padJump=false;this.jumpBuffer=0;}
    press(code){this.mode='keyboard';if(!this.keys.has(code)&&['Space','ArrowUp','KeyW'].includes(code))this.jumpBuffer=.14;this.keys.add(code);}
    release(code){this.keys.delete(code);}
    touch(id,action){this.mode='touch';this.pointers.set(id,action);if(action==='jump')this.jumpBuffer=.14;}
    releaseTouch(id){this.pointers.delete(id);}
    has(action){return [...this.pointers.values()].includes(action);}
    get move(){const digital=Number(this.keys.has('ArrowRight')||this.keys.has('KeyD')||this.has('right'))-Number(this.keys.has('ArrowLeft')||this.keys.has('KeyA')||this.has('left'));return digital||this.padMove;}
    get jumping(){return this.keys.has('Space')||this.keys.has('ArrowUp')||this.keys.has('KeyW')||this.has('jump')||this.padJump;}
    tick(dt){this.jumpBuffer=Math.max(0,this.jumpBuffer-dt);}
    consumeJump(){this.jumpBuffer=0;}
    poll(pads){
      const available=Array.from(pads||[]).filter(p=>p&&p.connected!==false&&p.mapping==='standard');
      const pad=available.find(p=>p.index===this.padIndex)||available[0];
      if(!pad){const disconnected=this.padIndex!==null;this.padIndex=null;this.padMove=0;this.padJump=false;this.previous=[];return {disconnected,connected:false,unsupported:Array.from(pads||[]).some(Boolean)};}
      if(pad.index!==this.padIndex){this.previous=[];this.padIndex=pad.index;}
      const down=i=>Boolean(pad.buttons[i]&&(pad.buttons[i].pressed||pad.buttons[i].value>.5));
      const edge=i=>down(i)&&!this.previous[i];
      const raw=Number.isFinite(pad.axes[0])?pad.axes[0]:0;
      const axis=Math.abs(raw)>.2?Math.sign(raw)*Math.min(1,(Math.abs(raw)-.2)/.8):0;
      this.padMove=Number(down(15))-Number(down(14))||axis;this.padJump=down(0);
      const actions={connected:true,disconnected:false,jump:edge(0),interact:edge(2),pause:edge(9),back:edge(1),journal:edge(3),prev:edge(4),next:edge(5)};
      if(actions.jump){this.jumpBuffer=.14;this.mode='gamepad';}if(this.padMove||Object.values(actions).slice(2).some(Boolean))this.mode='gamepad';
      this.previous=pad.buttons.map((_,i)=>down(i));return actions;
    }
  }
  if(typeof module==='object'&&module.exports)module.exports=Controls;else root.GameControls=Controls;
})(typeof window!=='undefined'?window:globalThis);
