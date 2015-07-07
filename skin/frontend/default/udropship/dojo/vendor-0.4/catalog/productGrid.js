define([
	"dgrid/Grid",
	"dgrid/OnDemandGrid",
	"dgrid/extensions/Pagination",
	"dgrid/extensions/CompoundColumns",
	"vendor/grid/ColumnSet",
    'vendor/grid/Selection',
    'dgrid/Selector',
	"dgrid/Keyboard",
	"dojo/_base/declare",
	"dojo/dom",
	"dojo/dom-construct",
	"dojo/on",
	"dojo/query",
	"put-selector/put",
	"dojo/dom-class",
	"dojo/request/xhr",
	// stores 
	"dstore/Rest",
	"dstore/Trackable",
	"dstore/Cache",
	
    "dojo/_base/lang",
	
	"vendor/grid/filter",
	"vendor/grid/QueryGrid",
	"vendor/grid/PopupEditor",
	'vendor/catalog/productGrid/mass/status',
	'vendor/catalog/productGrid/mass/attribute',
	"vendor/misc"
], function(BaseGrid, Grid, Pagination, CompoundColumns, ColumnSet, 
	Selection, Selector, Keyboard, declare, dom, domConstruct, on, query, 
	put, domClass, xhr, Rest, Trackable, Cache, lang, filter, QueryGrid, 
	PopupEditor, status, attrbiute,  misc){
	
	var grid,store,
		massAttribute,
		editDbClick = false, // Congiure click type
		massUrl = "/udprod/vendor_product/mass",
		resetFilters = query("#remove-filters")[0],
		switcher = query("#attribute_set_id")[0],
		baseQuery = {
			attribute_set_id: switcher.value,
			store_id: 0
		};
	
	////////////////////////////////////////////////////////////////////////////
	// Filtering
	////////////////////////////////////////////////////////////////////////////
	var applyExtendFilter = function(){
		var k, opt, select, name, 
			value, fValue, query = this.get("query");
		
		// first reset query staic params
		for(k in query){
			if(query.hasOwnProperty(k) && /^static/.test(k)){
				delete query[k];
			}
		}
		
		// Set values of static filters
		jQuery("#static-filters").find("option:selected").each(function(i){
			opt = jQuery(this);
			value = opt.val();
			fValue = opt.attr("filtervalue");
			select = opt.parent();
			name = "static[" + value + "]";
			
			if(value!="" && fValue!=""){
				query[name] = fValue;
			}
		});
		
		this.set("query", query);
		
	};
	
	var toggleRemoveFilter = function(query){
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
		resetFilters.on('click', function(e){
			var statiFilters = jQuery("#static-filters"),
				gridFields = jQuery("#grid-holder th :text, #grid-holder th select");
				
			if(statiFilters.length){
				statiFilters[0].reset();
			}
			
			if(gridFields.length){
				gridFields.each(function(){
					this.filterObserver.setValue("");
				});
			}
			
			grid.set("query", {});
			e.preventDefault();
		});
	}	
		
	////////////////////////////////////////////////////////////////////////////
	// The store
	////////////////////////////////////////////////////////////////////////////
	
	var RestStore = declare([ Rest, Trackable]);
	
	window.store = store = new RestStore({
		target:"/udprod/vendor_product/rest/",
		idProperty: "entity_id",
		put: function(obj, options){
			return  RestStore.prototype.put.call(this, obj, options);
		},
		useRangeHeaders: true
	});
			
	////////////////////////////////////////////////////////////////////////////
	// Formatters & renderes
	////////////////////////////////////////////////////////////////////////////
	
	var thumbnailHandler = function(e){
		
		var el = jQuery(this);
		var modal = jQuery("#product-image-popup");
		
		// Procss enter click on thumb - redirect to a
		if(e instanceof KeyboardEvent){
			if(e.keyCode!=13){
				return;
			}
			if(modal.length && modal.is(":visible")){
				modal.modal("hide");
				return;
			}
			el = jQuery(this).find("a");
		}
		
		var node = el.parents("td");
		
	
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
		);
		
		// focus cell after close modal
		if(node.length){
			modal.one("hidden.bs.modal", function(){
				grid.focus(grid.cell(node[0]));
			});
			modal.one("shown.bs.modal", function(){
				modal.find("button").focus();
			});
		}
		
		modal.modal("show");
		e.preventDefault();
	}
		
	/**
	 * @param {mixed} value
	 * @param {object} item
	 * @param {object} node
	 * @returns {string}
	 */
	var rendererThumbnail = function (item, value, node, options){
		var content,
			img;
		if(item.thumbnail){
			content = put("a", {
				href:  item.thumbnail, 
				title: item.name,
				target: "_blank"
			});
			img = put("img", {
				src: item.thumbnail_url
			});
			on(content, "click", thumbnailHandler)
			on(node, "keydown", thumbnailHandler)
		}else{
			content = put("p", 
				put("i", {className: "glyphicon glyphicon-ban-circle"})
			);
		}
		
		put(content, "span", {
			innerHTML: item.images_count
		});
		
		put(node, content);
		
		// Put img if exists
		if(img){
			put(node, img);
		}
	};
	
	/**
	 * @param {mixed} value
	 * @param {object} item
	 * @param {object} node
	 * @returns {string}
	 */
	var rendererTextarea = function (item, value, node, options){

		var column = this;
		var timeout;
		
		jQuery(node).text(value!==null ? value : ""); // faseter escape
		
		if(value===null || value===""){
			return;
		}
		
		jQuery(node).tooltip({
			container: "body", 
			animation: false, 
			placement: "top",
			trigger: "hover",
			delay: {"show": 1000, "hide": 0},
			title: function(){
				// Show only if editor closed
				var editor = grid.get("editors")[column.field];
				if(editor instanceof PopupEditor && editor.isOpen()){
					return null;
				}
				return value;
			},
            html: true
		});
		
	};
	
	/**
	 * @param {mixed} value
	 * @param {object} item
	 * @param {object} node
	 * @returns {string}
	 */
	var rendererName = function (item, value, node, options){
		var content = put("div");
		put(content, "p", {
			innerHTML: item.name
		});
		put(content, "p", {
			innerHTML: Translator.translate("SKU") + ": " + escape(item.skuv),
			className: "info"
		});
		put(node, content);
	};
	
	/**
	 * @param {mixed} value
	 * @param {object} item
	 * @param {object} node
	 * @returns {string}
	 */
	var rendererStatus = function (item, value, node, options){
		var label = "wait";
		switch(value){
			case "1":
				label = "on";
			break;
			case "2":
				label = "off";
			break;
		}
		
		node.className = node.className + " " + "status-" + label;
		//node.innerHTML = label;
		node.title = this.options[value] || "";
		
		jQuery(node).tooltip({
			container: "body", 
			trigger: "hover",
			animation: false, 
			placement: "top",
			delay: {"show": 1000, "hide": 0}
		});
	};
	
	var rendererDescription = function (item, value, node, options){
		var label = "close";
		var color = "red";
		switch(value){
			case "1":
				label = "open";
				color = "green";
			break;
			case "0":
			default:
				label = "close";
				color = "red";
			break;
		}
		node.title = this.options[value] || "";
		
		jQuery(node).tooltip({
			container: "body", 
			trigger: "hover",
			animation: false, 
			placement: "top",
			delay: {"show": 1000, "hide": 0}
		});
		var content = put("div");
		put(content, "p", {
			innerHTML: "<i style='color:"+color+"' class='icon-2 icon-eye-"+label+"'></i>"
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
	
	/**
	 * @param {Array} options
	 * @param {Bool} multi
	 * @returns {Function}
	 */
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
	
	////////////////////////////////////////////////////////////////////////////
	// Grid struct process
	////////////////////////////////////////////////////////////////////////////
	
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
					{ selector: 'checkbox', label: "+" }
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
					if(column.field=="description_accepted") {
						childColumn.renderCell = rendererDescription;
					} else 	if(column.field=="status"){
						childColumn.renderCell = rendererStatus;
					}else{
						childColumn.formatter = formatterOptionsFactor(
							childColumn.options, column.type=="multiselect");
					}
				}else if(column.type=="price"){
					childColumn.formatter = formatterPriceFactor(column.currencyCode);
				}else if(column.type=="textarea"){
					childColumn.renderCell = rendererTextarea;
				}else if(column.field=="thumbnail"){
					childColumn.renderCell = rendererThumbnail;
				}else if(column.field=="name"){
					childColumn.renderCell = rendererName;
				}
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
		
	////////////////////////////////////////////////////////////////////////////
	// Editors
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * @returns {void}
	 */
	var hideAllEditors = function(doFocus){
		var editor, editors = grid.get('editors');
		
		for(var key in editors){
			editor = editors[key];
			if(editor instanceof PopupEditor && editor.isOpen()){
				editor.close(doFocus);
			}
		}
	}
	
	/**
	 * @param {Evented} e
	 * @returns {void}
	 */
	var handleSaveEditor = function(e){
		var dataObject = e.row.data,
			field = e.field,
			id = e.id,
			value = e.value,
			oldValue = dataObject[field];
	
		
		// Use only single row
		if(!e.useSelection){
			// Start overlay loading hidden progress 
			misc.startLoading(false);
			dataObject.attribute_mode = {};
			dataObject.attribute_mode[field] = e.mode;
			dataObject[field] = value;
			dataObject.changed = [field];
			store.put(dataObject).then(function(){
				e.deferred.resolve();
			}, function(ex){
				alert(ex.response.text);
				e.deferred.reject();
			}).always(function(){
				misc.stopLoading();
			})
			return;
		}
		
		// Start overlay loading visible progress 
		misc.startLoading();
		
		// Handle by mass action object
		var req = {};
		
		req["attribute[" + field + "]"] = value;
		req["attribute_mode[" + field + "]"] = e.mode;
	
		massAttribute.setFocusedCell(e.cell);
	
		massAttribute.send(req).then(function(){
			e.deferred.resolve();
		}, function(){
			e.deferred.reject();
		}).always(function(){
			misc.stopLoading();
		});

	}
	
	/**
	 * @param {Object} e
	 * @returns {void}
	 */
	var handleColumnEdit = function(e){
		var cell = grid.cell(this),
			column = cell.column,
			field = column.field,
			editors = column.grid.get('editors'),
			editor;

		if(!editors[field]){
			editors[field] = new PopupEditor(column);
			editors[field].on("save", handleSaveEditor);
		}
		// Enter click - skip all keys except enter
		if(e instanceof KeyboardEvent){
			if(e.keyCode==13){
				// Prevent click if editor focused before
				// @todo investigate event flow
				e.preventDefault();
				hideAllEditors(false);
			}else{
				// Skip on other key
				return;
			}
		// If mouse event was prevented (by ctrl + click) do not open an editor
		// But hide other editor anyway
		}else if (e instanceof MouseEvent){
			hideAllEditors(false);
			if(e.defaultPrevented || (editDbClick && e.type=="click")){
				return;
			}
		}
		editors[field].open(cell, e);
	}


    var handleSelectAll = function(e){

        if(Object.keys(grid.selection).length == 0){
            //select all
            grid.selectAll();
        } else {
            //deselect all
            grid.clearSelection();
        }

    }
	
	on(document.body, "click", function(e){
		var el = jQuery(e.target);
		if(el.is(".editor") || el.parents(".editor").length || el.is(".editable")){
			return;
		}
		hideAllEditors(false);
	});
	
	on(document.body, "keydown", function(e){
		var editors = grid.get("editors"),
			hasOpen = false;
		for(var k in editors){
			if(editors.hasOwnProperty(k) && editors[k] instanceof PopupEditor && editors[k].isOpen()){
				hasOpen = true;
				break;
			}
		}
		if(e.keyCode==27 && hasOpen){
			hideAllEditors(true);
			e.preventDefault();
		}
	});
	
	
	
	////////////////////////////////////////////////////////////////////////////
	// Selection handling @todo move to Selection mixin
	////////////////////////////////////////////////////////////////////////////
	/**
	 * @param {Evented} e
	 * @returns {void}
	 */
	var toggleRowSelection = function(e){
		var row  = grid.row(e);
		if(grid.isSelected(row)){
			grid.deselect(row);
		}else{
			grid.select(row);
		}
	}
	/**
	 * @param {Evented} e
	 * @returns {void}
	 */
	var handleSelection = function(e){
		// Skip selector column focuses
		if(e instanceof KeyboardEvent){
			if(domClass.contains(e.target, "dgrid-selector")){
				return;
			}
			if(e.keyCode==32){
				toggleRowSelection(e);
			}
		}else if(e instanceof MouseEvent){
			if(e.metaKey){
				toggleRowSelection(e);
				e.preventDefault();
			}
		}
	}
	
	////////////////////////////////////////////////////////////////////////////
	// Mass actions
	////////////////////////////////////////////////////////////////////////////
	
	var updateMassButton = function(){
		dom.byId("massActions").disabled = !grid.getSelectedIds().length;
	};
	
	var registerMassactions = function(grid){
		massAttribute = (new status(grid, massUrl))
		massAttribute.setMethod("attribute");
		
		var massConfirm = new status(grid, massUrl);
		massConfirm.setMethod("confirm");
		
		var massDisable = new status(grid, massUrl)
		massDisable.setMethod("disable");
		
		on(dom.byId("massConfirmProducts"), "click", function(e){
			misc.startLoading();
			massConfirm.trigger(e).always(misc.stopLoading);
		});
		
		on(dom.byId("massDisbaleProducts"), "click", function(e){
			misc.startLoading();
			massDisable.trigger(e).always(misc.stopLoading);
		});
	}
	
	////////////////////////////////////////////////////////////////////////////
	// The grid
	////////////////////////////////////////////////////////////////////////////
	
	var PriceGrid = declare([/*BaseGrid, Pagination,*/Grid, Selection, Selector, 
		Keyboard, CompoundColumns, ColumnSet, QueryGrid]);
	
	var initGrid = function(columns, container){
		var config = {
			columnSets: processColumnSets(columns),

			loadingMessage: "<span>" + Translator.translate("Loading...") + "</span>",
			noDataMessage: "<span>" + Translator.translate("No results found") + "</span>.",

			selectionMode: 'none',
			//allowSelectAll: true,
			deselectOnRefresh: true,
			
			cellNavigation: true, /*false*/

			minRowsPerPage: 20,
			maxRowsPerPage: 50,
			pagingDelay: 50,
			bufferRows: 20,

			/* Paginatior  */
			/*rowsPerPage: 500,
			pagingLinks: 1,
			pagingTextBox: true,
			firstLastArrows: true,
			pageSizeOptions: [10, 15, 25],*/

			//
			collection: store.filter(baseQuery),
			baseQuery: lang.clone(baseQuery),
			
			getBeforePut: false,
			sort: "entity_id",
			applyExtendFilter: applyExtendFilter,
			
			// Editors registry
			editors: {},
			
			// Needed for query grid
			store: store,
			// Overwrite 
			_setQuery: function(query){
				toggleRemoveFilter(query);
				return this.inherited(arguments);
			}
		};
		
		
		window.grid = grid = new PriceGrid(config, container);

		// listen for selection via space, ctrl + mouse
		grid.on(".dgrid-row:keyup", handleSelection);
		grid.on("td.dgrid-cell:click", handleSelection);
		
		// listen for selection
		grid.on("dgrid-select", updateMassButton);

		// listen for selection
		grid.on("dgrid-deselect", updateMassButton);
		
		// listen for refresh if selected
		grid.on("dgrid-refresh-complete", updateMassButton);
		
		// listen for editable
		if(editDbClick){
			grid.on("td.dgrid-cell.editable:dblclick", handleColumnEdit);
		}
		grid.on("td.dgrid-cell.editable:click", handleColumnEdit);
		grid.on("td.dgrid-cell.editable.dgrid-focus:keydown", handleColumnEdit);

        grid.on("th.field-0-0-0:click", handleSelectAll);
		
		registerMassactions(grid);
		
		
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