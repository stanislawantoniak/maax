define([
	"dojo/_base/declare",
	"vendor/catalog/priceGrid/single/_base",
], function(declare, _base){
	


	var Updater = declare([_base], {
		_url: "/udprod/vendor_price_detail/stockmodal",
		_title: Translator.translate('Stock of {{name}}'),
		_saveBtn: false,
		
		handleClick: function(row, evt){
			this.setProductId(jQuery(evt.toElement).data('product_id'));
			return this.inherited(arguments);
		},

		handleDbClick: function(row, evt){
			this.setProductId(jQuery(evt.toElement).data('product_id'));
			return this.inherited(arguments);
		},

	});
	  
	  
	
	return new Updater();
	
	
});