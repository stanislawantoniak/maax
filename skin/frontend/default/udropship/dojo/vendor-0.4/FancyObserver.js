define([
	"dojo/_base/declare", 
	"dojo/on",
	"dojo/query",
    "dojo/_base/lang"
], function(declare, on, query, lang){
	
	return declare(null, {
		interval: null,
		field: null,
		grid: null,
		oldValue: null,
		dataField: null, 
		options: null,
		type: null,
		delay: 50,
		
		
		constructor: function(field, handler, options){
			this.field = field;
			this.handler = handler || function(){};
			this.options = options || {};
			this.valueType = this.options.valueType || "text";
			this.oldValue = this.getValue(field.value);
			
			var tagName = field.tagName.toLowerCase();
			
			if(tagName=="input"){
				if(field.type=="text" || field.type=="number"){
					this.delay = 100;
				}
			}else if(tagName=="textarea"){
				this.delay = 300;
			}
			
			on(field, "focus", lang.hitch(this, this.registerEvent));
			on(field, "blur", lang.hitch(this, this.registerEvent));
			on(field, "change", lang.hitch(this, this.registerEvent));
		},
		getValue: function(value){
			if(value==""){
				return value;
			}
			if(this.valueType=="number"){
				return parseFloat(value.replace(",","."))
			}
			return value;
		},
		setValue: function(val){
			this._clear();
			this.oldValue = this.getValue(val);
			this.field.value = val;
		},
		// Zaczyna sprawdzanie
		registerEvent: function(){
			var self = this;
			this._clear();
			this.interval = setInterval(function(){
				self.update();
			}, this.delay);
		},
		// Czysci interwal
		_clear: function(){
			if(this.interval){
				clearInterval(this.interval);
			}
		},
		update: function(){
			var value = this.getValue(this.field.value);
			if(value!==this.oldValue){
				this.handler.apply(this.field, [value, this.oldValue]);
				this.oldValue = value;
			}
		},
		forceUpdate: function(){
			this.oldValue = this.getValue(this.field.value);
			this.handler.apply(this.field, [this.oldValue, this.oldValue]);
		}
	})
});

