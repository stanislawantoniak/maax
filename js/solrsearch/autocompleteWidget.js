AjaxSolr.AutocompleteWidget = AjaxSolr.AbstractWidget.extend({
	/**
	* The init function which bind the search box into jquery autocomplete widget
	*/
	ajaxBaseUrl:null,
	init: function () {
		var self = this;
		self.Autocomplete = new Autocomplete(self.inputid, {
		    minChars:2, 
		    manager:self.manager,
		    ajaxBaseUrl:self.ajaxBaseUrl,
		    searchResultUrl:self.searchResultUrl,
		    viewAllResultText:self.viewAllResultText,
		    displayResultOfText:self.displayResultOfText,
		    displayResultOfInsteadText:self.displayResultOfInsteadText,
		    categoryText:self.categoryText,
		    viewAllCategoryText:self.viewAllCategoryText,
		    viewAllBrandsText:self.viewAllBrandsText,
		    keywordsText:self.keywordsText,
		    productText: self.productText,
			productsText: self.productsText,
			brandText: self.brandText,
			fromPriceText: self.fromPriceText,
		    showBrand: self.showBrand,
		    storeid: self.storeid,
		    storetimestamp: self.storetimestamp,
		    customergroupid: self.customergroupid,
			showBrandAttributeCode: self.showBrandAttributeCode,
			categoryLimit:self.categoryLimit,
			brandLimit:self.brandLimit,
			categoryRedirect:self.categoryRedirect,
		    displaykeywordsuggestion:self.displaykeywordsuggestion,
		    currencyPos:self.currencyPos,
		    allowFilter:self.allowFilter,
			currencySign:self.currencySign,
			currencycode:self.currencycode,
		    displayThumb:self.displayThumb,
		    searchTextPlaceHolder:self.searchTextPlaceHolder,
		    width:400,
		    boxWidth:462,
		    sideBarWidth:200,
		    deferRequestBy:100,
		    container: self.containerid
		  });
	},
	/**
	* Display the loadding image during the ajax request
	*/
	beforeRequest: function () {
		var self = this;
		//$(self.inputid).addClassName('ac_loading');
	},
	/**
	* Process json result returned from Solr server
	*/
	afterRequest: function () {		
		var self = this;
		var response = self.manager.response;
		//$(self.inputid).removeClassName('ac_loading');
		if(typeof response !== 'undefined'){
			self.Autocomplete.processResponse(response);
			//$(self.inputid).removeClassName('ac_loading');
			$(self.inputid).setStyle({background: ''});
		}		
	}
});