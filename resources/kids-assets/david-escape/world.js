// One authored mission, independent of rendering and input devices.
export const VERSION = 1;
export const START = {x:0,y:5,z:3};
export const platforms = [
 {id:'window',x:0,z:0,w:6,d:10,top:5,bottom:-3,kind:'roof'},
 {id:'roof1',x:0,z:-10,w:5,d:5,top:5,bottom:-3,kind:'roof'},
 {id:'high-ledge',x:2,z:-18,w:5,d:6,top:6.8,bottom:-3,kind:'roof'},
 {id:'roof3',x:2,z:-26,w:5,d:6,top:6.8,bottom:-3,kind:'roof'},
 {id:'terrace',x:0,z:-34,w:9,d:6,top:5,bottom:-3,kind:'roof'},
 {id:'stair1',x:0,z:-39,w:7,d:4,top:4,bottom:-3,kind:'stone'},
 {id:'stair2',x:0,z:-43,w:7,d:4,top:3,bottom:-3,kind:'stone'},
 {id:'courtyard',x:0,z:-60,w:18,d:30,top:1.5,bottom:-3,kind:'stone'},
 {id:'field',x:0,z:-110,w:60,d:70,top:1.5,bottom:-3,kind:'grass'},
];
export const obstacles = [
 {id:'cover1',x:0,z:-50,w:3.2,d:1.5,top:2.85,bottom:1.5,kind:'crate'},
 {id:'cover2',x:-5,z:-57,w:2,d:2,top:3.5,bottom:1.5,kind:'crate'},
 {id:'cover3',x:2,z:-62,w:3,d:2,top:3.9,bottom:1.5,kind:'stone'},
 {id:'cover4',x:-3,z:-69,w:3,d:1.5,top:2.85,bottom:1.5,kind:'crate'},
 {id:'west-wall',x:-9.4,z:-60,w:.8,d:30,top:6,bottom:1.5,kind:'wall'},
 {id:'east-wall',x:9.4,z:-60,w:.8,d:30,top:6,bottom:1.5,kind:'wall'},
 {id:'gate-left',x:-4.2,z:-74,w:4.4,d:1.3,top:5.5,bottom:1.5,kind:'wall'},
 {id:'gate-right',x:4.2,z:-74,w:4.4,d:1.3,top:5.5,bottom:1.5,kind:'wall'},
 {id:'field-stone',x:-3,z:-94,w:2.5,d:2.5,top:3.7,bottom:1.5,kind:'rock'},
];
export const checkpoints = [
 {id:0,x:0,y:5,z:3,title:'Michal’s window',goal:'Cross the rooftops. Follow the lanterns.',ref:'1 Samuel 19:11–12',story:'Michal warned David and helped him escape through a window.'},
 {id:1,x:0,y:5,z:-34,title:'Beyond the rooftops',goal:'Stay out of the guards’ sight. Reach the gate.',ref:'1 Samuel 19:12',story:'David escaped Saul’s men. Use cover to pass this imagined courtyard.'},
 {id:2,x:0,y:1.5,z:-78,title:'Later, in the field',goal:'Find Jonathan near the olive tree.',ref:'1 Samuel 20:35–42',story:'Later, Jonathan’s arrow signal warned David that it was not safe to return. Their friendship held firm.'},
];
export const JONATHAN = {x:4,y:1.5,z:-109};
export function guardPositions(time) {
 return [0,1].map((i)=>{
  const phase=(time*.19+i*2.4)%(Math.PI*2);
  return {x:Math.sin(phase)*5.7,z:-54-i*11,y:1.5,dx:Math.cos(phase)>=0?1:-1,dz:0};
 });
}
export const bounds = b => ({minX:b.x-b.w/2,maxX:b.x+b.w/2,minZ:b.z-b.d/2,maxZ:b.z+b.d/2});
