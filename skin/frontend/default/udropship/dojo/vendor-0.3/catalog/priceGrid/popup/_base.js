define([
	"dojo/_base/declare",
	"vendor/misc"
], function(declare, misc){
	
	return declare(null, {
		
		_modalTemplate:
			'<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">\
				<form action="" class="form-horizontal row-border">\
					<div class="modal-dialog modal-lg">\
					  <div class="modal-content">\
						<div class="modal-header">\
						  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>\
						  <h4 class="modal-title">{{title}}</h4>\
						</div>\
						<div class="modal-body">\
						</div>\
						<div class="modal-footer">\
						  <button type="button" class="btn btn-default" data-dismiss="modal">' + Translator.translate('Close')  + '</button>\
						  <button type="button submit" class="btn btn-primary" data-loading-text="' + Translator.translate("Processing...") + '">' + Translator.translate('Save changes') + '</button>\
						</div>\
					  </div>\
					</div>\
			   </form>\
			 </div>',
		_saveBtn: true,
		_grid: null,
		_className: "",
		_modal: null,
		_storeId: null,
		_currentRow: null,
		_url: "",
		_saveUrl: "",
		_productId: null,
		
		constructor: function(){
			
		},
		setGrid: function(grid){
			this._grid = grid;
		},
		getGrid: function(){
			return this._grid;
		},
		setProductId: function(productId){
			this._productId = productId;
		},
		getProductId: function(){
			return this._productId;
		},
		setStoreId: function(storeId){
			this._storeId= storeId;
		},
		getStoreId: function(){
			return this._storeId;
		},
		
		handleClick: function(row){
			this.setStoreId(row.data.store_id);
			this.handleDbClick.apply(this, arguments);
		},
		
		handleDbClick: function(row){
			this.setStoreId(row.data.store_id);
			var modal = this._triggerModal(row);
			this._currentRow = row;
			modal.modal('show');
			this.loadContent(row.data);
		},
		
		handleSave: function(){
			var grid = this.getGrid();
			var store = grid.get('store');
			var rowUpdater = grid.get('rowUpdater');
			var id = this._currentRow.id;
			var button = this._modal.find(".btn-primary");
			var modal = this._modal;
			var self = this;
			
			rowUpdater.clear(id);
			button.button('loading');
			
			// make request
			var data = modal.find("form").serialize();
			jQuery.ajax({
				method: "post",
				data: data,
				url: this._saveUrl,
				success: function(data){
					store.notify(data, id);
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
		
		getLoadData: function(){
			return {id: this.getProductId(), store_id: this.getStoreId()};
		},
		
		loadContent: function(data){
			var btn = this._modal.find(".btn-primary").prop("disabled", true);
			var body = this._modal.find(".modal-body");
			var self = this;
			jQuery.ajax({
				url: this._url,
				data: this.getLoadData(data),
				success: function(response){
					btn.prop("disabled", false);
					self._afterLoad.apply(self, [body, response]);
				},
				error: function(){
					body.text(Translator.translate("Some error occured. Contact admin."))
				},
				beforeSend: function(){
					body.text(Translator.translate("Loading..."))
				}
			})
		},
		
		_triggerModal: function(row){
			var self = this;
			if(!this._modal){
				this._modal = jQuery(this._modalTemplate).addClass(this._className);
				var btn = this._modal.find(".btn-primary");
				if(!this._saveBtn){
					btn.hide();
				}else{
					btn.show();
				}
			}
			// set tile
			this._afterRender(row);
			return this._modal;
		},
		

		// Set title of product
		_afterRender: function(row){
			
		},
		
		// Set content
		_afterLoad: function(node, response){
			node.html(response);
		}
	});
});