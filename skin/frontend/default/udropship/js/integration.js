
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
            all.on(eventName, function( event ) {
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
 * Grid integrator
 */
Zolago.gridIntegrator = function(gridObj){

	this.gridObj = gridObj;
	
	var oldSetCheckboxChecked = gridObj.setCheckboxChecked;
	var	oldCheckCheckboxes = gridObj.massaction.checkCheckboxes;

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
	}
		
	varienGlobalEvents.attachEventHandler("gridRowClick", function(event){
		var el = $(event.currentTarget);
		if(!el.up("#"+gridObj.containerId)){
			return;
		}
		var checkbox = el.select(".massaction-checkbox")[0];
		updateSelectCheckbox(checkbox);
	});
	gridObj.massaction.checkCheckboxes = function(){
		oldCheckCheckboxes.apply(gridObj.massaction, arguments);
		gridObj.massaction.getCheckboxes().each(updateSelectCheckbox);
	}

	gridObj.setCheckboxChecked = function(element){
		oldSetCheckboxChecked.apply(gridObj, arguments);
		updateSelectCheckbox(element);
	}
	
	var widgets = $$(
			'#'+gridObj.containerId+' .filter input', 
			'#'+gridObj.containerId+' .filter select',
			'#'+gridObj.massaction.containerId+' select',
			'#'+gridObj.massaction.containerId+' input'
	);
	var buttons = $$(
			'#'+gridObj.massaction.containerId+' button'
	);
	widgets.each(function(el){
		el.addClassName("form-control");
	});
	buttons.each(function(el){
		el.addClassName("btn");
	});
	
	gridObj.massaction.checkCheckboxes();
}
console.log(Zolago);