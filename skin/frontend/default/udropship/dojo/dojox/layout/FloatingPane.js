//>>built
require({cache:{"url:dojox/layout/resources/FloatingPane.html":"<div class=\"dojoxFloatingPane\" id=\"${id}\">\n\t<div tabindex=\"0\" role=\"button\" class=\"dojoxFloatingPaneTitle\" dojoAttachPoint=\"focusNode\">\n\t\t<span dojoAttachPoint=\"closeNode\" dojoAttachEvent=\"onclick: close\" class=\"dojoxFloatingCloseIcon\"></span>\n\t\t<span dojoAttachPoint=\"maxNode\" dojoAttachEvent=\"onclick: maximize\" class=\"dojoxFloatingMaximizeIcon\">&thinsp;</span>\n\t\t<span dojoAttachPoint=\"restoreNode\" dojoAttachEvent=\"onclick: _restore\" class=\"dojoxFloatingRestoreIcon\">&thinsp;</span>\t\n\t\t<span dojoAttachPoint=\"dockNode\" dojoAttachEvent=\"onclick: minimize\" class=\"dojoxFloatingMinimizeIcon\">&thinsp;</span>\n\t\t<span dojoAttachPoint=\"titleNode\" class=\"dijitInline dijitTitleNode\"></span>\n\t</div>\n\t<div dojoAttachPoint=\"canvas\" class=\"dojoxFloatingPaneCanvas\">\n\t\t<div dojoAttachPoint=\"containerNode\" role=\"region\" tabindex=\"-1\" class=\"${contentClass}\">\n\t\t</div>\n\t\t<span dojoAttachPoint=\"resizeHandle\" class=\"dojoxFloatingResizeHandle\"></span>\n\t</div>\n</div>\n"}});
define("dojox/layout/FloatingPane",["dojo/_base/kernel","dojo/_base/lang","dojo/_base/window","dojo/_base/declare","dojo/_base/fx","dojo/_base/connect","dojo/_base/array","dojo/_base/sniff","dojo/window","dojo/dom","dojo/dom-class","dojo/dom-geometry","dojo/dom-construct","dijit/_TemplatedMixin","dijit/_Widget","dijit/BackgroundIframe","dojo/dnd/Moveable","./ContentPane","./ResizeHandle","dojo/text!./resources/FloatingPane.html","./Dock"],function(_1,_2,_3,_4,_5,_6,_7,_8,_9,_a,_b,_c,_d,_e,_f,_10,_11,_12,_13,_14,_15){
_1.experimental("dojox.layout.FloatingPane");
var _16=_4("dojox.layout.FloatingPane",[_12,_e],{closable:true,dockable:true,resizable:false,maxable:false,resizeAxis:"xy",title:"",dockTo:"",duration:400,contentClass:"dojoxFloatingPaneContent",_showAnim:null,_hideAnim:null,_dockNode:null,_restoreState:{},_allFPs:[],_startZ:100,templateString:_14,attributeMap:_2.delegate(_f.prototype.attributeMap,{title:{type:"innerHTML",node:"titleNode"}}),postCreate:function(){
this.inherited(arguments);
new _11(this.domNode,{handle:this.focusNode});
if(!this.dockable){
this.dockNode.style.display="none";
}
if(!this.closable){
this.closeNode.style.display="none";
}
if(!this.maxable){
this.maxNode.style.display="none";
this.restoreNode.style.display="none";
}
if(!this.resizable){
this.resizeHandle.style.display="none";
}else{
this.domNode.style.width=_c.getMarginBox(this.domNode).w+"px";
}
this._allFPs.push(this);
this.domNode.style.position="absolute";
this.bgIframe=new _10(this.domNode);
this._naturalState=_c.position(this.domNode);
},startup:function(){
if(this._started){
return;
}
this.inherited(arguments);
if(this.resizable){
if(_8("ie")){
this.canvas.style.overflow="auto";
}else{
this.containerNode.style.overflow="auto";
}
this._resizeHandle=new _13({targetId:this.id,resizeAxis:this.resizeAxis},this.resizeHandle);
}
if(this.dockable){
var _17=this.dockTo;
if(this.dockTo){
this.dockTo=dijit.byId(this.dockTo);
}else{
this.dockTo=dijit.byId("dojoxGlobalFloatingDock");
}
if(!this.dockTo){
var _18,_19;
if(_17){
_18=_17;
_19=_a.byId(_17);
}else{
_19=_d.create("div",null,_3.body());
_b.add(_19,"dojoxFloatingDockDefault");
_18="dojoxGlobalFloatingDock";
}
this.dockTo=new _15({id:_18,autoPosition:"south"},_19);
this.dockTo.startup();
}
if((this.domNode.style.display=="none")||(this.domNode.style.visibility=="hidden")){
this.minimize();
}
}
this.connect(this.focusNode,"onmousedown","bringToTop");
this.connect(this.domNode,"onmousedown","bringToTop");
this.resize(_c.position(this.domNode));
this._started=true;
},setTitle:function(_1a){
_1.deprecated("pane.setTitle","Use pane.set('title', someTitle)","2.0");
this.set("title",_1a);
},close:function(){
if(!this.closable){
return;
}
_6.unsubscribe(this._listener);
this.hide(_2.hitch(this,function(){
this.destroyRecursive();
}));
},hide:function(_1b){
_5.fadeOut({node:this.domNode,duration:this.duration,onEnd:_2.hitch(this,function(){
this.domNode.style.display="none";
this.domNode.style.visibility="hidden";
if(this.dockTo&&this.dockable){
this.dockTo._positionDock(null);
}
if(_1b){
_1b();
}
})}).play();
},show:function(_1c){
var _1d=_5.fadeIn({node:this.domNode,duration:this.duration,beforeBegin:_2.hitch(this,function(){
this.domNode.style.display="";
this.domNode.style.visibility="visible";
if(this.dockTo&&this.dockable){
this.dockTo._positionDock(null);
}
if(typeof _1c=="function"){
_1c();
}
this._isDocked=false;
if(this._dockNode){
this._dockNode.destroy();
this._dockNode=null;
}
})}).play();
var _1e=_c.getContentBox(this.domNode);
this.resize(_2.mixin(_c.position(this.domNode),{w:_1e.w,h:_1e.h}));
this._onShow();
},minimize:function(){
if(!this._isDocked){
this.hide(_2.hitch(this,"_dock"));
}
},maximize:function(){
if(this._maximized){
return;
}
this._naturalState=_c.position(this.domNode);
if(this._isDocked){
this.show();
setTimeout(_2.hitch(this,"maximize"),this.duration);
}
_b.add(this.focusNode,"floatingPaneMaximized");
this.resize(_9.getBox());
this._maximized=true;
},_restore:function(){
if(this._maximized){
this.resize(this._naturalState);
_b.remove(this.focusNode,"floatingPaneMaximized");
this._maximized=false;
}
},_dock:function(){
if(!this._isDocked&&this.dockable){
this._dockNode=this.dockTo.addNode(this);
this._isDocked=true;
}
},resize:function(dim){
dim=dim||this._naturalState;
this._currentState=dim;
var dns=this.domNode.style;
if("t" in dim){
dns.top=dim.t+"px";
}else{
if("y" in dim){
dns.top=dim.y+"px";
}
}
if("l" in dim){
dns.left=dim.l+"px";
}else{
if("x" in dim){
dns.left=dim.x+"px";
}
}
dns.width=dim.w+"px";
dns.height=dim.h+"px";
var _1f={l:0,t:0,w:dim.w,h:(dim.h-this.focusNode.offsetHeight)};
_c.setMarginBox(this.canvas,_1f);
this._checkIfSingleChild();
if(this._singleChild&&this._singleChild.resize){
this._singleChild.resize(_1f);
}
},bringToTop:function(){
var _20=_7.filter(this._allFPs,function(i){
return i!==this;
},this);
_20.sort(function(a,b){
return a.domNode.style.zIndex-b.domNode.style.zIndex;
});
_20.push(this);
_7.forEach(_20,function(w,x){
w.domNode.style.zIndex=this._startZ+(x*2);
_b.remove(w.domNode,"dojoxFloatingPaneFg");
},this);
_b.add(this.domNode,"dojoxFloatingPaneFg");
},destroy:function(){
this._allFPs.splice(_7.indexOf(this._allFPs,this),1);
if(this._resizeHandle){
this._resizeHandle.destroy();
}
this.inherited(arguments);
}});
return _16;
});
