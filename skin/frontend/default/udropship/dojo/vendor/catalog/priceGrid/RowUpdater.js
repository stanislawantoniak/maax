define([
	"dojo/_base/declare",
	"dojo/_base/lang",
	"put-selector/put",
	"dojo/query",
	"dojo/request"
], function(declare, lang, put, query, request){
	
	var RowUpdater = declare(null, {
		
		_cache: {},
		_queue: [],
		_grid: null,
		_timeout: null,
		_yes: "-",//"▾",
		_can: "+",//"▸",
		_not: "",
		_expandAll: false,
		_states: {},
		_storeId: null,
		
		setStoreId: function(storeIdl){
			this._storeId= storeIdl;
			this.clear();
		},
		getStoreId: function(storeIdl){
			return this._storeId;
		},
		getExpandSign: function(){
			return this._can;
		},
		getCollapseSign: function(){
			return this._yes;
		},
		setExpandAll: function(value){
			this._expandAll = value;
			// reset current
			for(var key in this._states){
				if(this._states.hasOwnProperty(key)){
					this._states[key] = value;
				}
			};
	
			query("#expand-toggler")[0].innerHTML = value ? 
				this.getCollapseSign() : this.getExpandSign();
				
			this.getGrid().refresh();
		},
		getExpandAll: function(value){
			return this._expandAll;
		},
		toggleExpandAll: function(){
			this.setExpandAll(!this.getExpandAll());
		},
		setGrid: function(grid){
			this._grid = grid;
		},
		getGrid: function(){
			return this._grid;
		},
		can: function(item){
			return !!item.can_collapse;
		},
		is: function(item){
			return this._get(item);
		},
		_set: function(item, state){
			this._states[item.entity_id] = state;
		},
		_get: function(item){
			if(typeof this._states[item.entity_id] != "undefined"){
				return !!this._states[item.entity_id];
			}
			return this.getExpandAll();
		},
		toggle: function(row){
			this._set(row.data, !this._get(row.data));
			var self = this;
			query("td.field-expander", row.element).forEach(function(item){
				item.innerHTML = self.cellRender(row.data);
			});
			// toggle state of node which was clicked
			if(this.is(row.data)){
				this.doRowExpand(row.data, row.element);
			}else{
				this.doRowCollapse(row.data, row.element)
			}
		},
		clear: function(){
			this._cache = {};
		},
		doRowExpand: function(item, node){
			this.load(item, node);
			put(node, "!collapsed");
		},
		doRowCollapse: function(item, node){
			put(node, ".collapsed");
		},
		cellRender: function(item){
			return this.is(item) ? this._yes : this._can;
		},
		queueItem: function(item){
			if(this._queue.indexOf(item.entity_id)<0){
				this._queue.push(item.entity_id);
			}
		},
		load: function(item, node){
			var cached = this.fetchFromCache(item);
			if(cached){
				this._renderSubRow(node, cached);
				return;
			}
			
			this.queueItem(item);
			this._loading(node);
			
			// set timout prcess
			this._clearTimeout();
			this._timeout = setTimeout(lang.hitch(this, this._process), 500);
		},
		
		fetchFromCache: function(item){
			if(this._cache[item.entity_id]){
				return this._cache[item.entity_id];
			}
			return null;
		},
		_cacheItem: function(item){
			this._cache[item.entity_id] = item;
		},
		_loading: function(node){
			query(".expando", node)[0].innerHTML = 'loading...';
		},
		
		_renderSubRow: function(node, data){
			// Make rendering
			var html = "loaded, id: " + data.entity_id +  ", var:" + data.var;
			switch(data.type_id){
				case "configurable":
					html = "Configurable product - " + html;
				break;
				case "simple":
				break;
			}
			
			query(".expando", node)[0].innerHTML = html;
		},
		
		_process: function(){
			// make request here
			// after that make render
			var buffer = [], i = 0;
			
			while(this._queue.length){
				buffer.push(this._queue.splice(0,1));
				if(++i>this.getGrid().get('maxRowsPerPage')){
					this._doXhr(buffer);
					buffer = [];
					i = 0;
				}
			}
			if(buffer.length){
				this._doXhr(buffer);
			}
		},
		_doXhr: function(ids){
			var self = this;
			request("/udprod/vendor_price/details", {
				query: {"ids[]": ids, "store": this.getStoreId()},
				handleAs: "json"
			}).then(function(result){
				var id, row, grid = self.getGrid();
				
				if(result && result.length){
					result.forEach(function(item){
						row = grid.row(item.entity_id);
						self._cacheItem(item);
						self._renderSubRow(row.element, item);
					});
				}
			});
		},
		_clearTimeout: function(){
			clearTimeout(this._timeout);
		}
	});
	
	return RowUpdater;
	
});