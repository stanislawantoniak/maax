define([
	"dgrid/Grid",
	"dgrid/OnDemandGrid",
	"dgrid/extensions/Pagination",
	"dgrid/extensions/CompoundColumns",
	"dgrid/ColumnSet",
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
	"vendor/grid/filter",
	"vendor/catalog/priceGrid/popup/price",
	"vendor/catalog/priceGrid/popup/stock",
	"vendor/catalog/priceGrid/popup/mass/price",
	"vendor/catalog/priceGrid/RowUpdater",
	"vendor/misc"
], function(BaseGrid, Grid, Pagination, CompoundColumns, ColumnSet, Selection, 
	Keyboard, editor, declare, domConstruct, on, query, Memory, 
	Observable, put, Cache, JsonRest, Selection, selector, 
	lang, request, filter, singlePriceUpdater, singleStockUpdater, 
	massPriceUpdater, RowUpdater, misc){
		
	
	var states = {
		loaded: {},
		changed: {},
		orig: {}
	}
	
	
	
	var grid,
		testStore,
		storeRest,
		switcher = query("#attribute_set_id")[0];
			
	 storeRest = new JsonRest({
		target:"/udprod/vendor_product/rest",
		idProperty: "entity_id",
		
		query: function(query, options){
			if(switcher){
				query.attribute_set_id = switcher.value;
			};
			query['store_id'] = 0;
			return JsonRest.prototype.query.call(this, query, options);
		},
		
		put: function(obj){
			obj.changed = states.changed[obj.entity_id];
			var def = JsonRest.prototype.put.apply(this, arguments);
			def.then(function(){
				obj.changed = states.changed[obj.entity_id] = [];
			}, function(evt){
				obj.changed = states.changed[obj.entity_id] = [];

				var id = obj.entity_id;
						
				if(states.orig[id]){
					if (grid.dirty.hasOwnProperty(id)) {
						delete grid.dirty[id]; // delete dirty data
						testStore.notify(states.orig[id], obj.entity_id);
					}
				}else{
					storeRest.get(obj.entity_id).then(function(result){
						if (grid.dirty.hasOwnProperty(id)) {
						   delete grid.dirty[id]; // delete dirty data
						   testStore.notify(result, obj.entity_id);
					   }
					});
				}
			});
			return def;
		}
	});
	
	var selectorInstance = selector({label: ''});
	
	/**
	 * Process column from backend
	 * 1. Add filter - its a function
	 * @param {type} columns
	 * @returns {unresolved}
	 */
	var processColumnSets = function(columns){
		
		// Column set
//		jQuery.each(columnSets, function(index){
//			// Colun subrow
//			jQuery.each(this, function(index1){
//				// Single colummn
//				jQuery.each(this, function(index2){
//					if(index==0 && index1==0 && index1==0){
//						columnSets[0][0][0] = selectorInstance;
//					}
//					var column = this;
//					if(column.children){
//						// Column child
//						jQuery.each(column.children, function(index3){
//							if(this.renderHeaderCell instanceof Array){
//								
//							}
//						});
//					}
//				});
//			});
//		});

		var columnSets = [
			[
				[
					selector({ label: ""})
				]
			], [
				[
					
				]
			]
		];
		
		
		for(var i=0, column; i<columns.length; i++){
			column = columns[i];
			
			if(column.children && column.children.length){
				column.children[0].renderHeaderCell = filter.apply(null, column.children[0].renderHeaderCell);
			}
			
			if(column.fixed){
				columnSets[0][0].push(column);
			}else{
				columnSets[1][0].push(column);
			}
		};
		
		
		return columnSets;
	};
	
	
	//var testStore =  Observable(Cache(storeRest, Memory()));
	// cache crakcs edit
	testStore =  Observable(storeRest);
	
	var PriceGrid = declare([/*BaseGrid, Pagination,*/Grid, Selection, Keyboard, CompoundColumns, ColumnSet]);
	
	var initGrid = function(columns, container){
		
		var config = {
			columnSets: processColumnSets(columns),
			getSelectedIds: function(){
				var selected = [];
				jQuery.each(grid.selection, function(k){
					if(true==this){
						selected.push(k);
					}
				});
				return selected;
			},

			loadingMessage: "<span>" + Translator.translate("Loading...") + "</span>",
			noDataMessage: "<span>" + Translator.translate("No results found") + "</span>.",

			selectionMode: 'none',
			allowSelectAll: true,
			deselectOnRefresh: true,

			minRowsPerPage: 50,
			maxRowsPerPage: 100,
			pagingDelay: 50,
			bufferRows: 20,



			/* Paginatior  */
			/* rowsPerPage: 500,
			pagingLinks: 1,
			pagingTextBox: true,
			firstLastArrows: true,
			pageSizeOptions: [10, 15, 25],*/

			store: testStore,
			getBeforePut: false,
			sort: "entity_id"
		};
		
		return new  PriceGrid(config, container);
	};
	
	
	return {
		setColumns: function(columns){
			this.columns = columns;
		},
		getColumns: function(){
			return this.columns;
		},
		startup: function(container){
			initGrid(
					this.getColumns(),  
					container
			);
		}
	}; 
});