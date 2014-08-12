//>>built
define("dojox/charting/action2d/MouseIndicator",["dojo/_base/lang","dojo/_base/declare","dojo/_base/connect","dojo/_base/window","dojo/sniff","./ChartAction","./_IndicatorElement","dojox/lang/utils","dojo/_base/event","dojo/_base/array"],function(_1,_2,_3,_4,_5,_6,_7,du,_8,_9){
return _2("dojox.charting.action2d.MouseIndicator",_6,{defaultParams:{series:"",vertical:true,autoScroll:true,fixed:true,precision:0,lines:true,labels:true,markers:true},optionalParams:{lineStroke:{},outlineStroke:{},shadowStroke:{},lineFill:{},stroke:{},outline:{},shadow:{},fill:{},fillFunc:null,labelFunc:null,font:"",fontColor:"",markerStroke:{},markerOutline:{},markerShadow:{},markerFill:{},markerSymbol:"",offset:{},start:false,mouseOver:false},constructor:function(_a,_b,_c){
this.opt=_1.clone(this.defaultParams);
du.updateWithObject(this.opt,_c);
du.updateWithPattern(this.opt,_c,this.optionalParams);
this._listeners=this.opt.mouseOver?[{eventName:"onmousemove",methodName:"onMouseMove"}]:[{eventName:"onmousedown",methodName:"onMouseDown"}];
this._uName="mouseIndicator"+this.opt.series;
this._handles=[];
this.connect();
},_disconnectHandles:function(){
if(_5("ie")){
this.chart.node.releaseCapture();
}
_9.forEach(this._handles,_3.disconnect);
this._handles=[];
},connect:function(){
this.inherited(arguments);
this.chart.addPlot(this._uName,{type:_7,inter:this});
},disconnect:function(){
if(this._isMouseDown){
this.onMouseUp();
}
this.chart.removePlot(this._uName);
this.inherited(arguments);
this._disconnectHandles();
},onChange:function(_d){
},onMouseDown:function(_e){
this._isMouseDown=true;
if(_5("ie")){
this._handles.push(_3.connect(this.chart.node,"onmousemove",this,"onMouseMove"));
this._handles.push(_3.connect(this.chart.node,"onmouseup",this,"onMouseUp"));
this.chart.node.setCapture();
}else{
this._handles.push(_3.connect(_4.doc,"onmousemove",this,"onMouseMove"));
this._handles.push(_3.connect(_4.doc,"onmouseup",this,"onMouseUp"));
}
this._onMouseSingle(_e);
},onMouseMove:function(_f){
if(this._isMouseDown||this.opt.mouseOver){
this._onMouseSingle(_f);
}
},_onMouseSingle:function(_10){
var _11=this.chart.getPlot(this._uName);
_11.pageCoord={x:_10.pageX,y:_10.pageY};
_11.dirty=true;
this.chart.render();
_8.stop(_10);
},onMouseUp:function(_12){
var _13=this.chart.getPlot(this._uName);
_13.stopTrack();
this._isMouseDown=false;
this._disconnectHandles();
_13.pageCoord=null;
_13.dirty=true;
this.chart.render();
}});
});
