define([
    "dgrid/Grid",                       // BaseGrid
    "dgrid/OnDemandGrid",               // Grid
    "dgrid/extensions/Pagination",      // Pagination
    "dgrid/extensions/CompoundColumns", // CompoundColumns
    "vendor/grid/ColumnSet",            // ColumnSet
    'vendor/grid/Selection',            // Selection
    'dgrid/Selector',                   // Selector
    "dgrid/Keyboard",                   // Keyboard
    "dojo/_base/declare",               // declare
    "dojo/dom",                         // dom
    "dojo/dom-construct",               // domConstruct
    "dojo/on",                          // on
    "dojo/query",                       // query
    "put-selector/put",                 // put
    "dojo/dom-class",                   // domClass
    "dojo/request/xhr",                 // xhr
    // stores
    "dstore/Rest",                      // Rest
    "dstore/Trackable",                 // Trackable
    "dstore/Cache",                     // Cache
    "dojo/_base/lang",                  // lang
    "vendor/grid/filter",               // filter
    "vendor/grid/QueryGrid",            // QueryGrid
    "vendor/grid/PopupEditor",          // PopupEditor
    'vendor/catalog/productGrid/mass/status',        // status
    'vendor/catalog/productGrid/mass/attribute',     // attrbiute
    'vendor/catalog/productGrid/mass/attributeRules',// attributeRules
    'vendor/catalog/productGrid/mass/attributeSet',// attributeSet
    'vendor/catalog/productGrid/mass/changesHistory',// changesHistory,
    "vendor/misc"//misc
], function (BaseGrid, Grid, Pagination, CompoundColumns, ColumnSet,
             Selection, Selector, Keyboard, declare, dom, domConstruct, on, query,
             put, domClass, xhr, Rest, Trackable, Cache, lang, filter, QueryGrid,
             PopupEditor, status, attrbiute, attributeRules, attributeSet, changesHistory, misc) {

    var grid,
		gallery,
        store,
        massAttribute,
        editDbClick = false, // Configure click type
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
    var applyExtendFilter = function () {
        var k, opt, select, name,
            value, fValue, query = this.get("query");

        // first reset query staic params
        for (k in query) {
            if (query.hasOwnProperty(k) && /^static/.test(k)) {
                delete query[k];
            }
        }

        // Set values of static filters
        jQuery("#static-filters").find("option:selected").each(function (i) {
            opt = jQuery(this);
            value = opt.val();
            fValue = opt.attr("filtervalue");
            select = opt.parent();
            name = "static[" + value + "]";

            if (value != "" && fValue != "") {
                query[name] = fValue;
            }
        });

        this.set("query", query);

    };

    var toggleRemoveFilter = function (query) {
        var k, i = 0;
        for (k in query) {
            if (!/(store_id|attribute_set_id)/.test(k) && query[k] !== null) {
                i++;
            }
        }
        if (resetFilters) {
            resetFilters.className = i ? "remove-filters" : "hidden";
        }
    };

    /**
     * Handle reset filters button
     */
    if (resetFilters) {
        resetFilters.on('click', function (e) {
            var statiFilters = jQuery("#static-filters"),
                gridFields = jQuery("#grid-holder th :text, #grid-holder th select");

            if (statiFilters.length) {
                statiFilters[0].reset();
            }

            if (gridFields.length) {
                gridFields.each(function () {
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

    var RestStore = declare([Rest, Trackable]);

    window.store = store = new RestStore({
        target: "/udprod/vendor_product/rest/",
        idProperty: "entity_id",
        put: function (obj, options) {
            return RestStore.prototype.put.call(this, obj, options);
        },
        // Overwrite request for:
        // - getting total numbers of products
        _request: function (kwArgs) {
            var ret = this.inherited(arguments);
            // Getting total numbers of products
            ret.total.then(function (res) {
                jQuery(".grid-total-number").html(parseInt(res));
            }, function () {
            });
            return ret;
        },
        useRangeHeaders: true
    });

    ////////////////////////////////////////////////////////////////////////////
    // Formatters & renderes
    ////////////////////////////////////////////////////////////////////////////
    var thumbnailHandler = function (e) {
        e.preventDefault();

        var el = jQuery(this);
        var modal = jQuery("#product-image-popup");
        var timeout = 0;

        if(modal.is(":visible")) {
            timeout = 250;
            modal.find('button.close').click();
        }

        setTimeout(function() {
            // mark row
            var row = grid.row(e),
                rowClass = 'grid_row_highlighted';

            jQuery('.'+rowClass).removeClass(rowClass);
            jQuery(row.element).addClass(rowClass);

            // Process enter click on thumb - redirect to a
            if (e instanceof KeyboardEvent) {
                if (e.keyCode != 13) {
                    return;
                }
                if (modal.length && modal.is(":visible")) {
                    modal.modal("hide");
                    return;
                }
                el = jQuery(this).find("a");
            }

            var node = el.parents("td");


            modal.find(".modal-title").text(el.attr("title")).prepend('<big><i class="icon icon-move"></i></big>&nbsp;&nbsp;');
            modal.find(".carousel").remove();
            modal.find(".modal-body").html('<div class="carousel">'+gallery[row.id]+'</div>');
            jQuery('#product-image-popup .carousel').rwdCarousel({
                items : 1,
                itemsDesktop : [1000,1], //5 items between 1000px and 901px
                itemsDesktopSmall : [900,1], // betweem 900px and 601px
                itemsTablet: [600,1], //2 items between 600 and 0
                itemsMobile : [480,1],
                pagination : true,
                itemsScaleUp:true,
                rewindNav : false,
                navigation: true,
                navigationText: [
                    "<div class='col-xs-6 product-image-popup-arrow-left'><i class='icon icon-arrow-left'></i></div>",
                    "<div class='col-xs-6 product-image-popup-arrow-right'><i class='icon icon-arrow-right'></i></div>"
                ],

            }).find('.rwd-item').click(function(e) {
                var offset = jQuery(this).offset();
                var pos_x = e.pageX - offset.left;
                var middle = jQuery(this).outerWidth() / 2;

                if(pos_x < middle)
                {
                    jQuery(this).trigger('rwd.prev');
                }
                else
                {
                    jQuery(this).trigger('rwd.next');
                }
            });

            // focus cell after close modal
            if (node.length) {
                modal.one("hidden.bs.modal", function () {
                    grid.focus(grid.cell(node[0]));
                    modal.find(".modal-body .carousel").html("");
                    jQuery('.' + rowClass).removeClass(rowClass);
                });
                modal.one("shown.bs.modal", function () {
                    window.thumbnailModalOpened = true;
                    modal.find("button").focus();
                });
            }


            modal.modal({backdrop: false});

            jQuery("#product-image-popup").draggable({
                handle: ".modal-header"
            });
        },timeout);
    };

    /**
     * @param {mixed} value
     * @param {object} item
     * @param {object} node
     * @returns {string}
     */
    var rendererThumbnail = function (item, value, node, options) {
        var content,
            img;
        //console.log(item);
        if (item.thumbnail) {
            content = put("a", {
                href: item.thumbnail,
                title: item.name,
                target: "_blank",
                class: "thumb"
            });
            img = put("img", {
                src: item.thumbnail_url
            });

            //jQuery(node).popover({
            //    placement: "right",
            //    html : true,
            //    container: 'body',
            //    trigger : 'hover', //<--- you need a trigger other than manual
            //    delay: {
            //        hide: 1000
            //    },
            //    content: function() {
            //        return "<div><img src='"+item.thumbnail+"' /><span class='view_lupa view_lupa_plus'></span></div>";
            //    }
            //});
            on(content, "click", thumbnailHandler)
            on(node, "keydown", thumbnailHandler)

        } else {
            content = put("p",
                put("i", {className: "glyphicon glyphicon-ban-circle"})
            );
        }

        put(content, "span", {
            innerHTML: item.images_count
        });
		gallery[item.entity_id] = item.gallery;
        put(node, content);


        // Put img if exists
        if (img) {
            put(node, img);
        }



    };

    /**
     * @param {mixed} value
     * @param {object} item
     * @param {object} node
     * @returns {string}
     */
    var rendererTextarea = function (item, value, node, options) {

        var column = this;
        var timeout;

        jQuery(node).text(value !== null ? value : ""); // faseter escape

        if (value === null || value === "") {
            return;
        }

        jQuery(node).tooltip({
            container: "body",
            animation: false,
            placement: "top",
            trigger: "hover",
            delay: {"show": 0, "hide": 0},
            title: function () {
                // Show only if editor closed
                var editor = grid.get("editors")[column.field];
                if (editor instanceof PopupEditor && editor.isOpen()) {
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
    var rendererName = function (item, value, node, options) {
        var content = put("div.editable");
        put(content, "p.editable", {
            innerHTML: item.name
        });
        put(content, "p", {
            innerHTML: Translator.translate("SKU") + ": " + escape(item.skuv),
            className: "info editable",
        });

        var canvas = document.getTextWidthCanvas || (document.getTextWidthCanvas = document.createElement("canvas"));
        var context = canvas.getContext("2d");
        context.font = "normal arial 13px";
        var metrics = context.measureText(value);

        if(metrics.width > 156) {
            node.title = value;
            jQuery(node).tooltip({
                container: "body",
                trigger: "hover",
                animation: false,
                placement: "top",
                delay: {"show": 0, "hide": 0}
            });
        }

        put(node, content);
    };

    var rendererDescriptionStatus = function (item, value, node, options) {

        // @see Zolago_Catalog_Model_Product_Source_Description
        //const DESCRIPTION_NOT_ACCEPTED = 1;// Nie zatwierdzony
        //const DESCRIPTION_WAITING      = 2;// Oczekuje na zatwierdzenie
        //const DESCRIPTION_ACCEPTED     = 3;// Zatwierdzony

        value = parseInt(value);
        var icon = '';
        switch (value) {
            case 3:
                icon = "ania-icon-accepted";
                break;
            case 1:
                icon = "ania-icon-notaccepted";
                break;
            case 2:
                icon = "ania-icon-hourglass";
                break;
            default:
                icon = "ania-icon-notaccepted";
                value = 1;
                break;
        }

        var productPreview = '<i class="icon-product-preview product_preview_tooltip" title="' + Translator.translate("Product preview") + '" ' +
                             'data-toggle="modal" data-target="#productPreviewModal" data-product-id="' + item.entity_id + '"></i>',
            descriptionStatus = "<i class='" + icon + " description_status_tooltip' title='" + (this.options[value] || "") + "'></i> ";

        var content = put("div");
        put(content, "p", {
            innerHTML: descriptionStatus + productPreview
        });



        put(node, content);

        var tooltipOptions = {
            container: "body",
            trigger: "hover",
            animation: false,
            placement: "top",
            delay: {"show": 0, "hide": 0}
        };

        jQuery(node).find(".description_status_tooltip,.product_preview_tooltip").tooltip(tooltipOptions);
    };

	var rendererIsInStock = function (item, value, node, options) {
		var itemsTxt		= item.type_id === "simple" ? Translator.translate("Available {{stock_qty}} items") : Translator.translate("Available together {{stock_qty}} items");
		var variantsTxt		= item.type_id === "simple" ? "" : Translator.translate("{{available_child_count}} variants of {{all_child_count}}");
		if (item.stock_qty == 1) {
			itemsTxt = item.type_id === "simple" ? Translator.translate("Available {{stock_qty}} item") : Translator.translate("Available together {{stock_qty}} item");
		}

		itemsTxt	= itemsTxt.replace("{{stock_qty}}", item.stock_qty);
		variantsTxt	= variantsTxt.replace("{{available_child_count}}", item.available_child_count);
		variantsTxt	= variantsTxt.replace("{{all_child_count}}", item.all_child_count);

		node.title = "<div style='text-align: center;'>" + itemsTxt + "<br/>" + variantsTxt + "</div>";

		jQuery(node).tooltip({
			html: true,
			container: "body",
			trigger: "hover",
			animation: false,
			placement: "top",
			delay: {"show": 0, "hide": 0},
		});

		var content = put("div");
		put(content, "p", {
			innerHTML: parseInt(item.stock_qty)
		});
		put(node, content);
	};
    
    /**
     * @param {string} currency
     * @returns {Function}
     */
    var formatterPriceFactor = function (currency) {
        /**
         * @param {mixed} value
         * @returns {string}
         */
        return function (value) {
            var price = parseFloat(value);

            if (!isNaN(price)) {
                return ('' + price.toFixed(2)).replace(".", ",") + " " + currency;
            }
            return value;
        }
    };

    /**
     * @param {Array} options
     * @param {Bool} multi
     * @returns {Function}
     */
    var formatterOptionsFactor = function (options, multi) {
        /**
         * @param {mixed} value
         * @returns {string}
         */
        return function (value) {
            if (value === null || value === "") {
                return "";
            }
            if (!multi && typeof options[value] != "undefined") {
                return options[value];
            } else if (multi) {
                var out = [];
                value.split(",").forEach(function (_value) {
                    if (typeof options[_value] != "undefined") {
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
    var processColumnSets = function (columns) {

        // Include selector
        var columnSets = [
            [
                [
                    {selector: 'checkbox', label: ""}
                ]
            ], [
                []
            ]
        ];

        for (var i = 0, column, childColumn; i < columns.length; i++) {
            column = columns[i];

            // Prpare header filter
            // Prepare values
            if (column.children && column.children.length &&
                column.children[0].filterable &&
                column.children[0].renderHeaderCell) {
                childColumn = column.children[0];

                childColumn.renderHeaderCell = filter.apply(null, childColumn.renderHeaderCell);

                // Prepare fomratter
                if (childColumn.options) {
                    if (column.field == "description_status") {
                        childColumn.renderCell = rendererDescriptionStatus;
                    } else if (column.field == "is_in_stock") {
						childColumn.renderCell = rendererIsInStock;
					} else {
                        childColumn.formatter = formatterOptionsFactor(
                            childColumn.options, column.type == "multiselect");
                    }
                } else if (column.type == "price") {
                    childColumn.formatter = formatterPriceFactor(column.currencyCode);
                } else if (column.type == "textarea") {
                    childColumn.renderCell = rendererTextarea;
                } else if (column.field == "thumbnail") {
                    childColumn.renderCell = rendererThumbnail;
                } else if (column.field == "name") {
                    childColumn.renderCell = rendererName;
                }
            }


            // Push to correct column set
            if (column.fixed) {
                columnSets[0][0].push(column);
            } else {
                columnSets[1][0].push(column);
            }
        }


        return columnSets;
    };

    ////////////////////////////////////////////////////////////////////////////
    // Editors
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @returns {void}
     */
    var hideAllEditors = function (doFocus) {
        var editor, editors = grid.get('editors');

        for (var key in editors) {
            editor = editors[key];
            if (editor instanceof PopupEditor && editor.isOpen()) {
                editor.close(doFocus);
            }
        }
    };

    /**
     * @param {Evented} e
     * @returns {void}
     */
    var handleSaveEditor = function (e) {
        var dataObject = e.row.data,
            field = e.field,
            id = e.id,
            value = e.value,
            oldValue = dataObject[field];


        // Use only single row (if checkbox: 'Apply to selection' is unchecked)
        if (!e.useSelection) {
            // Start overlay loading hidden progress
            misc.startLoading(false);
            dataObject.attribute_mode = {};
            dataObject.attribute_mode[field] = e.mode;
            dataObject[field] = value;
            dataObject.changed = [field];

            store.put(dataObject).then(function () {
                e.deferred.resolve();
                window.changesHistory.updateModal();
            }, function (ex) {
                alert(ex.response.text);
                e.deferred.reject();
            }).always(function () {
                misc.stopLoading();
            });
            return;
        }

        // Start overlay loading visible progress
        misc.startLoading();

        // Handle by mass action object
        var req = {};

        req["attribute[" + field + "]"] = value;
        req["attribute_mode[" + field + "]"] = e.mode;
        // Add info about 'Save as rule'
        req["save_as_rule"] = e.useSaveAsRule;

        massAttribute.setFocusedCell(e.cell);

        massAttribute.send(req).then(function () {
            e.deferred.resolve();
            if (e.useSaveAsRule) {
                // Get current attributes mapper block by ajax with spinner
                window.attributeRules.updateModal();
            }
            window.changesHistory.updateModal();
        }, function () {
            e.deferred.reject();
        }).always(function () {
            misc.stopLoading();
        });
    };

    /**
     * @param {Object} e
     * @returns {void}
     */
    var handleColumnEdit = function (e) {
        var cell = grid.cell(this),
            column = cell.column,
            field = column.field,
            editors = column.grid.get('editors'),
            editor;

        if (!editors[field]) {
            editors[field] = new PopupEditor(column);
            editors[field].on("save", handleSaveEditor);
        }
        // Enter click - skip all keys except enter
        if (e instanceof KeyboardEvent) {
            if (e.keyCode == 13) { // Enter key
                // Prevent click if editor focused before
                // @todo investigate event flow
                e.preventDefault();
                hideAllEditors(false);
            } else {
                // Skip on other key
                return;
            }
            // If mouse event was prevented (by ctrl + click) do not open an editor
            // But hide other editor anyway
        } else if (e instanceof MouseEvent) {
            hideAllEditors(false);
            if (e.defaultPrevented || (editDbClick && e.type == "click")) {
                return;
            }
        }
        editors[field].open(cell, e);
    };


    var handleSelectAll = function (e) {

        if (Object.keys(grid.selection).length == 0) {
            //select all
            grid.selectAll();
        } else {
            //deselect all
            grid.clearSelection();
        }

    };

    /**
     * Closing(hiding) editors if click somewhere on page (if it's not editor and cell)
     */
    on(document.body, "click", function (e) {
        var el = jQuery(e.target);
        if (el.is(".editor") || el.parents(".editor").length || el.is(".editable")) {
            return;
        }
        hideAllEditors(false);
    });

    on(document.body, "keydown", function (e) {
        var editors = grid.get("editors"),
            hasOpen = false;
        for (var k in editors) {
            if (editors.hasOwnProperty(k) && editors[k] instanceof PopupEditor && editors[k].isOpen()) {
                hasOpen = true;
                break;
            }
        }
        if (e.keyCode == 27 && hasOpen) { // Escape key
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
    var toggleRowSelection = function (e) {
        var row = grid.row(e);
        if (grid.isSelected(row)) {
            grid.deselect(row);
        } else {
            grid.select(row);
        }
    };
    /**
     * @param {Evented} e
     * @returns {void}
     */
    var handleSelection = function (e) {
        // Skip selector column focuses
        if (e instanceof KeyboardEvent) {
            if (domClass.contains(e.target, "dgrid-selector")) {
                return;
            }
            if (e.keyCode == 32) { // Space key
                toggleRowSelection(e);
            }
        } else if (e instanceof MouseEvent) {
            if (e.metaKey) {
                toggleRowSelection(e);
                e.preventDefault();
            }
        }
    };

    ////////////////////////////////////////////////////////////////////////////
    // Mass actions
    ////////////////////////////////////////////////////////////////////////////

    var updateMassButton = function () {
        dom.byId("massActions").disabled = !grid.getSelectedIds().length;
    };

    var registerMassactions = function (grid) {
        massAttribute = new status(grid, massUrl);
        massAttribute.setMethod("attribute");

        var massConfirm = new status(grid, massUrl);
        massConfirm.setMethod("confirm");

        var massDisable = new status(grid, massUrl);
        massDisable.setMethod("disable");

        on(dom.byId("massConfirmProducts"), "click", function (e) {
            misc.startLoading();
            massConfirm.trigger(e).always(misc.stopLoading);
        });

        // Open popup with autofill rules table
        jQuery("#massAttributeRules").click(function () {
            jQuery("a[data-target=#showAttributeRules]").click();
        });


    };

    ////////////////////////////////////////////////////////////////////////////
    // The grid
    ////////////////////////////////////////////////////////////////////////////

    var PriceGrid = declare([/*BaseGrid, Pagination,*/Grid, Selection, Selector,
        Keyboard, CompoundColumns, ColumnSet, QueryGrid]);

    var initGrid = function (columns, container) {
        var config = {
            columnSets: processColumnSets(columns),

            loadingMessage: '<div id="spinner_block"><i class="fa fa-spinner fa-spin"></i><div>' + Translator.translate("loading-spinner-bottom") + '</div></div>',
            noDataMessage: "<span>" + Translator.translate("No results found") + "</span>.",

            selectionMode: 'none',
            allowSelectAll: true,
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
            _setQuery: function (query) {
                toggleRemoveFilter(query);
                return this.inherited(arguments);
            }
        };
		gallery = [];

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
        if (editDbClick) {
            grid.on("td.dgrid-cell.editable:dblclick", handleColumnEdit);
        }
        grid.on("td.dgrid-cell.editable:click", handleColumnEdit);
        grid.on("td.dgrid-cell.editable.dgrid-focus:keydown", handleColumnEdit);

//        grid.on("th.field-0-0-0:click", handleSelectAll);

        registerMassactions(grid);
        return window.grid;
    };
    window.attributeSet = {
        _attributeSet: {},
        init: function (grid) {
            this._attributeSet = new attributeSet(grid, this.getFormActionUrl());
            this.attachSubmitButton();
        },
        getModal: function () {
            return jQuery('#massChangeAttributeSet');
        },
        getForm: function () {
            return this.getModal().find("form").first();
        },
        getFormActionUrl: function () {
            return this.getModal().find("form").prop("action");
        },
        closeModal: function () {
            this.getModal().modal('hide');
        },
        attachSubmitButton: function () {
            var self = this;
            // Main logic for submit
            this.getForm().submit(function (event) {
                // Stop form from submitting normally
                event.preventDefault();
                self.getModal().modal('hide');
                misc.startLoading();

                var ids = window.grid.getSelectedIds().join(",");
                var attribute_set = self.getModal().find("[name=attribute_set] option:selected").val();

                window.attributeSet._attributeSet.send({attribute_set_move_to: attribute_set})
                    .always(function () {
                        misc.stopLoading();
                    })
                ;

            });
        }
    },
        window.changesHistory = {
            _changesHistory: {},
            init: function (grid) {
                this._changesHistory = new changesHistory(grid);
                this.attachLogicRevertChange();
                this.attachControls();
            },
            attachControls : function(){
                jQuery(".show-changes-history-details").click(function(){
                    jQuery(this).closest(".changes-history-item").find(".changes-history-details").toggleClass("hidden");

                })
            },
            setSpinner: function () {
                var spinner = jQuery("<div>").css('text-align', 'center')
                    .append('<img src="/skin/frontend/default/udropship/img/bootsrap/ajax-loading.gif">');
                this.getModal().find(".modal-body").html(spinner);
            },
            getModal: function () {
                return jQuery('#showChangesHistory');
            },
            /**
             * Update html by ajax changesHistory modal
             */
            updateModal: function () {
                this.setSpinner();
                jQuery.ajax({
                    cache: false,
                    url: "/udprod/vendor_product/manageChangesHistory",
                    data: {}
                }).success(function (data, textStatus, jqXHR) {
                    var content = jQuery(data).find(".modal-dialog");
                    jQuery("#showChangesHistory").html(content);
                    window.changesHistory.init(window.grid);
                }).always(function () {

                });
            },
            attachLogicRevertChange: function () {
                jQuery("#revertChangeAttribute").click(function(){
                    window.changesHistory.setSpinner();
                    var changeAttributeHistoryId = jQuery(this).data("id");

                    jQuery.ajax({
                        cache: false,
                        url: "/udprod/vendor_product/revertChangesHistory",
                        data: {id: changeAttributeHistoryId}
                    }).success(function (data, textStatus, jqXHR) {

                        var response = jQuery.parseJSON(data);

                        if(response.length == 0){
                            window.changesHistory.updateModal();
                            window.grid.refresh();
                        } else {
                            noty({
                                text: response.error,
                                type: 'error',
                                timeout: 10000
                            });
                        }

                    }).always(function () {

                    });
                });
            }
        },
        window.attributeRules = {
            _tmpRemoveBtn: null,
            _attributeRules: {},

            init: function (grid) {
                this._attributeRules = new attributeRules(grid, this.getFormActionUrl());
                this.attachLogicShowDetails();
                this.attachLogicGroupCheckbox();
                this.attachLogicSubmitButton();
                this.attachLogicOnOpen();
                this.attachLogicRemoveRule();
            },

            getFormActionUrl: function () {
                return this.getModal().find("form:eq(0)").prop("action");
            },

            setSpinner: function () {
                var spinner = jQuery("<div>").css('text-align', 'center').append('<img src="/skin/frontend/default/udropship/img/bootsrap/ajax-loading.gif">');
                this.getModal().find(".modal-body").html(spinner);
            },

            /**
             * Update html by ajax auto fill attributes modal
             */
            updateModal: function () {
                this.setSpinner();
                jQuery.ajax({
                    cache: false,
                    url: "/udprod/vendor_product/manageattributes",
                    data: {attribute_set_id: this.getAttributeSetId()}
                }).success(function (data, textStatus, jqXHR) {
                    var form = jQuery(data).find("form:eq(0)");
                    jQuery("#showAttributeRules").html(form); // replace only "inside" html of modal
                    window.attributeRules.init(window.grid);
                    FormComponents.initUniform();// Attach checkbox style
                }).always(function () {
                    jQuery("input[type=checkbox][name=saveAsRule]").prop("checked", false);
                });
            },

            attachLogicRemoveRule: function () {
                this.getModal().find(".btn-remove-rule[data-action=remove]").tooltip();

                this.getModal().find(".btn-remove-rule[data-action=remove]").click(function (e) {
                    e.preventDefault();

                    window.attributeRules._tmpRemoveBtn = jQuery(this);

                    bootbox.dialog({
                        title: Translator.translate("Delete autofill rule?"),
                        message: Translator.translate("Are you sure you want to delete this rule?"),
                        onEscape: true,
                        buttons: {
                            cancel: {
                                label: Translator.translate("Cancel"),
                                className: "btn-default",
                                callback: function () {
                                }
                            },
                            success: {
                                label: Translator.translate("Remove autofill rule"),
                                className: "btn-primary",
                                callback: function () {
                                    // Add spinner
                                    window.attributeRules.setSpinner();
                                    jQuery.ajax({
                                        type: "GET",
                                        url: window.attributeRules._tmpRemoveBtn.prop("href"),
                                        cache: false
                                    }).success(function (data, textStatus, jqXHR) {
                                        //var status = data['status'];
                                        //var msg = data['message'];
                                        //noty({
                                        //    text: msg,
                                        //    type: status ? 'success' : 'error',
                                        //    timeout: 10000
                                        //});
                                    }).always(function () {
                                        window.attributeRules._tmpRemoveBtn = null;
                                        window.attributeRules.updateModal();
                                    });
                                }
                            }
                        }
                    }).css("top", "20px");
                });
            },

            setDataFromGrid: function () {
                this._setDataFormGridQuery();
                this.getModal().find("input[type=hidden][name=attribute_set_id]").val(this.getAttributeSetId());
                this.getModal().find("input[type=hidden][name=global]").val(this.getAllProductsFlag());
                var ids = this.getProductIds();
                if (this.getAllProductsFlag()) {
                    ids = "";// smaller post
                }
                this.getModal().find("input[type=hidden][name=product_ids]").val(ids);
            },

            _setDataFormGridQuery: function () {
                var query = jQuery.extend({}, this.getGridQuery()); // Copy
                delete query.store_id;
                delete query.attribute_set_id;

                var div = this.getForm().find("div.query-from-grid").html("");
                jQuery.map(query, function (val, i) {
                    var div = window.attributeRules.getForm().find("div.query-from-grid");
                    div.append(jQuery("<input>").prop("name", i).attr("value", val).prop("type", "hidden"));
                });
            },

            getGridQuery: function () {
                return window.grid.query;
            },

            getProductIds: function () {
                return window.grid.getSelectedIds().join();
            },

            getAllProductsFlag: function () {
                return window.grid.getCheckAll() ? 1 : 0;
            },

            getAttributeSetId: function () {
                return window.grid.baseQuery["attribute_set_id"];
            },

            attachLogicOnOpen: function () {
                jQuery("a[data-target=#showAttributeRules]").click(function () {
                    window.attributeRules.setDataFromGrid();
                    window.attributeRules._attachLogicSubmitButtonOnChange();
                });
                window.attributeRules.setDataFromGrid();
            },

            _attachLogicSubmitButtonOnChange: function () {
                var modal = window.attributeRules.getModal();
                var btn = window.attributeRules.getSubmitBtn();

                btn.closest("div.tooltip-wrapper").tooltip('destroy');

                if (!window.attributeRules.getProductIds().length) {
                    btn.closest("div.tooltip-wrapper").attr("title", btn.closest("div.tooltip-wrapper").attr("data-title-products"));
                }
                if (!modal.find("input[type=checkbox]:checked").length) {
                    btn.closest("div.tooltip-wrapper").attr("title", btn.closest("div.tooltip-wrapper").attr("data-title-rules"));
                }

                if (modal.find("input[type=checkbox]:checked").length && window.attributeRules.getProductIds().length) {
                    btn.closest("div.tooltip-wrapper").tooltip('destroy');
                    btn.attr("disabled", false);
                } else {
                    btn.closest("div.tooltip-wrapper").tooltip();
                    btn.attr("disabled", true);
                }
            },

            attachLogicSubmitButton: function () {
                // If any checkbox checked then make submit button available
                // only if on grid any products selected
                this.getModal().find("input[type=checkbox]").on("change", function () {
                    window.attributeRules._attachLogicSubmitButtonOnChange();
                });
                this._attachLogicSubmitButtonOnChange();
                // Mail logic for submit
                this.getForm().submit(function (event) {
                    // Stop form from submitting normally
                    event.preventDefault();

                    var form = event.target;

                    misc.startLoading();
                    window.attributeRules._attributeRules.send(form.serialize())
                        .always(function () {
                            misc.stopLoading();
                        });
                });
            },

            closeModal: function () {
                this.getModal().modal('hide');
            },

            openModal: function () {
                jQuery("a[data-target=#showAttributeRules]").click();
            },

            getSubmitBtn: function () {
                return this.getModal().find("button[type=submit]");
            },

            getForm: function () {
                return this.getModal().find("form").first();
            },

            attachLogicShowDetails: function () {
                this.getModal().on("shown.bs.collapse", function (e) {
                    jQuery("[data-target='#" + e.target.id + "'] .btn i").removeClass("icon-plus").addClass("icon-minus");
                });
                this.getModal().on("hidden.bs.collapse", function (e) {
                    jQuery("[data-target='#" + e.target.id + "'] .btn i").removeClass("icon-minus").addClass("icon-plus");
                });
            },

            attachLogicGroupCheckbox: function () {
                var modal = this.getModal();
                this.getModal().find("input[type=checkbox]").on("change", function (e, eventFromChildren) {
                    var selector = jQuery(e.target).attr("data-checkbox-group-target");

                    if (selector && !eventFromChildren) {
                        // If parent checked/unchecked => set checked/unchecked state for children
                        var target = jQuery(selector);
                        if (e.target.checked) {
                            target.prop('checked', true).closest('span').addClass('checked').closest('tr').addClass('checked');
                            target.trigger("change");
                        } else {
                            target.prop('checked', false).closest('span').removeClass('checked').closest('tr').removeClass('checked');
                            target.trigger("change");

                        }
                    }

                    // If all children checked/unchecked,  set checked/unchecked state for parent
                    var parentSelector = jQuery(e.target).attr("data-checkbox-group-parent");

                    if (parentSelector && parentSelector !== "#attr-select-all") {
                        var parent = jQuery(parentSelector);
                        var allChild = jQuery(parent.attr("data-checkbox-group-target"));
                        var allCheckedChild = jQuery(parent.attr("data-checkbox-group-target") + ":checked");

                        var isAllChecked = allCheckedChild.length == allChild.length;

                        if (isAllChecked) {
                            if (parent.prop('checked') != true) {
                                parent.prop('checked', true).closest('span').addClass('checked').closest('tr').addClass('checked');
                                if (!eventFromChildren) {
                                    parent.trigger("change", [true]);
                                }
                            }
                        } else {
                            if (parent.prop('checked') != false) {
                                parent.prop('checked', false).closest('span').removeClass('checked').closest('tr').removeClass('checked');
                                if (!eventFromChildren) {
                                    parent.trigger("change", [true]);
                                }
                            }
                        }

                    }


                    // Check/ uncheck all
                    var attrSelectAll = modal.find("#attr-select-all");
                    var allItemsChecked = [];

                    modal.find("input[type=checkbox]:not(#attr-select-all)").each(function (i, element) {
                        allItemsChecked.push(jQuery(element).prop('checked'));
                    });

                    if (jQuery.inArray(false, allItemsChecked) == -1) {
                        attrSelectAll.prop('checked', true).closest('span').addClass('checked');
                    } else {
                        attrSelectAll.prop('checked', false).closest('span').removeClass('checked');
                    }

                    // --- Check/ uncheck all
                });
            },

            getModal: function () {
                return jQuery('#showAttributeRules');
            }
        };

    return {
        setColumns: function (columns) {
            this.columns = columns;
            return this;
        },
        getColumns: function () {
            return this.columns;
        },
        startup: function (container) {
            var _grid = initGrid(
                this.getColumns(),
                container
            );

            window.attributeRules.init(_grid);
            window.attributeSet.init(_grid);
            window.changesHistory.init(_grid);

            // For mapping attribute process, checkbox 'save as rule' need to be disabled when
            // for multi select option delete is checked
            jQuery(document).delegate('input[type=radio][name=mode]', 'change', function () {
                var checkbox = jQuery(this).closest('form').find('.checkbox.save-as-rule');
                if (this.value == 'sub') {
                    checkbox.find('input').attr("disabled", true).prop('checked', false);
                    checkbox.css("color", "#C8C8C8").attr("disabled", true);
                } else {
                    var checked = jQuery(this).closest('form').find('.checkbox.selection').find('input');
                    if (checked.prop('checked')) {
                        checkbox.find('input').attr("disabled", false);
                        checkbox.css("color", "").attr("disabled", false);
                    }
                }
            });
            // For mapping attributes process, checkbox 'save as rule' need to be disabled when
            // checkbox 'Apply to selection' is unchecked
            jQuery(document).delegate('input[type=checkbox][name=selection]', 'change', function () {
                var checkbox = jQuery(this).closest('form').find('.checkbox.save-as-rule');
                if (!jQuery(this).prop("checked")) {
                    checkbox.find('input').attr("disabled", true).prop('checked', false);
                    checkbox.css("color", "#C8C8C8").attr("disabled", true);
                } else {
                    var radio = jQuery(this).closest('form').find('input[type=radio][name=mode]:checked');
                    if (radio.val() != 'sub') {
                        checkbox.find('input').attr("disabled", false);
                        checkbox.css("color", "").attr("disabled", false);
                    }
                }
            });

            // Adding tooltip's to grid column header
            jQuery(this.getColumns()).each(function (idx, elem) {
                if (elem.field && elem.title) {
                    jQuery(".header.field-" + elem.field).attr("title", elem.title).attr("data-tooltip-header", "true");
                }
            });
            jQuery(".dgrid-selector[role=columnheader]").attr("title", Translator.translate("Selection")).attr("data-tooltip-header", "true");
            jQuery("[data-tooltip-header=true]").tooltip({
                container: "body",
                animation: false,
                placement: "top",
                trigger: "hover",
                delay: {"show": 0, "hide": 0}
            });
            return this;
        }
    };
});