define([
	"dojo/_base/declare",
	"vendor/catalog/priceGrid/popup/_base",
	"vendor/misc"
], function(declare, _base, misc){
	


	var Updater = declare([_base], {
		_url: "/udprod/vendor_price_detail/stockmodal",
		_title: Translator.translate('Stock of {{name}}'),
		_saveBtn: false,
		
		handleClick: function(row, evt){
			this.setProductId(jQuery(evt.toElement || evt.target).data('product_id'));
			return this.inherited(arguments);
		},

		handleDbClick: function(row, evt){
			this.setProductId(jQuery(evt.toElement || evt.target).data('product_id'));
			return this.inherited(arguments);
		},
		
		_afterRender: function(row){
			this.inherited(arguments);
			this._modal.find("h4").text(
				misc.replace(this._title, {name: row.data.name})
			);
		},

	});
	  
	  
	
	return new Updater();
	
	
});