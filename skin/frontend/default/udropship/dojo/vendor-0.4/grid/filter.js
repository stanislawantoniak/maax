define([
    "dojo/_base/lang",
	"dojo/dom-construct",
	"dojo/on",
	"put-selector/put",
	"vendor/grid/ObserverFilter"
], function(lang, domConstruct, on, put, ObserverFilter){

	var doUpdatePosition = function(grid){
		return function(e){
			var target = jQuery(e.target).parents(".dgrid-column-set"),
				gridNode = jQuery(grid.domNode),
				columnSetId,
				targetScroll;
			
			if(!target.length){
				return;
			}
			
			columnSetId = target[0].getAttribute("data-dgrid-column-set-id");
			targetScroll = jQuery(
				".dgrid-column-set-scroller.dgrid-column-set-scroller-"+columnSetId, 
				gridNode
			);
			
			// After focus browser will change te active posiotions
			// We need timer to handle it
			setTimeout(function(){
				targetScroll.prop("scrollLeft", target[0].scrollLeft);
				grid.adjustScrollLeft();
			}, 1);
		}
	};

    /**
     * This function "render" header cell on grid
     * Type is column type
     * Name is column code
     * Config is like:
     * array (
     *      valueType       => type
     *      options         => array (value => label)
     *      filterOptions   => array (array ( value => ..., label => ... ) )
     *      allowEmpty      => true
     * )
     *
     * @param string type
     * @param string name
     * @param array config
     */
	return function(type, name, config){
		type = type || "text";
		config = config || {};
		switch(type){
			case "text":
			case "textarea":
				return function(){
					var element = domConstruct.create("input", {
						"type": "text",
						"className": "text-filter"
					}),
					grid = this.grid,
					observer = new ObserverFilter(element, this.grid, name);

					on(element, 'focus',	doUpdatePosition(grid));
					on(element, 'focus',	lang.hitch(observer, observer.start));
					on(element, 'keyup',	lang.hitch(observer, observer.start));
					on(element, 'keydown',	lang.hitch(observer, observer.start));
					on(element, 'blur',		lang.hitch(observer, observer.stop));
					
					return element;
				};
			break;
			
			case "select":
			case "options":
			case "multiselect":
				return function(){
					var element = domConstruct.create("select", {
						"className": "select-filter"
					}),
					grid = this.grid,
					options = lang.clone(config.filterOptions || []);
					
					
					// Array cast
					if(!(options instanceof Array)){
						var _options = options;
						options = [];
						jQuery.each(_options, function(index){
							options.push({
								"value": index,
								"label": this
							});
						});
					}
					
					if(config.allowEmpty){
						options.unshift({
							"value":"-", 
							"label": "["+Translator.translate("empty")+"]"
						});
					}
					
					if(!config.required){
						options.unshift({"value":"", "label": ""});
					}
					
					options.forEach(function(item){
						put(element, domConstruct.create("option", {
							"value": item.value,
							"innerHTML": item.label
						}));
					});

					var observer = new ObserverFilter(element, this.grid, name);
					
					
					on(element, 'focus',	doUpdatePosition(grid));
					// Only change - do update
					on(element, 'change',	lang.hitch(observer, observer.update));
					
					return element;
				};
			break;
			case "range":
			case "datetime":
			case "number":
			case "price":
				return function(){
					var grid = this.grid;
					var wrapper = domConstruct.create("div");
					var valueType = config.valueType || "number";
					["from", "to"].forEach(function(_type){
						var element = domConstruct.create("input", {
							"type": "text",
							"placeholder": Translator.translate(_type[0].toUpperCase() + _type.slice(1)),
							"className": "range-field" + " " + "range-field-" + _type + " " + "range-field-" + valueType
						});
						
						// Add numeric widget
						if(jQuery && jQuery.fn.numeric && type!="datetime"){
							jQuery(element).numeric(config);
						}
						
						// Add calendar if needed
						if(jQuery && jQuery.fn.datepicker && type=="datetime"){
							jQuery(element).datepicker({firstDay: 1});
						}
						
						var observer = new ObserverFilter(element, grid, name + '['+_type+']', {valueType: valueType});
						
						on(element, 'focus',	doUpdatePosition(grid));
						on(element, 'focus',	lang.hitch(observer, observer.start));
						on(element, 'keyup',	lang.hitch(observer, observer.start));
						on(element, 'keydown',	lang.hitch(observer, observer.start));
						
						// add e time to observe value changed by widget
						if(type=="datetime"){
							on(element, 'blur',	lang.hitch(observer, observer.updateDelayed));
						}else{
							on(element, 'blur',	lang.hitch(observer, observer.stop));
						}
						
						put(wrapper, element);
					});

					
					return wrapper;
				};
			break;
		}
	}
});
