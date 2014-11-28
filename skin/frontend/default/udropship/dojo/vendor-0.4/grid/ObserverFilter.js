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
		tagName: null,
		
		constructor: function(field, grid, dataField, options){
			this.field = field;
			this.grid = grid;
			this.options = options || {};
			this.valueType = this.options.valueType || "text";
			this.dataField = dataField;
			this.oldValue = this.getValue(field.value);
			this.field.filterObserver = this;
			this.tagName = field.tagName.toLowerCase();
		},
		setValue: function(value){
			this.field.value = value;
			this.oldValue = this.getValue(value);
		},
		getValue: function(value){
			if(value==""){
				return value;
			}
			if(this.valueType=="number" || this.valueType=="price"){
				return parseFloat(value.replace(",","."))
			}
			return value;
		},
		// Zaczyna sprawdzanie
		start: function(){
			var self = this;
			this._clear();
			// Trigger by select update immidietly
			if(this.tagName=="select"){
				self.update();
				return;
			}
			this.interval = setInterval(function(){
				self.update();
			}, 300);
		},
		// Stopuje interwal
		stop: function(){
			this.update();
			this._clear();
		},
		// Czysci interwal
		_clear: function(){
			if(this.interval){
				clearInterval(this.interval);
			}
		},
		updateDelayed: function(){
			var self = this;
			this._clear();
			setTimeout(function(){self.update(); }, 300);
		},
		update: function(){
			var value = this.getValue(this.field.value);
			if(value!==this.oldValue){
				// need query factory
				var query = this.grid.get('query');
	
				if(value!==""){
					query[this.dataField] = value;
				}else{
					query[this.dataField] = null;
					delete query[this.dataField];
				}
				// Set grid query
				//this.grid.set('query', query);
				this.grid.set('query', query);
//				var store  = this.grid.get("collection").get("storage");
//				this.grid.set('collection', store.filter(query));
				this.oldValue = value;
			}
		}
	})
});

