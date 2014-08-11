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
			jQuery(".expando", node).html(jQuery("<div>").addClass("sub-row-loading").text("loading..."))
		},
		
		_renderSubRow: function(node, data){
			// Make rendering
			var divLeft=jQuery("<div>").addClass("p50 pull-left left");
			var divRight=jQuery("<div>").addClass("p50 pull-left right");
			var buttons = [
				{"label": "Change prices"}
			];
			
			switch(data.type_id){
				case "configurable":
					if(data.children){
						var table = jQuery("<table><tbody></tbody></table>").
								addClass("table table-condensed table-subrow"),
							tbody = table.find('tbody');
							
						tbody.append(
							jQuery("<tr>").addClass("header-row").
								append(jQuery("<td>").attr("colspan", 6).
									addClass("align-center").text("Child products")
							)
						);	
							
						data.children.forEach(function(item){
							tbody.append(
								jQuery("<tr>").addClass("header-row").
									append(jQuery("<td>").addClass("sub-checkbox")).
									append(jQuery("<td>").text(item.label)).
									append(jQuery("<td>").text("Price Variation")).
									append(jQuery("<td>").text("Availability")).
									append(jQuery("<td>").text("Stock")).
									append(jQuery("<td>").text("POS Stock"))
							)
					
							item.children.forEach(function(child){
								tbody.append(
									jQuery("<tr>").
										append(jQuery("<td>").addClass("sub-checkbox").append("<input type=\"checkbox\"/>")).
										append(jQuery("<td>").text(child.option_text)).
										append(jQuery("<td>").text(child.price)).
										append(jQuery("<td>").text(child.is_in_stock ? "Yes" : "No")).
										append(jQuery("<td>").text(parseInt(child.qty))).
										append(jQuery("<td>").append(jQuery("<a>").text("View")))
								)
							})
						});
						divLeft.append(table);
					}
				break;
				case "simple":
					divLeft.append(
						jQuery("<a>").
							addClass("sub-row-pos").
							text("View POS Stock")
					);
				break;
			}
			
			
			if(data.campaign){
				var table = jQuery("<table><tbody></tbody></table>").
						addClass("table table-condensed table-subrow"),
					tbody = table.find('tbody'),
					campaign = data.campaign;
			
				tbody.append(
					jQuery("<tr>").addClass("header-row").
						append(jQuery("<td>").attr("colspan", 8).
							addClass("align-center").
							append(jQuery("<span>").text(campaign.type)).
							append(jQuery("<span>").text(": ")).
							append(jQuery("<a>").text(campaign.name))
					)
				);
		
				tbody.append(
					jQuery("<tr>").addClass("header-row").
						append(jQuery("<td>").text("State")).
						append(jQuery("<td>").text("Date from")).
						append(jQuery("<td>").text("Date to")).
						append(jQuery("<td>").text("Price source")).
						append(jQuery("<td>").text("Margin")).
						append(jQuery("<td>").text("Camapign price")).
						append(jQuery("<td>").text("Msrp")).
						append(jQuery("<td>").text("Regular price"))
				)
		
				tbody.append(
					jQuery("<tr>").
						append(jQuery("<td>").text(campaign.status_text)).
						append(jQuery("<td>").text(campaign.date_from)).
						append(jQuery("<td>").text(campaign.date_to)).
						append(jQuery("<td>").text(campaign.price_source_id)).
						append(jQuery("<td>").text(misc.percent(campaign.price_margin))).
						append(jQuery("<td>").text(misc.currency(campaign.special_price))).
						append(jQuery("<td>").text(misc.currency(campaign.msrp))).
						append(jQuery("<td>").text(misc.currency(campaign.price)))
				)
		
				buttons.unshift({"label": "Remove from campaign"});
		
				divRight.append(table);
		
			}
			
			var buttonsDiv = jQuery("<div>").addClass("sub-row-actions")
			jQuery.each(buttons, function(){
				buttonsDiv.append(jQuery("<a>").text(this.label))
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
		}
	});
	
	return RowUpdater;
	
});