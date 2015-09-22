define([
	"dojo/_base/declare",
	"dojo/_base/lang"
], function(declare, lang){
	
	var formatPrice = function(value, currency){
		if(typeof currency != "string"){
			currency = null;
		}
		currency = currency || "zł"; 
		return formatNumber(value) + " " + currency;
	};
	
	
	var formatNumber = function(number){
		if(number===null){
			number = 0;
		}
		return parseFloat(number).toFixed(2).replace("\.", ",");
	};
	
	var formatNumberEmpty = function(number){
		if(!parseFloat(number)){
			return "";
		}
		return formatNumber(number);
	};
	
	var toNumber = function(number){
		return parseFloat(number.replace(",", "."));
	};
	
	var formatPercent = function(value){
		return formatNumber(value) + "%";
	};
	
	var replace = function(markup, data){
		for(var key in data){
			if(data.hasOwnProperty(key)){
				markup = markup.replace(new RegExp("\{\{" + key + "\}\}", "g"), typeof data[key] != "undefined" ? data[key] : "");
			}
        }
        return markup;
	};
	
	
	var startLoading = function(showProgress){
		
		if(typeof showProgress === "undefined"){
			showProgress = true;
		}
		
		var loader = jQuery("#vendor-loading");
		if(!loader.length){
			loader = jQuery("<div>").attr({
				"id": "vendor-loading"
			}).click(function(){
				return false;
			}).hide().appendTo("body");
		}
		
		if(showProgress){
			loader.addClass("with-progress");
		}else{
			loader.removeClass("with-progress");
		}
		
		loader.show();
	};
	
	var stopLoading =  function(){
		jQuery("#vendor-loading").fadeOut();
	};
	
	return {
		currency: formatPrice,
		number: formatNumber,
		numberEmpty: formatNumberEmpty,
		percent: formatPercent,
		replace: replace,
		toNumber: toNumber,
		startLoading: startLoading,
		stopLoading: stopLoading
	};
});