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
			
			console.log(data);
			
			// make request
//			
//			jQuery.ajax({
//				method: "post",
//				data: data,
//				url: this._saveUrl,
//				success: function(data){
//					store.notify(data, id);
//				},
//				complete: function(){
//					button.button('reset');
//					modal.modal('hide');
//				},
//				error: function(data){
//					alert(data.responseText)
//				}
//			});
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
				form = node.parents("form"),
				self = this;



			App.applyNumeric(); // Apply numeric plugin

			form.validate({
				submitHandler: function(){
					self.handleSave.apply(self, arguments);
					return false;
				}
			});
		}
	});
	  
	return new Updater();
});