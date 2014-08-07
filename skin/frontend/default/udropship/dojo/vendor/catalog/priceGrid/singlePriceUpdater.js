define([
	"dojo/_base/declare",
], function(declare){
	
	var _modal = null;
	
	var _modalTemplate = 
	 '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">\
		<div class="modal-dialog">\
		  <div class="modal-content">\
			<div class="modal-header">\
			  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>\
			  <h4 class="modal-title">' + 'Change product price' + '</h4>\
			</div>\
			<div class="modal-body">\
			</div>\
			<div class="modal-footer">\
			  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
			  <button type="button" class="btn btn-primary">Save changes</button>\
			</div>\
		  </div>\
		</div>\
	  </div>';
	
	var updater = {
		_grid: null,
		setGrid: function(grid){
			this._grid = grid;
		},
		getGrid: function(){
			return this._grid;
		},
		
		handleDbClick: function(row){
			var modal = this._triggerModal();
			modal.data('row', row);
			modal.modal('show');
			this.loadContent(row.data);
		},
		
		handleSave: function(){
			
		},
		
		loadContent: function(data){
			var btn = _modal.find(".btn-primary").attr("disabled", "disabled");
			var body = _modal.find(".modal-body");
			jQuery.ajax({
				url: "/udprod/vendor_price_detail/pricemodal",
				data: {id: data.entity_id},
				success: function(response){
					body.html(response);
					btn.attr("disabled", null);
				},
				error: function(){
					body.text("Some error occured. Contact admin.")
				},
				beforeSend: function(){
					body.text("Loading...")
				}
			})
		},
		
		_triggerModal: function(){
			if(!_modal){
				_modal = jQuery(_modalTemplate);
				_modal.find(".btn-primary").click(function(){
					_modal.modal('hide');
				})
			}
			return _modal;
		}
	}
	
	return updater;
	
	
});