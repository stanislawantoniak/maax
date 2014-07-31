//>>built
define("dojox/gfx/_base",["dojo/_base/kernel","dojo/_base/lang","dojo/_base/Color","dojo/_base/sniff","dojo/_base/window","dojo/_base/array","dojo/dom","dojo/dom-construct","dojo/dom-geometry"],function(_1,_2,_3,_4,_5,_6,_7,_8,_9){
var g=_2.getObject("dojox.gfx",true),b=g._base={};
g._hasClass=function(_a,_b){
var _c=_a.getAttribute("className");
return _c&&(" "+_c+" ").indexOf(" "+_b+" ")>=0;
};
g._addClass=function(_d,_e){
var _f=_d.getAttribute("className")||"";
if(!_f||(" "+_f+" ").indexOf(" "+_e+" ")<0){
_d.setAttribute("className",_f+(_f?" ":"")+_e);
}
};
g._removeClass=function(_10,_11){
var cls=_10.getAttribute("className");
if(cls){
_10.setAttribute("className",cls.replace(new RegExp("(^|\\s+)"+_11+"(\\s+|$)"),"$1$2"));
}
};
b._getFontMeasurements=function(){
var _12={"1em":0,"1ex":0,"100%":0,"12pt":0,"16px":0,"xx-small":0,"x-small":0,"small":0,"medium":0,"large":0,"x-large":0,"xx-large":0};
var p;
if(_4("ie")){
_5.doc.documentElement.style.fontSize="100%";
}
var div=_8.create("div",{style:{position:"absolute",left:"0",top:"-100px",width:"30px",height:"1000em",borderWidth:"0",margin:"0",padding:"0",outline:"none",lineHeight:"1",overflow:"hidden"}},_5.body());
for(p in _12){
div.style.fontSize=p;
_12[p]=Math.round(div.offsetHeight*12/16)*16/12/1000;
}
_5.body().removeChild(div);
return _12;
};
var _13=null;
b._getCachedFontMeasurements=function(_14){
if(_14||!_13){
_13=b._getFontMeasurements();
}
return _13;
};
var _15=null,_16={};
b._getTextBox=function(_17,_18,_19){
var m,s,al=arguments.length;
var i,box;
if(!_15){
_15=_8.create("div",{style:{position:"absolute",top:"-10000px",left:"0",visibility:"hidden"}},_5.body());
}
m=_15;
m.className="";
s=m.style;
s.borderWidth="0";
s.margin="0";
s.padding="0";
s.outline="0";
if(al>1&&_18){
for(i in _18){
if(i in _16){
continue;
}
s[i]=_18[i];
}
}
if(al>2&&_19){
m.className=_19;
}
m.innerHTML=_17;
if(m.getBoundingClientRect){
var bcr=m.getBoundingClientRect();
box={l:bcr.left,t:bcr.top,w:bcr.width||(bcr.right-bcr.left),h:bcr.height||(bcr.bottom-bcr.top)};
}else{
box=_9.getMarginBox(m);
}
m.innerHTML="";
return box;
};
b._computeTextLocation=function(_1a,_1b,_1c,_1d){
var loc={},_1e=_1a.align;
switch(_1e){
case "end":
loc.x=_1a.x-_1b;
break;
case "middle":
loc.x=_1a.x-_1b/2;
break;
default:
loc.x=_1a.x;
break;
}
var c=_1d?0.75:1;
loc.y=_1a.y-_1c*c;
return loc;
};
b._computeTextBoundingBox=function(s){
if(!g._base._isRendered(s)){
return {x:0,y:0,width:0,height:0};
}
var loc,_1f=s.getShape(),_20=s.getFont()||g.defaultFont,w=s.getTextWidth(),h=g.normalizedLength(_20.size);
loc=b._computeTextLocation(_1f,w,h,true);
return {x:loc.x,y:loc.y,width:w,height:h};
};
b._isRendered=function(s){
var p=s.parent;
while(p&&p.getParent){
p=p.parent;
}
return p!==null;
};
var _21=0;
b._getUniqueId=function(){
var id;
do{
id=_1._scopeName+"xUnique"+(++_21);
}while(_7.byId(id));
return id;
};
b._fixMsTouchAction=function(_22){
var r=_22.rawNode;
if(typeof r.style.msTouchAction!="undefined"){
r.style.msTouchAction="none";
}
};
_2.mixin(g,{defaultPath:{type:"path",path:""},defaultPolyline:{type:"polyline",points:[]},defaultRect:{type:"rect",x:0,y:0,width:100,height:100,r:0},defaultEllipse:{type:"ellipse",cx:0,cy:0,rx:200,ry:100},defaultCircle:{type:"circle",cx:0,cy:0,r:100},defaultLine:{type:"line",x1:0,y1:0,x2:100,y2:100},defaultImage:{type:"image",x:0,y:0,width:0,height:0,src:""},defaultText:{type:"text",x:0,y:0,text:"",align:"start",decoration:"none",rotated:false,kerning:true},defaultTextPath:{type:"textpath",text:"",align:"start",decoration:"none",rotated:false,kerning:true},defaultStroke:{type:"stroke",color:"black",style:"solid",width:1,cap:"butt",join:4},defaultLinearGradient:{type:"linear",x1:0,y1:0,x2:100,y2:100,colors:[{offset:0,color:"black"},{offset:1,color:"white"}]},defaultRadialGradient:{type:"radial",cx:0,cy:0,r:100,colors:[{offset:0,color:"black"},{offset:1,color:"white"}]},defaultPattern:{type:"pattern",x:0,y:0,width:0,height:0,src:""},defaultFont:{type:"font",style:"normal",variant:"normal",weight:"normal",size:"10pt",family:"serif"},getDefault:(function(){
var _23={};
return function(_24){
var t=_23[_24];
if(t){
return new t();
}
t=_23[_24]=new Function();
t.prototype=g["default"+_24];
return new t();
};
})(),normalizeColor:function(_25){
return (_25 instanceof _3)?_25:new _3(_25);
},normalizeParameters:function(_26,_27){
var x;
if(_27){
var _28={};
for(x in _26){
if(x in _27&&!(x in _28)){
_26[x]=_27[x];
}
}
}
return _26;
},makeParameters:function(_29,_2a){
var i=null;
if(!_2a){
return _2.delegate(_29);
}
var _2b={};
for(i in _29){
if(!(i in _2b)){
_2b[i]=_2.clone((i in _2a)?_2a[i]:_29[i]);
}
}
return _2b;
},formatNumber:function(x,_2c){
var val=x.toString();
if(val.indexOf("e")>=0){
val=x.toFixed(4);
}else{
var _2d=val.indexOf(".");
if(_2d>=0&&val.length-_2d>5){
val=x.toFixed(4);
}
}
if(x<0){
return val;
}
return _2c?" "+val:val;
},makeFontString:function(_2e){
return _2e.style+" "+_2e.variant+" "+_2e.weight+" "+_2e.size+" "+_2e.family;
},splitFontString:function(str){
var _2f=g.getDefault("Font");
var t=str.split(/\s+/);
do{
if(t.length<5){
break;
}
_2f.style=t[0];
_2f.variant=t[1];
_2f.weight=t[2];
var i=t[3].indexOf("/");
_2f.size=i<0?t[3]:t[3].substring(0,i);
var j=4;
if(i<0){
if(t[4]=="/"){
j=6;
}else{
if(t[4].charAt(0)=="/"){
j=5;
}
}
}
if(j<t.length){
_2f.family=t.slice(j).join(" ");
}
}while(false);
return _2f;
},cm_in_pt:72/2.54,mm_in_pt:7.2/2.54,px_in_pt:function(){
return g._base._getCachedFontMeasurements()["12pt"]/12;
},pt2px:function(len){
return len*g.px_in_pt();
},px2pt:function(len){
return len/g.px_in_pt();
},normalizedLength:function(len){
if(len.length===0){
return 0;
}
if(len.length>2){
var _30=g.px_in_pt();
var val=parseFloat(len);
switch(len.slice(-2)){
case "px":
return val;
case "pt":
return val*_30;
case "in":
return val*72*_30;
case "pc":
return val*12*_30;
case "mm":
return val*g.mm_in_pt*_30;
case "cm":
return val*g.cm_in_pt*_30;
}
}
return parseFloat(len);
},pathVmlRegExp:/([A-Za-z]+)|(\d+(\.\d+)?)|(\.\d+)|(-\d+(\.\d+)?)|(-\.\d+)/g,pathSvgRegExp:/([A-DF-Za-df-z])|([-+]?\d*[.]?\d+(?:[eE][-+]?\d+)?)/g,equalSources:function(a,b){
return a&&b&&a===b;
},switchTo:function(_31){
var ns=typeof _31=="string"?g[_31]:_31;
if(ns){
_6.forEach(["Group","Rect","Ellipse","Circle","Line","Polyline","Image","Text","Path","TextPath","Surface","createSurface","fixTarget"],function(_32){
g[_32]=ns[_32];
});
if(typeof _31=="string"){
g.renderer=_31;
}else{
_6.some(["svg","vml","canvas","canvasWithEvents","silverlight"],function(r){
return (g.renderer=g[r]&&g[r].Surface===g.Surface?r:null);
});
}
}
}});
return g;
});
