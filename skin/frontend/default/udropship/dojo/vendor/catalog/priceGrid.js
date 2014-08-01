define([
	"dgrid/OnDemandGrid",
	"dgrid/extensions/CompoundColumns",
	"dgrid/Selection",
	"dgrid/Keyboard",
	"vendor/grid/editor",
	"dojo/_base/declare",
	"dojo/dom-construct",
	"dojo/on",
	"dojo/query",
	"dojo/store/Memory",
	"dojo/store/Observable",
	"put-selector/put",
	"dojo/store/Cache",
	"dojo/store/JsonRest",
    'dgrid/Selection',
    'dgrid/selector',
    "dojo/_base/lang",
	"dojo/request",
	"vendor/grid/ObserverFilter",
], function(Grid, CompoundColumns, Selection, Keyboard, editor, declare, domConstruct, 
	on, query, Memory, Observable, put, Cache, JsonRest, Selection, 
	selector, lang, request, ObserverFilter){
	
	
	var converterPriceTypeOptions = [
		{value: 799, label: "A"},
		{value: 800, label: "B"},
		{value: 801, label: "C"},
		{value: 802, label: "Z"},
	];
	
	var flagOptions = [
		{value: 1, label: "Promotion"},
		{value: 2, label: "Sale"}
	];
	
	
	var statusOptions = [
		{value: '1', label: "Enabled"},
		{value: '2', label: "Disabled"},
		{value: '3', label: "Panding"},
		{value: '4', label: "Fix"},
		{value: '5', label: "Discard"},
		{value: '6', label: "Vacation"},
	];
	
	var typeIdOptions = [
		{value: 'configurable', label: "Configurable"},
		{value: 'simple', label: "Simple"}
	];
	
	var boolOptions = [
		{value: 1, label: "Yes"},
		{value: 0, label: "No"}
	];
	
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
			case "select":
				return function(){
					var element = domConstruct.create("select", {
						"className": "select-filter"
					});
							
					var options = lang.clone(config.options || []);
					
					if(!config.required){
						options.unshift({"value":"", "label": ""});
					}
					
					options.forEach(function(item){
						put(element, domConstruct.create("option", {
							"value": item.value,
							"innerHTML": item.label
						}));
					})

							
					var observer = new ObserverFilter(element, this.grid, name);

					on(element, 'change',	lang.hitch(observer, observer.start));
					
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
							"className": "range-field" + " " + "range-field-" + type,
						});
						
						if(jQuery && jQuery.fn.numeric){
							jQuery(element).numeric(config);
						}
						
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
			
	var switcher = query("#store-switcher")[0];
	

			
	var storeRest = new JsonRest({
		target:"/udprod/vendor_price/rest",
		idProperty: "entity_id",
		query: function(query, options){
			if(switcher){
				query['store_id'] = switcher.value;
			}
			return JsonRest.prototype.query.call(this, query, options);
		},
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
					{
						renderHeaderCell: filterRendererFacory("text", "name"),
						sortable: false, 
						field: "name",
						className: "filterable",
					}
				]
			},
			price: {
				label: "Price",
				field: "display_price",
				className: "column-medium",
				children: [
					editor({
						renderHeaderCell: filterRendererFacory("range", "display_price"),
						sortable: false, 
						field: "display_price",
						editor: "text",
						editOn: "dblclick",
						className: "filterable align-right column-medium",
						autoSave: true
					})
				]
			},
			campaign_regular_id: {
				label: "Price type",
				field: "campaign_regular_id",
				className: "column-medium",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "campaign_regular_id", {options: []}),
						sortable: false, 
						field: "campaign_regular_id",
						className: "filterable column-medium align-center text-overflow",
					}
				]
			},
			price_margin: {
				label: "Margin",
				field: "price_margin",
				className: "column-medium",
				children: [
					{
						renderHeaderCell: filterRendererFacory("range", "price_margin"),
						sortable: false, 
						field: "price_margin",
						className: "filterable align-right column-medium",
					}
				]
			},
			converter_price_type: {
				label: "Price source",
				field: "converter_price_type",
				className: "column-medium",
				children: [
					editor({
						/** @todo add dynamic **/
						renderHeaderCell: filterRendererFacory("select", "converter_price_type", {options: converterPriceTypeOptions}),
						sortable: false, 
						field: "converter_price_type",
						editor: "select",
						editorArgs: {options: converterPriceTypeOptions},
						editOn: "dblclick",
						autoSave: true,
						className: "filterable align-center column-medium",
						formatter: function(value, item){
							for(var i=0; i<converterPriceTypeOptions.length; i++){
								if(converterPriceTypeOptions[i].value+'' == value+''){
									return converterPriceTypeOptions[i].label;
								}
							}
							return "";
						}
					})
				]
			},
			msrp: {
				label: "Msrp",
				field: "msrp",
				className: "column-medium",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "msrp", {options: boolOptions}),
						sortable: false, 
						field: "msrp",
						className: "filterable align-right column-medium",
					}
				]
			},
			is_new: editor({
				label: "New",
				field: "is_new",
				className: "column-short",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: boolOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "is_new", {options: boolOptions}),
						sortable: false, 
						field: "is_new",
						className: "filterable align-center column-short",
						formatter: function(value, item){
							for(var i=0; i<boolOptions.length; i++){
								if(boolOptions[i].value+'' == value+''){
									return boolOptions[i].label;
								}
							}
							return "";
						}
					})
				]
			}),
			is_bestseller: {
				label: "Best",
				field: "is_bestseller",
				className: "column-short",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: boolOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "is_bestseller", {options: boolOptions}),
						sortable: false, 
						field: "is_bestseller",
						className: "filterable align-center column-short",
						formatter: function(value, item){
							for(var i=0; i<boolOptions.length; i++){
								if(boolOptions[i].value+'' == value+''){
									return boolOptions[i].label;
								}
							}
							return "";
						}
					})
				]
			},
			product_flag: {
				label: "Flag",
				field: "product_flag",
				className: "column-short",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: flagOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "product_flag", {options: flagOptions}),
						sortable: false, 
						field: "product_flag",
						className: "filterable align-center column-short",
						formatter: function(value, item){
							for(var i=0; i<flagOptions.length; i++){
								if(flagOptions[i].value+'' == value+''){
									return flagOptions[i].label;
								}
							}
							return "";
						}
					})
				]
			},
			is_in_stock: {
				label: "In stock",
				field: "is_in_stock",
				className: "column-short",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: boolOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "is_in_stock", {options: boolOptions}),
						sortable: false, 
						field: "is_in_stock",
						className: "filterable align-center column-short",
						formatter: function(value, item){
							for(var i=0; i<boolOptions.length; i++){
								if(boolOptions[i].value+'' == value+''){
									return boolOptions[i].label;
								}
							}
							return "";
						}
					})
				]
			},
			varian_qty: {
				label: "Variant Stock",
				field: "variant_qty",
				className: "column-medium",
				children: [
					{
						renderHeaderCell: filterRendererFacory("range", "variant_qty"),
						sortable: false, 
						field: "variant_qty",
						className: "filterable align-right column-medium",
					}
				]
			},
			stock_qty: {
				label: "Stock Qty",
				field: "stock_qty",
				className: "column-medium",
				children: [
					{
						renderHeaderCell: filterRendererFacory("range", "stock_qty"),
						sortable: false, 
						field: "stock_qty",
						className: "filterable align-right column-medium",
					}
				]
			},
			status: { 
				label: "Status", 
				field: "status",
				className: "column-medium",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "status", {options: statusOptions}),
						sortable: false, 
						field: "status",
						className: "filterable align-center column-medium",
						formatter: function(value, item){
							for(var i=0; i<statusOptions.length; i++){
								if(statusOptions[i].value+'' == value+''){
									return statusOptions[i].label;
								}
							}
							return "";
						}
					}
				]
			},
			type_id: { 
				label: "Type", 
				field: "type_id",
				className: "column-medium",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "type_id", {options: typeIdOptions}),
						sortable: false, 
						field: "type_id",
						className: "filterable align-center column-medium",
						formatter: function(value, item){
							for(var i=0; i<typeIdOptions.length; i++){
								if(typeIdOptions[i].value+'' == value+''){
									return typeIdOptions[i].label;
								}
							}
							return "";
						}
					}
				]
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
	
	on(switcher, "change", function(){
		grid.refresh();
	})
	
	// listen for clicks to trigger expand/collapse in table view mode
	expandoListener = on.pausable(grid.domNode, ".dgrid-row td.field-expander :click", function(evt){
		updater.toggle(grid.row(evt));		
	});
	
	return grid;
	
	
});