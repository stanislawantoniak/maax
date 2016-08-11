define([
	"dojo/_base/declare",
	"dojo/_base/lang",
	"vendor/misc"
], function(declare, lang, misc){
	
	return declare(null, {
		
		_saveUrl: "",
		_productIds: [],
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
		_hasModal: false,
		_method: null,
		_modalUrl: "",
		_className: "",
		_modal: null,
		_storeId: null,
		_currentRow: null,
		_focusedCell: null,
		
		constructor: function(grid, saveUrl){
			this._grid = grid;
			this._saveUrl = saveUrl;
		},
		getGrid: function(){
			return this._grid;
		},
		setProductIds: function(productId){
			this._productIds = productId;
		},
		getProductIds: function(){
			return this._productIds;
		},
		setFocusedCell: function(focusedCell){
			this._focusedCell = focusedCell;
		},
		getFocusedCell: function(){
			return this._focusedCell;
		},
		setStoreId: function(storeId){
			this._storeId= storeId;
		},
		getStoreId: function(){
			return this._storeId;
		},
		setMethod: function(method){
			this._method= method;
		},
		getMethod: function(){
			return this._method;
		},
		trigger: function(){
			if(this._hasModal){
				// Trigger modal
			}else{
				return this.send();
			}
		},
		send: function(requestData){
			var urlParams = this.getGrid().getUrlQuery();
			return jQuery.post(
				this._mixinUrl(this._saveUrl, urlParams), 
				lang.mixin(requestData || {}, this._getRequestData())
			).then(
				lang.hitch(this, this._saveSuccess), 
				lang.hitch(this, this._saveError)
			);
		},
		_saveSuccess: function(response){
			var grid = this.getGrid();
			var self = this;
			var column = self.getFocusedCell();
			// Make a refresh
			grid.refresh({keepScrollPosition: true}).then(function(){
				// Make a focus
				if(grid.focus && column){
					grid.focus(grid.cell(column.row.id, column.column.id));
				}
				if(grid._total==0){
					grid.clearSelection();
				}
				grid._updateHeaderCheckboxes();
			});
			// Restore selection
			if(response.global){
				grid.selectAll();
			} else if(typeof response.changed_ids != 'undefined'){
				response.changed_ids.forEach(function(id){
					grid.select(id);
				});
			} else {
				alert(Translator.translate('Server error occured. Close this window to reload page.'));
				window.location.reload(true); //force no cache
			}
			
			grid._updateHeaderCheckboxes();
		},
		_saveError: function(response){
			var responseExists = typeof response.responseJSON != 'undefined';
			var modal=jQuery('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">\
			<div class="modal-dialog"><div class="modal-content"><div class="modal-body">' 
			+ (responseExists ? response.responseJSON : Translator.translate('Server error occured. Close this window to reload page.')) +
			'</div><div class="modal-footer"><button class="btn btn-default" data-dismiss="modal"' + (!responseExists ? 'onclick="window.location.reload();"' : '') + '>'+Translator.translate('Close')+'</button></div></div></div></div>');
			modal.modal('show');
			modal.show();
		},
		_getRequestData: function(){
			var obj = {
				"product_ids": this.getProductIds(),
				"store_id": this.getStoreId()
			};
			if(this.getMethod()){
				obj.method = this.getMethod()
			}
			return obj;
		},
		// Set title of product
		_afterRender: function(row){
			
		},
		/**
		 * @param {String} base
		 * @param {String} queryPart
		 * @returns {String}
		 */
		_mixinUrl: function(base, queryPart){
			if(queryPart==""){
				return base;
			}
			if(base.indexOf("?")>-1){
				return base + "&" + queryPart;
			}
			return base + "?" + queryPart;
		}
	});
});