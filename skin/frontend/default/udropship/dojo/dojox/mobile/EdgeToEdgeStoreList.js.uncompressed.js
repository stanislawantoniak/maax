define("dojox/mobile/EdgeToEdgeStoreList", [
	"dojo/_base/declare",
	"./EdgeToEdgeList",
	"./_StoreListMixin"
], function(declare, EdgeToEdgeList, StoreListMixin){

	// module:
	//		dojox/mobile/EdgeToEdgeStoreList

	return declare("dojox.mobile.EdgeToEdgeStoreList", [EdgeToEdgeList, StoreListMixin],{
		// summary:
		//		A dojo/store-enabled version of EdgeToEdgeList.
		// description:
		//		EdgeToEdgeStoreList is a subclass of EdgeToEdgeList which
		//		can generate ListItems according to the given dojo/store store.
	});
});
