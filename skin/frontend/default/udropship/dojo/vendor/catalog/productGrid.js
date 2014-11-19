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
		resetFilters = query("#remove-filters")[0],
		switcher = query("#attribute_set_id")[0],
		staticFilters = query("#static-filters")[0];
		
	
	var extendWithStaticFilter = function(query, form){
		var k, opt, select, name, value, fValue;
		
		// first reset query staic params
		for(k in query){
			if(query.hasOwnProperty(k) && /^static/.test(k)){
				delete query[k];
			}
		}
		
		// Set values of static filters
		jQuery.each(jQuery(form).find("option:selected"), function(i){
			opt = jQuery(this);
			value = opt.val();
			fValue = opt.attr("filtervalue");
			select = opt.parent();
			name = "static[" + value + "]";
			
			if(value!="" && fValue!=""){
				query[name] = fValue;
			}
		});
		
	};
	
	var toogleRemoveFilter = function(query){
		var k,i = 0;
		for(k in query){
			if(!/(store_id|attribute_set_id)/.test(k) && query[k]!==null){
				i++;
			}
		}
		if(resetFilters){
			resetFilters.className = i ? "remove-filters" : "hidden";
		}
	}

	/**
	 * Handle reset filters button
	 */
	if(resetFilters){
		resetFilters.on('click', function(){
			var statiFilters = jQuery("#static-filters"),
				gridFields = jQuery("#grid-holder th :text, #grid-holder th select");

			if(statiFilters.length){
				statiFilters[0].reset();
			}
			if(gridFields.length){
				gridFields.val(""); // it triggers refresh 
			}else{
				grid.refresh();
			}
		});
	}	
	
	storeRest = new JsonRest({
		target:"/udprod/vendor_product/rest",
		idProperty: "entity_id",
		
		query: function(query, options){
			if(switcher){
				query.attribute_set_id = switcher.value;
			};
			if(staticFilters){
				extendWithStaticFilter(query, staticFilters);
			}
			query.store_id = 0;
			toogleRemoveFilter(query);
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
	
	var thumbnailClickHandler = function(e){
		var modal = jQuery("#product-image-popup"),
			el = jQuery(this);
	
		if(!modal.length){
			modal = jQuery('<div id="product-image-popup" class="modal fade in" role="dialog">\
				<div class="modal-dialog">\
					<div class="modal-content">\
						<div class="modal-header">\
							<button type="button" class="close" data-dismiss="modal">\n\
								<span aria-hidden="true">×</span><span class="sr-only">Close</span></button>\
							<h4 class="modal-title"></h4>\
						</div>\
						<div class="modal-body"></div>\
						<div class="modal-footer">\
							<button type="button" class="btn btn-default" data-dismiss="modal">' + 
							Translator.translate("Close") + 
							'</button>\
						</div>\
					</div>\
				</div>\
			</div>').
			appendTo(jQuery("body")); 
		}
		modal.find(".modal-title").text(el.attr("title"));
		modal.find(".modal-body").html(
				jQuery("<img>").attr("src", el.attr("href"))
		)
		modal.modal("show");
		e.preventDefault();
	}
		
	/**
	 * @param {mixed} value
	 * @param {object} item
	 * @param {object} node
	 * @returns {string}
	 */
	var rendererThumbnail = function renderCell(item, value, node, options){
		var content;
		
		if(item.thumbnail!==null){
			content = put("a", {
				href:  item.thumbnail, 
				title: item.name,
				target: "_blank"
			});
			on(content, "click", thumbnailClickHandler)
		}else{
			content = put("p");
		}
		put(content, "img", {
			src: item.thumbnail_url,
			width: 45,
			height: 45
		});
		put(content, "span", {
			innerHTML: item.images_count
		});
		put(node, content);
	};
	
	var rendererName = function renderCell(item, value, node, options){
		var opts = this.parentColumn.statusOptions;
		var content = put("div");
		put(content, "p", {
			innerHTML: item.name
		});
		put(content, "p", {
			innerHTML: Translator.translate("St.") + ": " + opts[item.status] + 
				" / " + Translator.translate("SKU") + ": " + escape(item.sku),
			className: "info"
		});
		put(node, content);
	};
	
	/**
	 * @param {string} currency
	 * @returns {Function}
	 */
	var formatterPriceFactor = function(currency){
		/**
		 * @param {mixed} value
		 * @returns {string}
		 */
		return function(value){
			var price = parseFloat(value);
			
			if(!isNaN(price)){
				return (''+price.toFixed(2)).replace(".", ",") + " " + currency;
			}
			return value;
		}
	};
	
	var formatterOptionsFactor = function(options, multi){
		/**
		 * @param {mixed} value
		 * @returns {string}
		 */
		return function(value){
			if(value===null || value===""){
				return "";
			}
			if(!multi && typeof options[value] != "undefined"){
				return options[value];
			}else if(multi){
				var out = [];
				value.split(",").forEach(function(_value){
					if(typeof options[_value] != "undefined"){
						out.push(options[_value]);
					}
				});
				return out.join(",<br/>");
			}
			return "";
		}
	};
	
	/**
	 * Process column from backend
	 * 1. Add filter - its a function
	 * @param {type} columns
	 * @returns {unresolved}
	 */
	var processColumnSets = function(columns){
		
		// Include selector
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
		
		
		for(var i=0, column, childColumn; i<columns.length; i++){
			column = columns[i];
			
			// Prpare header filter 
			// Prepare values
			if(column.children && column.children.length && 
					column.children[0].filterable && 
					column.children[0].renderHeaderCell){
				childColumn = column.children[0];
				
				childColumn.renderHeaderCell = filter.apply(null, childColumn.renderHeaderCell);
				// Prepare fomratter
				if(childColumn.options){
					childColumn.formatter = formatterOptionsFactor(childColumn.options, column.type=="multiselect");
				}else if(column.type=="price"){
					childColumn.formatter = formatterPriceFactor(column.currencyCode);
				}else if(column.field=="name"){
					childColumn.renderCell = rendererName;
				}
			}
			
			if(column.field=="thumbnail"){
				column.renderCell = rendererThumbnail;
			}
			
			// Push to correct column set
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
		
		window.grid = grid = new PriceGrid(config, container);
		
		return window.grid;
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