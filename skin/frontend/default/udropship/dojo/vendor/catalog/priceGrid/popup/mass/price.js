define([
	"dojo/_base/declare",
	"vendor/catalog/priceGrid/popup/_base",
], function(declare, _base){
	


	var Updater = declare(null, {
		handleDbClick: function(){
			console.log("open")
		},
		handleClick: function(){
			this.handleDbClick.apply(this, arguments);
		},
	});
	  
	  
	
	return new Updater();
	
	
});