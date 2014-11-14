define([
    "dojo/_base/lang",
	"dojo/dom-construct",
	"dojo/on",
	"put-selector/put",
	"vendor/grid/ObserverFilter",
], function(lang, domConstruct, on, put, ObserverFilter){

	return function(type, name, config){
		type = type || "text";
		config = config || {};
		switch(type){
			case "text":
				return function(){
					var element = domConstruct.create("input", {
						"type": "text",
						"className": "text-filter"
					}),
					observer = new ObserverFilter(element, this.grid, name);

					on(element, 'focus',	lang.hitch(observer, observer.start));
					on(element, 'keyup',	lang.hitch(observer, observer.start));
					on(element, 'keydown',	lang.hitch(observer, observer.start));
					on(element, 'blur',		lang.hitch(observer, observer.start));
					
					return element;
				}
			break;
			case "select":
				return function(){
					var element = domConstruct.create("select", {
						"className": "select-filter"
					});
							
					var options = lang.clone(config.options || []);
					
					if(!config.required){
						options.unshift({"value":"", "label": ""});
					}
					
					options.forEach(function(item){
						put(element, domConstruct.create("option", {
							"value": item.value,
							"innerHTML": item.label
						}));
					})

							
					var observer = new ObserverFilter(element, this.grid, name);

					on(element, 'change',	lang.hitch(observer, observer.start));
					
					return element;
				}
			break;
			case "range":
				return function(){
					var grid = this.grid;
					var wrapper = domConstruct.create("div");
					var valueType = config.valueType || "number";
					["from", "to"].forEach(function(type){
						var element = domConstruct.create("input", {
							"type": "text",
							"placeholder": Translator.translate(type[0].toUpperCase() + type.slice(1)),
							"className": "range-field" + " " + "range-field-" + type + " " + "range-field-" + valueType,
						});
						
						if(jQuery && jQuery.fn.numeric){
							jQuery(element).numeric(config);
						}
						
						var observer = new ObserverFilter(element, grid, name + '['+type+']', {valueType: valueType});
						
						on(element, 'focus',	lang.hitch(observer, observer.start));
						on(element, 'keyup',	lang.hitch(observer, observer.start));
						on(element, 'keydown',	lang.hitch(observer, observer.start));
						on(element, 'blur',		lang.hitch(observer, observer.start));
						
						put(wrapper, element);
					});

					
					return wrapper;
				}
			break;
		}
	}
});
