define([
	"dojo/_base/declare",
	"dojo/_base/lang",
], function(declare,lang){
	
	return declare(null, {
		/**
		 * @type {Object}
		 */
		
		query: {},
		/**
		 * Query soul be allwasys mixed in
		 * @type Object
		 */
		baseQuery: {},
		
		/**
		 * Query setter
		 * @param {type} query
		 * @returns {undefined}
		 */
		_setQuery: function(query){
			if(!this.get("store")){
				throw new Error("No store defined");
			}
			query = query || {};
			lang.mixin(query, this.baseQuery);
			this.set('collection', this.get('store').filter(query));
			this.query = query;
		},
		
		/**
		 * Query setter
		 * @param {type} query
		 * @returns {Object}
		 */
		_getQuery: function(){
			lang.mixin(this.query, this.baseQuery);
			return this.query;
		},
		
		/**
		 * Returns the url current selection
		 * @returns {Array|String}
		 */
		getUrlQuery: function(){
			var urlParams = [], k, query;
			/**
			 * @todo add instanceof conition - has selected ids & check all
			 */
			// Use selection and select all checked - move params to query string
			if(this.getCheckAll()){
				query = this.get("query");
				for(k in query){
					if(query.hasOwnProperty(k)){
						urlParams.push(k + "=" + encodeURIComponent(query[k]));
					}
				}
				urlParams.push("global" + "=" + "1");
			// Use selection and some records checked
			}else{
				urlParams.push("product_ids" + "=" + encodeURIComponent(this.getSelectedIds().join(",")));
				for(k in this.baseQuery){
					if(this.baseQuery.hasOwnProperty(k)){
						urlParams.push(k + "=" + encodeURIComponent(this.baseQuery[k]));
					}
				}
                query = this.get("query");
                for(k in query){
                    if(query.hasOwnProperty(k)){
                        urlParams.push(k + "=" + encodeURIComponent(query[k]));
                    }
                }
				urlParams.push("global" + "=" + "0");
			}
			return urlParams.join("&");
		
		}
	});
});

