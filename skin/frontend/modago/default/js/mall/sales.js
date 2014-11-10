(function(){
	
	var _vendorContact = {
		_currentVendor: null,
		_currentOrder: null,
		_currentShipment: null,
		_formTemplateId: null,
		_currentPopup: jQuery(
			'<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">\
				<div class="modal-dialog modal-lg">\
					<div class="modal-content">\
						<div class="modal-header">\
							<button type="button" class="close" data-dismiss="modal"></button>\
							<h2 class="title_section"></h2>\
						</div>\
						<div class="modal-body"></div>\
					</div>\
				</div>\
			</div>'),

		setFormTemplateId: function(id){
			this._formTemplateId = id;
		},
		
		handleResize: function(){
			var self = _vendorContact;
			if(!self._currentVendor || !self._currentOrder){
				return;
			}
			if(!self._shoulBePopup()){
				self._removeCurrentPopup();
			}
		},
		
		handleClick: function(){
			var el = jQuery(this),
				self = _vendorContact;

			self._currentVendor = el.data('vendorId');
			self._currentOrder = el.data('orderId');
			self._currentShipment = el.data('shipmentId') || "";
			self._currentVendorName = el.data('vendorName') || "";

			var inlineForm = self._prepareInlineForm(
				self._currentVendor, 
				self._currentOrder,
				self._currentShipment
			);

			if(self._shoulBePopup()){
				self._removeCurrentPopup();
				self._preparePopup();
				self._currentPopup.modal('show');
				return false;
			}

			if(inlineForm.is(":visible")){
				inlineForm.hide();
			}else{
				inlineForm.show();
			}
			return false;
		},

		_getTemplate: function(data){
			return Mall.replace(jQuery("#"+this._formTemplateId).html(), data);
		},
		
		_shoulBePopup: function(){
			return jQuery(window).width()>740;
		},


		_removeCurrentPopup: function(){
			if(this._currentPopup.is(":visible")){
				this._currentPopup.modal("hide");
			}
		},

		_prepareInlineForm: function(vId, oId, sId){
			
			var obj = {
				"order_id": oId,
				"vendor_id": vId,
				"shipment_id": sId
			};
			var inlineForm = jQuery("#contact-vendor-" + vId + "-" + oId);
			if(inlineForm.length){
				if(inlineForm.is(":empty")){
					var content = jQuery(this._getTemplate(obj));
					this._prepareValidation(content);
					inlineForm.html(content);
					inlineForm.hide();
				}
			}
			
			return inlineForm;
		},
		
		_prepareValidation: function(obj){
			Mall.validate.init();
			obj.validate(Mall.validate._default_validation_options);
		},

		_preparePopup: function(){
			var obj = {
				"order_id": this._currentOrder,
				"vendor_id": this._currentVendor,
				"shipment_id": this._currentShipment
			};
			this._currentPopup.find('.title_section').text(
					Mall.translate.__("Contact Vendor") + 
					" " + 
					this._currentVendorName
			);
			var content = jQuery(this._getTemplate(obj));
			this._prepareValidation(content);
			this._currentPopup.find('.modal-body').html(content);
			
		}
	};
	
	// Extend mall obj
	jQuery.extend(Mall, {sales: {
			vendorContact: _vendorContact
	}});
	
})();