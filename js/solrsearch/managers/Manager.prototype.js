// $Id$

/**
 * @see http://wiki.apache.org/solr/SolJSON#JSON_specific_parameters
 * @class Manager
 * @augments AjaxSolr.AbstractManager
 */

AjaxSolr.Manager = AjaxSolr.AbstractManager.extend({
	executeRequest : function(servlet) {
		var self = this;
		console.log(this.solrUrl + servlet + '&' + this.store.string() +'&wt=json');
		//alert(this.solrUrl + servlet + '&' + this.store.string() +'&wt=json');
		self.currentRequest = new Ajax.JSONRequest(this.solrUrl + servlet + '&'+ this.store.string() + '&wt=json', {
			callbackParamName : "json.wrf",
			onCreate : function(response) {},
			onSuccess : function(response) {},
			onFailure : function(response) {},
			onComplete : function(response) {
				self.handleResponse(response.responseJSON);
			}
		});
	}
});