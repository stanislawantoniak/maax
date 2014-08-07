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
		constructor: function(field, grid, dataField){
			this.field = field;
			this.grid = grid;
			this.dataField = dataField;
			this.oldValue = field.value;
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
			var value = this.field.value;
			if(value!==this.oldValue){
				// need query factory
				var query = this.grid.get("query");
			
				if(value!=""){
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

