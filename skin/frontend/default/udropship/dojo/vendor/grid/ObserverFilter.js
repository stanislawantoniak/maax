define([
	"dojo/_base/declare",
	"dojo/dom-construct",
	"dojo/on",
	"dojo/query",
    "dojo/_base/lang"
], function(declare, domConstruct, on, query, lang){
	
	return declare(null, {
		interval: null,
		field: null,
		grid: null,
		oldValue: null,
		dataField: null, 
		options: null,
		type: null,
		constructor: function(field, grid, dataField, options){
			this.field = field;
			this.grid = grid;
			this.options = options || {};
			this.valueType = this.options.valueType || "text";
			this.dataField = dataField;
			this.oldValue = this.getValue(field.value);
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
		// Zaczyna sprawdzanie
		start: function(){
			var self = this;
			this._clear();
			this.interval = setInterval(function(){
				self.update();
			}, 300);
		},
		// Stopuje interwal
		stop: function(){
			this._clear();
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
				// need query factory
				var query = this.grid.get("query");
	
				if(value!==""){
					query[this.dataField] = value;
				}else{
					query[this.dataField] = null;
				}
				// Set grid query
				this.grid.set('query', query);
				this.oldValue = value;
			}
		}
	})
});

