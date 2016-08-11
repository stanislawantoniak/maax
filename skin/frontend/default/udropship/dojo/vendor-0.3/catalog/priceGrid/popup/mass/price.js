define([
	"dojo/_base/declare",
	"vendor/catalog/priceGrid/popup/_base",
], function(declare, _base){
	
	var Updater = declare([_base], {
		_url: "/udprod/vendor_price/mass",
		_saveUrl: "/udprod/vendor_price/massSave",
		_className: "price-modal",
		
		handleDbClick: function(params){
			this.setStoreId(params.store_id);
			var modal = this._triggerModal(params);
			modal.modal('show');
			this.loadContent(params);
		},
		handleClick: function(){
			this.handleDbClick.apply(this, arguments);
		},
		getLoadData: function(inputData){
			return inputData;
		},
		handleSave: function(){
			
			var grid = this.getGrid();
			var store = grid.get('store');
			var rowUpdater = grid.get('rowUpdater');
			var button = this._modal.find(".btn-primary");
			var modal = this._modal;
			
			var data = modal.find("form").serialize();
			
			//rowUpdater.clear(id);
			button.button('loading');
			
			// make request
			
			jQuery.ajax({
				method: "post",
				data: data,
				url: this._saveUrl,
				success: function(data){
					
					var data = data.content;
					
					// Restore selection just changed prices
					grid.
						refresh({keepScrollPosition: true}).
						then(function(){
							if(data.global){
								grid.selectAll();
							}else{
								jQuery.each(data.changed_ids, function(){
									grid.select(parseInt(this));
								});
							}
						})

                    if (parseInt(data.skipped)) {
                        noty({
                            text: data.skipped_msg,
                            type: 'warning'
                        });
                    }
				},
				complete: function(){
					button.button('reset');
					modal.modal('hide');
				},
				error: function(data){
					alert(data.responseText)
				}
			});
		},
		_afterRender: function(data){
			this.inherited(arguments);
			this._modal.find("h4").text(Translator.translate("Mass price change"));
		},
		// After load content
		_afterLoad: function(node, response){
			this.inherited(arguments);

			var source = jQuery(".converterPriceType", node),
				margin = jQuery(".marignPercent", node),
				msrp = jQuery(".converterMsrpType", node),
				sourceChange = jQuery(".converterPriceTypeChange", node),
				marginChange = jQuery(".marignPercentChange", node),
				msrpSourceChange = jQuery(".converterMsrpTypeChange", node),
				form = node.parents("form"),
				btn = jQuery(".btn-primary", form),
				self = this;

		    var refreshSaveBtn = function(){
				//console.log(!jQuery(":checkbox:checked", node).length);
				btn.prop("disabled", !jQuery(":checkbox:checked", node).length);
			}
				
			// Enable disable controll

			sourceChange.change(function(){
				source.prop("disabled", !this.checked);
				refreshSaveBtn();
			});
			
			marginChange.change(function(){
				margin.prop("disabled", !this.checked);
				refreshSaveBtn();
			})	
			
			msrpSourceChange.change(function(){
				msrp.prop("disabled", !this.checked);
				refreshSaveBtn();
			});

			App.applyNumeric(); // Apply numeric plugin
			App.uniform(); // Apply uniform

			form.validate({
				submitHandler: function(){
					self.handleSave.apply(self, arguments);
					return false;
				}
			});
			
			refreshSaveBtn();
		}
	});
	  
	return new Updater();
});