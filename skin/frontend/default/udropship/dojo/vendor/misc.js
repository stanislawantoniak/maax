define([
	"dojo/_base/declare",
	"dojo/_base/lang"
], function(declare, lang){
	
	var formatPrice = function(value, currency){
		if(typeof currency != "string"){
			currency = null;
		}
		currency = currency || "PLN"; 
		return formatNumber(value) + " " + currency;
	}
	
	var formatNumber = function(number){
		if(number===null){
			number = 0;
		}
		return parseFloat(number).toFixed(2).replace("\.", ",");
	}
	
	var formatPercent = function(value){
		return formatNumber(value) + "%";
	}
	
	return {
		currency: formatPrice,
		number: formatNumber,
		percent: formatPercent
	};
})