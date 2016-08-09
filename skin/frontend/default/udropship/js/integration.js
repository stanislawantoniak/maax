
jQuery.noConflict();

(function() {
    var isBootstrapEvent = false;
    if (window.jQuery) {
        var all = jQuery('*');
        jQuery.each(['hide.bs.dropdown', 
            'hide.bs.collapse', 
            'hide.bs.modal', 
            'hide.bs.tooltip',
            'hide.bs.popover'], function(index, eventName) {
            all.on(eventName, function() {
                isBootstrapEvent = true;
            });
        });
		
    }
    var originalHide = Element.hide;
    Element.addMethods({
        hide: function(element) {
            if(isBootstrapEvent) {
                isBootstrapEvent = false;
                return element;
            }
            return originalHide(element);
        }
    });
})();

/**
 * Zolago class
 */

Zolago = Object.create(Object.prototype);

/**
 * Form integrator
 */
Zolago.formIntegrator = function(form){
	form.find(".required-entry").attr("required", "required");
	form.find(".validate-email ").addClass("email");
	form.find(".validate-digits ").addClass("number");
};
/**
 * Grid integrator
 */
Zolago.gridIntegrator = function(gridObj){

	this.gridObj = gridObj;
	
	var oldSetCheckboxChecked = gridObj.setCheckboxChecked;
	var mass = gridObj.massaction;
	var	oldCheckCheckboxes = mass ? mass.checkCheckboxes : null;

	var updateSelectCheckbox = function(checkbox){
		if(jQuery.uniform){
			jQuery.uniform.update(checkbox);
		}
		var el = checkbox.up("tr");
		if(checkbox.checked){
			el.addClassName("row-selected");
		}else{
			el.removeClassName("row-selected");
		}
	};
		
	varienGlobalEvents.attachEventHandler("gridRowClick", function(event){
		var el = $(event.currentTarget);
		if(!el.up("#"+gridObj.containerId)){
			return;
		}
		if(mass){
			var checkbox = el.select(".massaction-checkbox")[0];
			updateSelectCheckbox(checkbox);
		}
	});
	if(mass){
		mass.checkCheckboxes = function(){
			oldCheckCheckboxes.apply(mass, arguments);
			mass.getCheckboxes().each(updateSelectCheckbox);
		}
	}

	if(mass){
		gridObj.setCheckboxChecked = function(element){
			oldSetCheckboxChecked.apply(gridObj, arguments);
			updateSelectCheckbox(element);
		}
	}
	var widgets = [], buttons = [];
	
	if(mass){
		 widgets = $$(
				'#'+gridObj.containerId+' .filter input', 
				'#'+gridObj.containerId+' .filter select',
				'#'+mass.containerId+' select',
				'#'+mass.containerId+' input'
		);

		buttons = $$(
				'#'+gridObj.containerId+' button',
				'#'+mass.containerId+' button'
		);
	}else{
		 widgets = $$(
				'#'+gridObj.containerId+' .filter input', 
				'#'+gridObj.containerId+' .filter select'
		);
		buttons = $$(
				'#'+gridObj.containerId+' button'
		);
	}
	
	/// proess date fileds
	$$('#'+gridObj.containerId+' .filter .date img', 
	   '#'+gridObj.containerId+' .filter .date span').each(function(el){
		el.remove();
	});
	$$('#'+gridObj.containerId+' .filter .date input').each(function(el, i){
		var _el = jQuery(el);
			
			
			_el.parent().css({
				display: "inline-block",
				width: "49%",
				marginBottom: "0px"
			});
			_el.attr("placeholder", Translator.translate(!(i%2)? "From" : "To"));

			if(!(i%2)){
				_el.parent().css("margin-right", "2%");
			}
			_el.datepicker({firstDay: 1});
	});
	
	// Range
	$$('#'+gridObj.containerId+' .filter .range-line input').each(function(el, i){
		var _el = jQuery(el);
			_el.attr("placeholder", Translator.translate(!(i%2) ? "From" : "To"));
	});
	
	widgets.each(function(el){
		el.addClassName("form-control");
	});
	buttons.each(function(el){
		el.addClassName("btn");
		if(el.hasClassName("task")){
			el.addClassName("btn-primary btn-search");
		}
	});
	if(mass){
		mass.checkCheckboxes();
	}
};

Zolago.price = function(v){
	
        /* @todo locale... */
        var p = (Math.round(v * Math.pow(10, 2)) / Math.pow(10, 2) + "").split(".");

        if (p.length == 1) {
            p[1] = "00";
        } else if (p.length == 2 && p[1].length == 1) {
            p[1] += "0";
        }
        return p.join(",");
};

Zolago.round = function(v, pow){
	
		if(pow==undefined){
			pow=2;
		}
		
        return Math.round(v * Math.pow(10, pow)) / Math.pow(10, pow) + "";
};

Zolago.currency = function(){
	return this.price(price) + " " + global.i18n.currency;
};

Zolago.replace = function(markup, data) {
	jQuery.each(data, function(key) {
		markup = markup.replace(new RegExp("\{\{" + key + "\}\}", "g"), typeof data[key] != "undefined" ? data[key] : "");
	});
	return markup;
};

/*
 * Replace ',' to dot gor value of jQuery object update value of this object
 */
Zolago.parseForFloatWithReplace = function(obj) {
    var val = jQuery(obj).val();
    val = val.replace(",", ".");
    jQuery(obj).val(val);
    return val;
};