//>>built
define("dojox/mobile/bidi/IconItem",["dojo/_base/declare","./common"],function(_1,_2){
return _1(null,{_applyAttributes:function(){
if(!this.textDir&&this.getParent()&&this.getParent().get("textDir")){
this.textDir=this.getParent().get("textDir");
}
this.inherited(arguments);
},_setLabelAttr:function(_3){
if(this.textDir){
_3=_2.enforceTextDirWithUcc(_3,this.textDir);
}
this.inherited(arguments);
},_setTextDirAttr:function(_4){
if(_4&&this.textDir!==_4){
this.textDir=_4;
this.labelNode.innerHTML=_2.removeUCCFromText(this.labelNode.innerHTML);
this.labelNode.innerHTML=_2.enforceTextDirWithUcc(this.labelNode.innerHTML,this.textDir);
if(this.paneWidget){
this.paneWidget.labelNode.innerHTML=_2.removeUCCFromText(this.paneWidget.labelNode.innerHTML);
this.paneWidget.labelNode.innerHTML=_2.enforceTextDirWithUcc(this.paneWidget.labelNode.innerHTML,this.textDir);
}
}
}});
});
