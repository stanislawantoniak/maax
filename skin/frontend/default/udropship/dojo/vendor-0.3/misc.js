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

    var prepareQuery = function(query) {
        var _arr =jQuery.map(query, function (value, index) {
            var a = {};
            if (index.match(/.*?\[from\].*?/gi)) {
                a[index.replace(/\[from\]/, '')] = {"from" : value};
            } else if (index.match(/.*?\[to\].*?/gi)) {
                a[index.replace(/\[to\]/, '')] = {"to" : value}
            } else {
                a[index] = value;
            }
            return a;
        });

        var arr = {};
        for(var i = 0; i < _arr.length; i++) {
            arr[Object.keys(_arr[i])[0]] = _arr[i][Object.keys(_arr[i])[0]];
        }
        return arr;
    };
	
	return {
		currency: formatPrice,
		number: formatNumber,
		numberEmpty: formatNumberEmpty,
		percent: formatPercent,
		replace: replace,
		toNumber: toNumber,
        prepareQuery: prepareQuery
	};
});