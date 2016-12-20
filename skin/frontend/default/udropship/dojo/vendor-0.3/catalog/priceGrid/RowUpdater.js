define([
	"dojo/_base/declare",
	"dojo/_base/lang",
	"put-selector/put",
	"dojo/query",
	"dojo/request",
	"vendor/misc"
], function(declare, lang, put, query, request, misc){
	
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
		_canProcess: true,
		
		setCanProcess: function(canProcess){
			this._canProcess= canProcess;
		},
		getCanProcess: function(canProcess){
			return this._canProcess;
		},
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
			grid.set('rowUpdater', this);
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
			if (row.data.type_id != "simple") {
				this._set(row.data, !this._get(row.data));
				var self = this;
				query("td.field-expander", row.element).forEach(function (item) {
					item.innerHTML = self.cellRender(row.data);
				});
				// toggle state of node which was clicked
				if (this.is(row.data)) {
					this.doRowExpand(row.data, row.element);
				} else {
					this.doRowCollapse(row.data, row.element)
				}
			}
		},
		clear: function(id){
			if(id){
				delete this._cache[id];
			}else{
				this._cache = {};
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
			return this.is(item) ? this._yes : (item.type_id == "simple" ? this._not : this._can);
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
			
			// Start process
			this.process();
		},
		
		process: function(){
			
			// If has same not responsed store queries do waiting
			if(this.getCanProcess()){
				this._clearTimeout();
				this._timeout = setTimeout(lang.hitch(this, this._process), 500);
			// else wait for posibility
			}else{
				console.log("Waiting...");
				this._clearWaitTimeout();
				this._waitTimeout = setTimeout(lang.hitch(this, this.process), 1000);
			}
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
			jQuery(".expando", node).html(jQuery("<div>").
				addClass("sub-row-loading").
				text(Translator.translate("Loading...")))
		},
		
		_renderSubRow: function(node, data){
			// Make rendering
			var divLeft=jQuery("<div>").addClass("sub-row-left");
			var divRight=jQuery("<div>").addClass("sub-row-left");
			var buttons = [];
			
			// Product has no cmapaign - price editable
			if(!data.campaign){
				buttons.push({"label": Translator.translate("Change prices"), className: "signle-price-edit editable"});
			}
			
			switch(data.type_id){
				case "configurable":
					if(data.children){
						var table = jQuery("<table><tbody></tbody></table>").
								addClass("table table-condensed table-subrow"),
							tbody = table.find('tbody');
							
						tbody.append(
							jQuery("<tr>").addClass("header-row").
								append(jQuery("<td>").attr("colspan", 11).
									addClass("align-center").text(Translator.translate("Child products"))
							)
						);	
							
						data.children.forEach(function(item){
							tbody.append(
								jQuery("<tr>").addClass("header-row").
									append(jQuery("<td>").addClass("sub-checkbox")).
									append(jQuery("<td>").text(item.label)).
									append(jQuery("<td>").text(Translator.translate("SKU"))).
									append(jQuery("<td>").text(Translator.translate("Price Variation"))).
									append(jQuery("<td>").text(Translator.translate("Price update date"))).
									append(jQuery("<td>").text(Translator.translate("In stock"))).
									append(jQuery("<td>").text(Translator.translate("Stock Qty"))).
									append(jQuery("<td>").text(Translator.translate("Reservations"))).
									append(jQuery("<td>").text(Translator.translate("Stock Disp."))).
									append(jQuery("<td>").text(Translator.translate("Stock update date"))).
									append(jQuery("<td>").text(Translator.translate("POS Stock")))
							);
					
							item.children.forEach(function(child){
								tbody.append(
									jQuery("<tr>").
										append(jQuery("<td>").addClass("sub-checkbox").append(jQuery("<input/>").attr({
											"type": "checkbox",
											"disabled": "disabled"
										}))).
										append(jQuery("<td>").text(child.option_text)).
										append(jQuery("<td>").text(child.skuv)).
										append(jQuery("<td>").
											addClass("signle-price-edit" + (!data.campaign ? " editable" : "")).
											append(jQuery("<" + (!data.campaign ? "a" : "span") + ">").
											text(misc.currency(child.price)))).
										append(jQuery("<td>").text(child.children[0].update_price_date)).
										append(jQuery("<td>").text(
											Translator.translate(parseInt(child.children[0].is_in_stock) ? "Yes" : "No"))).
										append(jQuery("<td>").text(parseInt(child.children[0].all_qty))).
										append(jQuery("<td>").text(parseInt(child.children[0].reservation))).
										append(jQuery("<td>").text(parseInt(child.children[0].qty))).
										append(jQuery("<td>").text(child.children[0].update_stock_date)).										
										append(jQuery("<td>").append(jQuery("<a>").
											data("product_id", child.children[0].entity_id). // @todo can be lot of matched products
											addClass("editable signle-stock-edit").
											text(Translator.translate("View POS Stock"))))
								)
							})
						});
						divLeft.append(table);
					}
				break;
				case "simple":
					buttons.push({
						"label": Translator.translate("View POS Stock"), 
						className: "signle-stock-edit editable", 
						data: {product_id: data.entity_id}
					});
				break;
			}
			
			
			if(data.campaign){
				var table = jQuery("<table><tbody></tbody></table>").
						addClass("table table-condensed table-subrow"),
					tbody = table.find('tbody'),
					campaign = data.campaign,
					campaignHeader = jQuery(campaign.is_allowed ? "<a>" : "<span>").text(campaign.name);
			
				if(campaign.is_allowed){
					campaignHeader.attr("href", campaign.url)
				}
			
				tbody.append(
					jQuery("<tr>").addClass("header-row").
						append(jQuery("<td>").attr("colspan", 8).
							addClass("align-center").
							append(jQuery("<span>").text(Translator.translate(campaign.type_text))).
							append(jQuery("<span>").text(": ")).
							append(campaignHeader)
					)
				);
		
				tbody.append(
					jQuery("<tr>").addClass("header-row").
						append(jQuery("<td>").text(Translator.translate("Status"))).
						append(jQuery("<td>").text(Translator.translate("From"))).
						append(jQuery("<td>").text(Translator.translate("To"))).
						append(jQuery("<td>").text(Translator.translate("Price source"))).
						append(jQuery("<td>").text(Translator.translate("Margin"))).
						append(jQuery("<td>").text(Translator.translate("Camapign price"))).
						append(jQuery("<td>").text(Translator.translate("Msrp"))).
						append(jQuery("<td>").text(Translator.translate("Regular price")))
				)
		
				tbody.append(
					jQuery("<tr>").
						append(jQuery("<td>").text(Translator.translate(campaign.status_text))).
						append(jQuery("<td>").text(campaign.date_from)).
						append(jQuery("<td>").text(campaign.date_to)).
						append(jQuery("<td>").text(campaign.price_source_id_text)).
						append(jQuery("<td>").text(misc.percent(campaign.price_margin))).
						append(jQuery("<td>").text(misc.currency(campaign.special_price))).
						append(jQuery("<td>").text(misc.currency(campaign.msrp))).
						append(jQuery("<td>").text(misc.currency(campaign.price)))
				)
		
				buttons.push({
                    "label": Translator.translate("Remove from campaign"),
                    className: "campaign-product-remove editable",
                    data: {
                        campaign_regular_id: campaign.campaign_regular_id,
                        campaign_name: campaign.name,
                        product_id: data.entity_id
                    }
                });

				divRight.append(table);
		
			}
			
			var buttonsDiv = jQuery("<div>").addClass("sub-row-actions")
			jQuery.each(buttons, function(){
				buttonsDiv.append(jQuery("<a>").text(this.label).addClass(this.className).data(this.data || {}))
			});
			
			divRight.append(buttonsDiv);
			
			jQuery(".expando", node).
					html('').
					append(divLeft).
					append(divRight).
					append(jQuery("<div>").addClass("clearfix"))
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
			request("/udprod/vendor_price_detail/detail", {
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
		},
		_clearWaitTimeout: function(){
			clearTimeout(this._waitTimeout);
		},
	});
	
	return RowUpdater;
	
});