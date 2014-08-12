/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/

define("dojo/store/Cache", ["../_base/lang","../when" /*=====, "../_base/declare", "./api/Store" =====*/],
function(lang, when /*=====, declare, Store =====*/){

// module:
//		dojo/store/Cache

var Cache = function(masterStore, cachingStore, options){
	options = options || {};
	return lang.delegate(masterStore, {
		query: function(query, directives){
			var results = masterStore.query(query, directives);
			results.forEach(function(object){
				if(!options.isLoaded || options.isLoaded(object)){
					cachingStore.put(object);
				}
			});
			return results;
		},
		// look for a queryEngine in either store
		queryEngine: masterStore.queryEngine || cachingStore.queryEngine,
		get: function(id, directives){
			return when(cachingStore.get(id), function(result){
				return result || when(masterStore.get(id, directives), function(result){
					if(result){
						cachingStore.put(result, {id: id});
					}
					return result;
				});
			});
		},
		add: function(object, directives){
			return when(masterStore.add(object, directives), function(result){
				// now put result in cache
				cachingStore.add(result && typeof result == "object" ? result : object, directives);
				return result; // the result from the add should be dictated by the masterStore and be unaffected by the cachingStore
			});
		},
		put: function(object, directives){
			// first remove from the cache, so it is empty until we get a response from the master store
			cachingStore.remove((directives && directives.id) || this.getIdentity(object));
			return when(masterStore.put(object, directives), function(result){
				// now put result in cache
				cachingStore.put(result && typeof result == "object" ? result : object, directives);
				return result; // the result from the put should be dictated by the masterStore and be unaffected by the cachingStore
			});
		},
		remove: function(id, directives){
			return when(masterStore.remove(id, directives), function(result){
				return cachingStore.remove(id, directives);
			});
		},
		evict: function(id){
			return cachingStore.remove(id);
		}
	});
};
lang.setObject("dojo.store.Cache", Cache);

/*=====
var __CacheArgs = {
	// summary:
	//		These are additional options for how caching is handled.
	// isLoaded: Function?
	//		This is a function that will be called for each item in a query response to determine
	//		if it is cacheable. If isLoaded returns true, the item will be cached, otherwise it
	//		will not be cached. If isLoaded is not provided, all items will be cached.
};

Cache = declare(Store, {
	// summary:
	//		The Cache store wrapper takes a master store and a caching store,
	//		caches data from the master into the caching store for faster
	//		lookup. Normally one would use a memory store for the caching
	//		store and a server store like JsonRest for the master store.
	// example:
	//	|	var master = new Memory(data);
	//	|	var cacher = new Memory();
	//	|	var store = new Cache(master, cacher);
	//
	constructor: function(masterStore, cachingStore, options){
		// masterStore:
		//		This is the authoritative store, all uncached requests or non-safe requests will
		//		be made against this store.
		// cachingStore:
		//		This is the caching store that will be used to store responses for quick access.
		//		Typically this should be a local store.
		// options: __CacheArgs?
		//		These are additional options for how caching is handled.
	},
	query: function(query, directives){
		// summary:
		//		Query the underlying master store and cache any results.
		// query: Object|String
		//		The object or string containing query information. Dependent on the query engine used.
		// directives: dojo/store/api/Store.QueryOptions?
		//		An optional keyword arguments object with additional parameters describing the query.
		// returns: dojo/store/api/Store.QueryResults
		//		A QueryResults object that can be used to iterate over.
	},
	get: function(id, directives){
		// summary:
		//		Get the object with the specific id.
		// id: Number
		//		The identifier for the object in question.
		// directives: Object?
		//		Any additional parameters needed to describe how the get should be performed.
		// returns: dojo/store/api/Store.QueryResults
		//		A QueryResults object.
	},
	add: function(object, directives){
		// summary:
		//		Add the given object to the store.
		// object: Object
		//		The object to add to the store.
		// directives: dojo/store/api/Store.AddOptions?
		//		Any additional parameters needed to describe how the add should be performed.
		// returns: Number
		//		The new id for the object.
	},
	put: function(object, directives){
		// summary:
		//		Put the object into the store (similar to an HTTP PUT).
		// object: Object
		//		The object to put to the store.
		// directives: dojo/store/api/Store.PutDirectives?
		//		Any additional parameters needed to describe how the put should be performed.
		// returns: Number
		//		The new id for the object.
	},
	remove: function(id){
		// summary:
		//		Remove the object with the specific id.
		// id: Number
		//		The identifier for the object in question.
	},
	evict: function(id){
		// summary:
		//		Remove the object with the given id from the underlying caching store.
		// id: Number
		//		The identifier for the object in question.
	}
});
=====*/

return Cache;
});


//>>built
/*
define("dojo/store/Cache",["../_base/lang","../when"],function(_1,_2){
var _3=function(_4,_5,_6){
_6=_6||{};
return _1.delegate(_4,{query:function(_7,_8){
var _9=_4.query(_7,_8);
_9.forEach(function(_a){
if(!_6.isLoaded||_6.isLoaded(_a)){
_5.put(_a);
}
});
return _9;
},queryEngine:_4.queryEngine||_5.queryEngine,get:function(id,_b){
return _2(_5.get(id),function(_c){
return _c||_2(_4.get(id,_b),function(_d){
if(_d){
_5.put(_d,{id:id});
}
return _d;
});
});
},add:function(_e,_f){
return _2(_4.add(_e,_f),function(_10){
_5.add(_10&&typeof _10=="object"?_10:_e,_f);
return _10;
});
},put:function(_11,_12){
_5.remove((_12&&_12.id)||this.getIdentity(_11));
return _2(_4.put(_11,_12),function(_13){
_5.put(_13&&typeof _13=="object"?_13:_11,_12);
return _13;
});
},remove:function(id,_14){
return _2(_4.remove(id,_14),function(_15){
return _5.remove(id,_14);
});
},evict:function(id){
return _5.remove(id);
}});
};
_1.setObject("dojo.store.Cache",_3);
return _3;
});
*/

