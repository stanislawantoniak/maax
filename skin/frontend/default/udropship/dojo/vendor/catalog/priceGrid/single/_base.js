define([
	"dojo/_base/declare",
], function(declare){
	
	return declare(null, {
		
		_modalTemplate: "",
		_grid: null,
		_modal: null,
		_storeId: null,
		_currentRow: null,
		
		constructor: function(){
			
		},
		setGrid: function(grid){
			this._grid = grid;
		},
		getGrid: function(){
			return this._grid;
		},
		setStoreId: function(storeId){
			this._storeId= storeId;
		},
		getStoreId: function(){
			return this._storeId;
		},
		
		handleClick: function(row){
			this.handleDbClick(row);
		},
		
		handleDbClick: function(row){
			var modal = this._triggerModal();
			this._currentRow = row;
			modal.modal('show');
			this.loadContent(row.data);
		},
		
		handleSave: function(){
			var self= this;
			var grid = this.getGrid();
			var store = grid.get('store');
			// Refresh grid row
			store.get(this._currentRow.id).then(function(data){
				store.notify(data, self._currentRow.id);
			});
			this._modal.modal('hide');
		},
		
		loadContent: function(data){
			var btn = this._modal.find(".btn-primary").attr("disabled", "disabled");
			var body = this._modal.find(".modal-body");
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
			var self = this;
			if(!this._modal){
				this._modal = jQuery(this._modalTemplate);
				this._modal.find(".btn-primary").click(function(){
					self.handleSave.apply(self, arguments);
				})
			}
			return this._modal;
		}
	});
});