define([
	"dojo/_base/declare",
	"vendor/catalog/priceGrid/single/_base",
	"vendor/misc"
], function(declare, _base,misc){

		var Updater = declare([_base], {
			_url: "/udprod/vendor_price_detail/pricemodal",
			_title: Translator.translate('Change product price {{name}}'),
			_className: "price-modal",
			
			// Set title of product
			_afterRender: function(row){
				this._modal.find("h4").text(
					misc.replace(this._title, {name: row.data.name})
				);
			},
			
			// After load content
			_afterLoad: function(node, response){
				this.inherited(arguments);
				
				var price = jQuery("#price", node),
					rows = jQuery("table tbody tr", node),
					form = node.parents("form"),
					self = this;
					
				jQuery("#converter_price_type", node).change(function(){
					price.attr("disabled", !!jQuery(this).val());
				}).change();
				
				price.change(function(){
					rows.find("input").change();
				});
				
				rows.find("input").change(function(){
					var el = jQuery(this),
						rowValue = misc.toNumber(el.val()),
						generalValue = misc.toNumber(price.val());
						
					if(isNaN(rowValue)){
						rowValue = 0;
					}
					if(isNaN(generalValue)){
						generalValue = 0;
					}
					
					el.
						parents("tr").
						find(".effective-price").
						text(misc.currency(rowValue + generalValue))
				});
				
				App.applyNumeric(); // Apply numeric plugin
				
				
				form.validate({
					submitHandler: function(){
						self.handleSave.apply(self, arguments);
						return false;
					}
				});
				
				price.change();
			}
			
			
		});
	  
	return new Updater();
});