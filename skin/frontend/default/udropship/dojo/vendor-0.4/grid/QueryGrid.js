define([
	"dojo/_base/declare",
], function(declare){
	
	return declare(null, {
		query: {},
		_setQuery: function(query){
			if(!this.get("store")){
				throw new Error("No store defined");
			}
			query = query || {};
			this.set('collection', this.get('store').filter(query));
			this.query = query;
		}
	});
});

