define([
	"dojo/_base/declare",
	"vendor/catalog/priceGrid/single/_base",
], function(declare, _base){
	


	  var Updater = declare([_base], {
		  _modalTemplate:
			'<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">\
			   <div class="modal-dialog">\
				 <div class="modal-content">\
				   <div class="modal-header">\
					 <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>\
					 <h4 class="modal-title">' + Translator.translate('Stock') + '</h4>\
				   </div>\
				   <div class="modal-body">\
				   </div>\
				   <div class="modal-footer">\
					 <button type="button" class="btn btn-default" data-dismiss="modal">' + Translator.translate('Close')  + '</button>\
					 <button type="button" class="btn btn-primary">' + Translator.translate('Save changes') + '</button>\
				   </div>\
				 </div>\
			   </div>\
			 </div>'
	  });
	  
	  
	
	return new Updater();
	
	
});