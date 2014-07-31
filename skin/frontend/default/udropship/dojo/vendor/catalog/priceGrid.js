define([
	"dgrid/OnDemandGrid",
	"dgrid/extensions/CompoundColumns",
	"dgrid/Selection",
	"dgrid/Keyboard",
	"dgrid/editor",
	"dojo/_base/declare",
	"dojo/dom-construct",
	"dojo/on",
	"dojo/query",
	"dojo/store/Memory",
	"dojo/store/Observable",
	"put-selector/put",
	"dojo/text!./resources/description.html",
	"dojo/store/Cache",
	"dojo/store/JsonRest",
    'dgrid/Selection',
    'dgrid/selector',
    "dojo/_base/lang",
	"dojo/request",
	"vendor/grid/ObserverFilter",
], function(Grid, CompoundColumns, Selection, Keyboard, editor, declare, domConstruct, 
	on, query, Memory, Observable, put, descriptionHtml, Cache, JsonRest, Selection, 
	selector, lang, request, ObserverFilter){
	
	
	var states = {
		expanded: {},
		checked: {},
		loaded: {}
	}
	
	
	var filterRendererFacory = function(type, name, config){
		type = type || "text";
		config = config || {};
		switch(type){
			case "text":
				return function(){
					var element = domConstruct.create("input", {
						"type": "text",
						"className": "text-filter"
					}),
					observer = new ObserverFilter(element, this.grid, name);

					on(element, 'focus',	lang.hitch(observer, observer.start));
					on(element, 'keyup',	lang.hitch(observer, observer.start));
					on(element, 'keydown',	lang.hitch(observer, observer.start));
					on(element, 'blur',		lang.hitch(observer, observer.stop));
					
					return element;
				}
			break;
			case "range":
				return function(){
					var grid = this.grid;
					var wrapper = domConstruct.create("div");
					["from", "to"].forEach(function(type){
						var element = domConstruct.create("input", {
							"type": "text",
							"placeholder": type[0].toUpperCase() + type.slice(1),
							"className": "range-field" + " " + "range-field-" + type
						});
						
						var observer = new ObserverFilter(element, grid, name + '['+type+']');
						
						on(element, 'focus',	lang.hitch(observer, observer.start));
						on(element, 'keyup',	lang.hitch(observer, observer.start));
						on(element, 'keydown',	lang.hitch(observer, observer.start));
						on(element, 'blur',		lang.hitch(observer, observer.stop));
						
						put(wrapper, element);
					});

					
					return wrapper;
				}
			break;
		}
	}
	
	var RowUpdater = declare(null, {
		
		_cache: {},
		_queue: [],
		_grid: null,
		_timeout: null,
		_yes: "-",//"▾",
		_can: "+",//"▸",
		_not: "",
		_expandAll: false,
		
		getExpandSign: function(){
			return this._can;
		},
		getCollapseSign: function(){
			return this._yes;
		},
		setExpandAll: function(value){
			this._expandAll = value;
			// reset current
			for(var key in states.expanded){
				if(states.expanded.hasOwnProperty(key)){
					states.expanded[key] = value;
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
			states.expanded[item.entity_id] = state;
		},
		_get: function(item){
			if(typeof states.expanded[item.entity_id] != "undefined"){
				return !!states.expanded[item.entity_id];
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
			query(".expando", node)[0].innerHTML = "loaded, id: " + data.entity_id +  ", var:" + data.var;
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
				query: {"ids[]": ids},
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
	
	var grid,
		updater = new RowUpdater(),
		renderer = function(obj, options){
				var div = put("div", Grid.prototype.renderRow.apply(this, arguments)),
					expando = put(div, "div.expando");
			
				if(updater.is(obj)){
					updater.doRowExpand(obj, div);
				}else{
					updater.doRowCollapse(obj, div);
				}
			
				return div;
			};
			
			
	var storeRest = new JsonRest({
		target:"/udprod/vendor_price/rest",
		idProperty: "entity_id"
	});
	
	
	//var testStore =  Observable(Cache(storeRest, Memory()));
	// cache crakcs edit
	var testStore =  Observable(storeRest);
	
	var PriceGrid = declare([Grid, Selection,Keyboard, CompoundColumns]);
	
	grid = new PriceGrid({
		columns: {
			selector: selector({ label: ''}),
			expander: {
				label: '',
				get: lang.hitch(updater, updater.cellRender),
				sortable: false,
				renderHeaderCell: function(node){
					on(node, "click", function(){
						updater.toggleExpandAll();
					})
					
					node.innerHTML = updater.getExpandSign();
					node.style.cursor = "pointer";
					node.id =  "expand-toggler";
					
					return updater.getExpandSign();
				}
			},
			name: {
				label: "Name",
				field: "name",
				children: [
					editor({
						renderHeaderCell: filterRendererFacory("text", "name"),
						sortable: false, 
						field: "name",
						editor: "text",
						editOn: "dblclick",
						className: "filterable",
						autoSave: true
					})
				]
			},
			price: {
				label: "Price",
				field: "final_price",
				className: "column-medium",
				children: [
					editor({
						renderHeaderCell: filterRendererFacory("range", "final_price"),
						sortable: false, 
						field: "final_price",
						editor: "text",
						editOn: "dblclick",
						className: "filterable align-right column-medium",
						autoSave: true
					})
				]
			},
			margin: {
				label: "Margin",
				field: "price_margin",
				className: "column-medium",
				children: [
					editor({
						renderHeaderCell: filterRendererFacory("range", "margin"),
						sortable: false, 
						field: "price_margin",
						editor: "text",
						editOn: "dblclick",
						className: "filterable align-right column-medium",
						autoSave: true
					})
				]
			},
			entity_id: {
				label: "Product Id",
				field: "entity_id"
			},
			type: { 
				label: "Type", 
				field: "type_id",
				className: "column-medium"
			}
		},
		loadingMessage: "<span>Loading data...</span>",
		noDataMessage: "<span>No results found</span>.",
        selectionMode: 'none',
		minRowsPerPage: 50,
		maxRowsPerPage: 100,
		pagingDelay: 200,
		bufferRows: 50,
		renderRow: renderer,
		store: testStore,
		deselectOnRefresh: false,
		getBeforePut: false,
		sort: "entity_id"
	}, "grid-holder");
	
	
	updater.setGrid(grid);
	
	// listen for clicks to trigger expand/collapse in table view mode
	expandoListener = on.pausable(grid.domNode, ".dgrid-row td.field-expander :click", function(evt){
		updater.toggle(grid.row(evt));		
	});
	
	return grid;
	
	
});