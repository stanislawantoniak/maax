define([
	"dojo/_base/declare",
	"vendor/grid/mass/_base"
], function(declare, _base){
	
	return declare([_base], {
		_getRequestData: function(){
			var ret = this.inherited(arguments);
			return ret;
		}
	});
});