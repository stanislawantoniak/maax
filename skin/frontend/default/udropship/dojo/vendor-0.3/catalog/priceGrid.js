define([
	"dgrid/Grid",
	"dgrid/OnDemandGrid",
	"dgrid/extensions/Pagination",
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
	"vendor/grid/filter",
	"vendor/catalog/priceGrid/popup/price",
	"vendor/catalog/priceGrid/popup/stock",
	"vendor/catalog/priceGrid/popup/mass/price",
	"vendor/catalog/priceGrid/RowUpdater",
	"vendor/misc",
    "vendor/catalog/priceGrid/popup/campaign",
    "vendor/catalog/priceGrid/popup/mass/status",
    "vendor/catalog/priceGrid/popup/mass/politics"
], function(BaseGrid, Grid, Pagination, CompoundColumns, Selection, 
	Keyboard, editor, declare, domConstruct, on, query, Memory, 
	Observable, put, Cache, JsonRest, Selection, selector, lang, 
	request, ObserverFilter, filterRendererFacory, 
	singlePriceUpdater, singleStockUpdater, 
	massPriceUpdater, RowUpdater, misc, campaignUpdater, massStatusUpdater, massPoliticsUpdater){
	
	/**
	 * @todo Make source options it dynamicly
	 */
	
	var campainRegularIdOptions = sourceOptions.campaign_regular_id,
		converterMsrpTypeOptions = sourceOptions.converter_msrp_type,
		converterPriceTypeOptions = sourceOptions.converter_price_type,
		flagOptions = sourceOptions.product_flag,
		statusOptions = sourceOptions.status,
		descriptionStatusOptions = sourceOptions.description_status,
		typeIdOptions = sourceOptions.type_id,
		boolOptions = sourceOptions.bool;

	var states = {
		loaded: {},
		changed: {},
		orig: {}
	};
	
	var priceEditPriceMeta = function(object,value){
		return !object.campaign_regular_id;
	};
	
	
	
	
	var grid,
		testStore,
		storeRest,
		updater = new RowUpdater(),
		renderer = function(obj, options){
			var div = put("div", Grid.prototype.renderRow.apply(this, arguments)),
				expando = put(div, "div.expando");

			if(updater.is(obj)){
				updater.doRowExpand(obj, div);
			}else{
				updater.doRowCollapse(obj, div);
			}

			states.orig[obj.entity_id] = lang.mixin({}, obj);

			return div;
		};
			
	var switcher = query("#store-switcher")[0];
	var priceChanger = query("#change-prices")[0];
    var massEnableProductChanger  = query("#mass-enable-products")[0];
    var massInvalidProductChanger = query("#mass-invalid-products")[0];
    var politicsChanger =query("#mass-change-politics")[0];


    storeRest = new JsonRest({
        target: "/udprod/vendor_price/rest",
        idProperty: "entity_id",
        // Overwrite querying for:
        // - filtering by selected store
        // - getting total numbers of products
        query: function (query, options) {
            // Filtering by selected store
            if (switcher) {
                query['store_id'] = switcher.value;
            }
            // Getting total numbers of products
            var ret = JsonRest.prototype.query.call(this, query, options);
            ret.total.then(function (res) {
                jQuery(".grid-total-number").html(parseInt(res));
            });
            return ret;
        },
		
		put: function(obj){
			obj.changed = states.changed[obj.entity_id];
			var def = JsonRest.prototype.put.apply(this, arguments);
			def.then(function(data){
                obj.changed = states.changed[obj.entity_id] = [];
                if (data['message']) {
                    var notyObj = {};
                    if (data['message']['text']) {
                        notyObj.text = data['message']['text'];
                        data['message']['text'] = undefined;// Show msg only once
                    }
                    if (data['message']['type']) {
                        notyObj.type = data['message']['type'];
                        data['message']['type'] = undefined;
                    } else {
                        notyObj.type = 'warning';// Default
                    }
                    if (data['message']['timeout']) {
                        notyObj.timeout = data['message']['timeout'];
                        data['message']['timeout'] = undefined;
                    }
                    if (notyObj.text) { // Show msg only once
	                    jQuery('#noty_top_layout_container').remove();
                        noty(notyObj);
                    }
                }
                // Fix for correct updating "changed" select in row for cell status
                var row = grid.row(data['entity_id']);
				jQuery(row.element).remove();
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

				alert(evt.response.data)
			});
			return def;
		}
	});
	
	
	//var testStore =  Observable(Cache(storeRest, Memory()));
	// cache crakcs edit
	testStore =  Observable(storeRest);
	
	var PriceGrid = declare([/*BaseGrid, Pagination,*/Grid, Selection, Keyboard, CompoundColumns]);

	window.priceGrid = grid = new PriceGrid({
		columns: {
			selector: selector({ 
				label: '',
                title: Translator.translate("Selection"),
                className: "header"
			}),
			expander: {
				label: '',
                title: Translator.translate("Unwrapping"),
				get: lang.hitch(updater, updater.cellRender),
				sortable: false,
				className: 'expander header',
				renderHeaderCell: function(node){
					on(node, "click", function(){
						updater.toggleExpandAll();
					});
					
					node.innerHTML = updater.getExpandSign();
					node.style.cursor = "pointer";
					node.id =  "expand-toggler";
					
					return updater.getExpandSign();
				}
			},
			name: {
				label: Translator.translate("Name"),
				field: "name",
                className: "header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("text", "name"),
						sortable: false, 
						field: "name",
						className: "filterable"
					}
				]
			},
			skuv: {
				label: Translator.translate("SKU"),
				field: "skuv",			
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("text", "skuv"),
						sortable: false, 
						field: "skuv",
						className: "filterable column-medium"
					}
				]
			},
			price: {
				label: Translator.translate("Price"),
				field: "display_price",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("range", "display_price"),
						sortable: false, 
						field: "display_price",
						className: "filterable align-right column-medium signle-price-edit popup-trigger ",
						formatter: misc.currency,
						renderCell: function(item,value,node){
							if(priceEditPriceMeta(item, value)){
								put(node, ".editable");
							}
							BaseGrid.defaultRenderCell.apply(this, arguments);
						}
					}
				]
			},
			campaign_regular_id: {
				label: Translator.translate("Price type"),
				field: "campaign_regular_id",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "campaign_regular_id", {options: campainRegularIdOptions}),
						sortable: false, 
						field: "campaign_regular_id",
						className: "filterable column-medium align-center text-overflow",
						formatter: function(value, item){
							for(var i=0; i<campainRegularIdOptions.length; i++){
								if(campainRegularIdOptions[i].value+'' == value+''){
									return campainRegularIdOptions[i].label;
								}
							}
							return "Standard";
						}
					}
				]
			},
			price_margin: {
				label: Translator.translate("Margin"),
				field: "price_margin",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("range", "price_margin"),
						sortable: false, 
						field: "price_margin",
						get: function(item){
							return (item.price_margin!==null)  ? item.price_margin : 0
						},
						formatter: function(value){
							return misc.number(value) + "%";
						},
						renderCell: function(item,value,node){
							if(priceEditPriceMeta(item, value)){
								put(node, ".editable");
							}
							BaseGrid.defaultRenderCell.apply(this, arguments);
						},
						className: "filterable align-right column-medium signle-price-edit popup-trigger"
					}
				]
			},
			converter_price_type: {
				label: Translator.translate("Price source"),
				field: "converter_price_type",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "converter_price_type", {options: converterPriceTypeOptions}),
						sortable: false, 
						field: "converter_price_type",
						className: "filterable align-center column-medium signle-price-edit popup-trigger",
						formatter: function(value, item){
							for(var i=0; i<converterPriceTypeOptions.length; i++){
								if(converterPriceTypeOptions[i].value+'' == value+''){
									return converterPriceTypeOptions[i].label;
								}
							}
							return "";
						},
						renderCell: function(item,value,node){
							if(priceEditPriceMeta(item, value)){
								put(node, ".editable");
							}
							BaseGrid.defaultRenderCell.apply(this, arguments);
						}
					}
				]
			},
			msrp: {
				label: Translator.translate("Msrp"),
				field: "msrp",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "msrp", {options: boolOptions}),
						sortable: false, 
						field: "msrp",
						className: "filterable align-right column-medium signle-price-edit popup-trigger",
						formatter: function(value){
							if(value!==null){
								return misc.currency(value);
							}
							return "";
						},
						renderCell: function(item,value,node){
							if(priceEditPriceMeta(item, value)){
								put(node, ".editable");
							}
							BaseGrid.defaultRenderCell.apply(this, arguments);
						}
					}
				]
			},
			converter_msrp_type: {
				label: Translator.translate("MSRP Source"),
				field: "converter_msrp_type",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "converter_msrp_type", {options: converterMsrpTypeOptions}),
						sortable: false, 
						field: "converter_msrp_type",
						className: "filterable align-center column-medium signle-price-edit popup-trigger",
						formatter: function(value, item){
							for(var i=0; i<converterMsrpTypeOptions.length; i++){
								if(converterMsrpTypeOptions[i].value+'' == value+''){
									return converterMsrpTypeOptions[i].label;
								}
							}
							return "";
						},
						renderCell: function(item,value,node){
							if(priceEditPriceMeta(item, value)){
								put(node, ".editable");
							}
							BaseGrid.defaultRenderCell.apply(this, arguments);
						}
					}
				]
			},
			is_new: editor({
				label: Translator.translate("New"),
				field: "is_new",
				className: "column-short header",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: boolOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "is_new", {options: boolOptions}),
						sortable: false, 
						field: "is_new",
						className: "filterable align-center column-short editable",
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
				label: Translator.translate("Best"),
				field: "is_bestseller",
				className: "column-short header",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: boolOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "is_bestseller", {options: boolOptions}),
						sortable: false, 
						field: "is_bestseller",
						className: "filterable align-center column-short editable",
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
				label: Translator.translate("Flag"),
				field: "product_flag",
				className: "column-short header",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: flagOptions, required: false},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "product_flag", {options: flagOptions}),
						sortable: false, 
						field: "product_flag",
						className: "filterable align-center column-short text-overflow",
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
				label: Translator.translate("In stock"),
				field: "is_in_stock",
				className: "column-short header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "is_in_stock", {options: boolOptions}),
						sortable: false, 
						field: "is_in_stock",
						className: "filterable align-center  column-short",
						formatter: function(value, item){
							for(var i=0; i<boolOptions.length; i++){
								if(boolOptions[i].value+'' == value+''){
									return boolOptions[i].label;
								}
							}
							return "";
						}
					}
				]
			},
			variant_qty: {
				label: Translator.translate("Variants"),
				field: "available_child_count",
				className: "column-center header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("range", "available_child_count"),
						sortable: false, 
						field: "available_child_count",
						className: "filterable align-center column-medium",
						formatter: function(value, item){
							if(item.type_id=="configurable" || item.type_id=="grouped"){
								return item.available_child_count + "/" + item.all_child_count;
							}
							return "";
						}
					}
				]
			},
			stock_qty: {
				label: Translator.translate("Stock Qty"),
				field: "stock_qty",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("range", "stock_qty"),
						sortable: false, 
						field: "stock_qty",
						className: "filterable align-right  column-medium",
						formatter: function(value){return parseInt(value);}
					}
				]
			},
			politics: {
				label: Translator.translate("Manual out-of-stock"), 
				field: "politics",
				className: "column-medium header",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: boolOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "politics", {options: boolOptions}),
						sortable: false,
						field: "politics",
						className: "filterable align-center column-short text-overflow",
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
			status: { 
				label: "Status", 
				field: "status",
				className: "column-medium header",
				children: [
					editor({
						editor: "select",
						editorArgs: {options: statusOptions, required: true},
						editOn: "dblclick",
						autoSave: true,
						renderHeaderCell: filterRendererFacory("select", "status", {options: statusOptions}),
						sortable: false, 
						field: "status",
						className: "filterable align-center column-medium editable",
						formatter: function(value, item){
							for(var i=0; i<statusOptions.length; i++){
								if(statusOptions[i].value+'' == value+''){
									return statusOptions[i].label;
								}
							}
							return "";
						}
					})
				]
			},
            description_status: {
                label: Translator.translate("Description status"),
                field: "description_status",
                className: "column-medium header",
                children: [
                    {
                        renderHeaderCell: filterRendererFacory("select", "description_status", {options: descriptionStatusOptions}),
                        sortable: false,
                        field: "description_status",
                        className: "filterable column-medium align-center text-overflow",
                        formatter: function(value, item){
                            for(var i=0; i<descriptionStatusOptions.length; i++){
                                if(descriptionStatusOptions[i].value+'' == value+''){
                                    return descriptionStatusOptions[i].label;
                                }
                            }
                            return "";
                        }
                    }
                ]
            },
			type_id: { 
				label: Translator.translate("Type"), 
				field: "type_id",
				className: "column-medium header",
				children: [
					{
						renderHeaderCell: filterRendererFacory("select", "type_id", {options: typeIdOptions}),
						sortable: false, 
						field: "type_id",
						className: "filterable column-medium text-overflow",
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
		
		renderRow: renderer,
		store: testStore,
		getBeforePut: false,
		sort: "entity_id"
	}, "grid-holder");

    var updateMassButton = function(){
        jQuery("#massActions").prop( "disabled", !grid.getSelectedIds().length);
    };

	var updateSelectionButtons = function(){
		var disabled = true;
		for(var k in grid.selection){
			if(grid.selection.hasOwnProperty(k)){
				disabled = false;
			}
		}
		jQuery(priceChanger).prop("disabled", disabled);
	};
	
	// Store switcher 
	on(switcher, "change", function(){
		updater.setStoreId(this.value);
		grid.refresh();
	});
	
	// Price changer 
	on(priceChanger, "click", function(){
		var global = jQuery(".dgrid-selector input", grid.domNode).attr("aria-checked")==="true";
		var query = grid.get("query");
		var selected = [];
		
		if(!global){
			selected = grid.getSelectedIds();
		}
		
		massPriceUpdater.handleClick({
			"global":	global ? 1 : 0,
			"query":	global ? misc.prepareQuery(query) : misc.prepareQuery({}),
			"selected": selected.join(","),
			"store_id": switcher.value
		});
	});

    // Status changer for mass enable products
    on(massEnableProductChanger, "click", function() {
        var global = jQuery(".dgrid-selector input", grid.domNode).attr("aria-checked")==="true";
        var query = grid.get("query");
        var selected = [];

        if(!global){
            selected = grid.getSelectedIds();
        }

        massStatusUpdater.handleClick({
            "global"   : global ? 1 : 0,
            "query"    : global ? misc.prepareQuery(query) : misc.prepareQuery({}),
            "selected" : selected.join(","),
            "store_id" : switcher.value,
            "status"   : "enable"
        });
    });
    // Status changer for mass invalid products
    on(massInvalidProductChanger, "click", function() {
        var global = jQuery(".dgrid-selector input", grid.domNode).attr("aria-checked")==="true";
        var query = grid.get("query");
        var selected = [];

        if(!global){
            selected = grid.getSelectedIds();
        }

        massStatusUpdater.handleClick({
            "global"   : global ? 1 : 0,
            "query"    : global ? misc.prepareQuery(query) : misc.prepareQuery({}),
            "selected" : selected.join(","),
            "store_id" : switcher.value,
            "status"   : "invalid"
        });
    });
    // Politics changer
    on(politicsChanger, "click", function() {
        var global = jQuery(".dgrid-selector input", grid.domNode).attr("aria-checked")==="true";
        var query = grid.get("query");
        var selected = [];

        if(!global){
            selected = grid.getSelectedIds();
        }

        massPoliticsUpdater.handleClick({
            "global":	global ? 1 : 0,
            "query":	global ? misc.prepareQuery(query) : misc.prepareQuery({}),
            "selected": selected.join(","),
            "store_id": switcher.value
        });
    });
	
	// listen for selection
	on.pausable(grid.domNode, "dgrid-select", updateSelectionButtons);
	on.pausable(grid.domNode, "dgrid-select", updateMassButton);

	// listen for selection
	on.pausable(grid.domNode, "dgrid-deselect", updateSelectionButtons);
	on.pausable(grid.domNode, "dgrid-deselect", updateMassButton);

    // listen for refresh if selected
    on.pausable(grid.domNode, "dgrid-refresh-complete", updateMassButton);

	// listen for clicks to trigger expand/collapse in table view mode
	on.pausable(grid.domNode, ".dgrid-row td.expander :click", function(evt){
		updater.toggle(grid.row(evt));		
	});
	
	// Open dialog to single price edit
	on.pausable(grid.domNode, ".dgrid-row .signle-price-edit.editable :click", function(evt){
		singlePriceUpdater.handleDbClick(grid.row(evt), evt);
	});
	
	

	// Open dialog to single stock edit
	on.pausable(grid.domNode, ".dgrid-row .signle-stock-edit.editable :click", function(evt){
		singleStockUpdater.handleClick(grid.row(evt), evt);
	});

    // Open dialog to remove product from campaign
    on.pausable(grid.domNode, ".dgrid-row .campaign-product-remove.editable :click", function(evt){
        campaignUpdater.handleClick(grid.row(evt), evt);
    });
			
	
	//Chandel data change
	on.pausable(grid.domNode, "dgrid-datachange", function(evt){
		var col = evt.cell.column,
			data = evt.cell.row.data;
			
		if(!states.changed[data.entity_id]){
			states.changed[data.entity_id] = [];
		}
		if(states.changed[data.entity_id].indexOf(col.field)<0){
			states.changed[data.entity_id].push(col.field);
		}
		
	});
	

	// Connect objects
	updater.setGrid	(grid);
	updater.setStoreId(switcher.value);
	
	massPriceUpdater.setGrid(grid);
	massPriceUpdater.setStoreId(switcher.value);

    massStatusUpdater.setGrid(grid);
    massStatusUpdater.setStoreId(switcher.value);
	
	massPoliticsUpdater.setGrid(grid);
	massPoliticsUpdater.setStoreId(switcher.value);

	singlePriceUpdater.setGrid(grid);
	singlePriceUpdater.setStoreId(switcher.value);
	
	singleStockUpdater.setStoreId(switcher.value);
	singleStockUpdater.setGrid(grid);

    campaignUpdater.setStoreId(switcher.value);
    campaignUpdater.setGrid(grid);

	
	updateSelectionButtons();
	updateMassButton();

    jQuery.map(window.priceGrid.columns, function (elem, i) {
        if (elem.field) {
            var title = elem.title ? elem.title : elem.label;
            jQuery(".header.field-" + elem.field).attr("title", title).attr("data-tooltip-header", "true");
        }
    });
    jQuery("[data-tooltip-header=true]").tooltip({
        container: "body",
        animation: false,
        placement: "top",
        trigger: "hover",
        delay: {"show": 0, "hide": 0}
    });

	return grid; 


	
});