define([
	"dojo/_base/declare",
	"vendor/grid/mass/_base"
], function(declare, _base){
	
	return declare([_base], {
		_getRequestData: function(){
			var ret = this.inherited(arguments);
			return ret;
		},
		_saveSuccess: function(response){
			this.inherited(arguments);
			if(response.message) {
				var modal = jQuery('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">\
			<div class="modal-dialog"><div class="modal-content"><div class="modal-body">'
					+ response.message +
					'</div><div class="modal-footer"><button class="btn btn-default" data-dismiss="modal">' + Translator.translate('Close') + '</button></div></div></div></div>');
				modal.modal('show');
				modal.show();
			}
		}
	});
});