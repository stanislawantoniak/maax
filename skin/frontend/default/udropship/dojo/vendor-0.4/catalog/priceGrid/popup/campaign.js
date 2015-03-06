define([
    "dojo/_base/declare",
    "vendor/catalog/priceGrid/popup/_base",
    "vendor/misc"
], function(declare, _base, misc){

	var Updater = declare([_base], {
		_url: "/udprod/vendor_price_detail/removemodal",
        _saveUrl: "/udprod/vendor_price_detail/removemodalSave",
		_title: Translator.translate('Remove {{name}} from campaing'),
		_saveBtn: true,

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
				misc.replace(this._title, {name: row.data.name}));
            this._modal.find(".modal-footer button.btn-primary").text(Translator.translate('Remove from campaign'));
		},

        _afterLoad: function(node, response) {
            this.inherited(arguments);
            var form = node.parents("form"),
                self = this;

            App.applyNumeric(); // Apply numeric plugin
            App.uniform();

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