define([
	"dojo/_base/declare",
	"vendor/catalog/priceGrid/single/_base",
	"vendor/misc",
	"vendor/FancyObserver",
], function(declare, _base, misc, FancyObserver){

		var Updater = declare([_base], {
			_url: "/udprod/vendor_price_detail/pricemodal",
			_saveUrl: "/udprod/vendor_price_detail/pricemodalSave",
			_title: Translator.translate('Change product price {{name}}'),
			_className: "price-modal",
			
					
			handleClick: function(row){
				this.setProductId(row.data.entity_id);
				return this.inherited(arguments);
			},

			handleDbClick: function(row){
				this.setProductId(row.data.entity_id);
				return this.inherited(arguments);
			},
			
			// After load content
			_afterLoad: function(node, response){
				this.inherited(arguments);
				
				var price = jQuery("#price", node),
					numeric = jQuery(".marignPercent, .numeric", node),
					source = jQuery("#converter_price_type", node),
					margin = jQuery("#price_margin", node),
					// Composite
					rows = jQuery("table tbody tr", node),
					minRow = jQuery("table tbody tr.minimal-price", node),
					// Misc
					form = node.parents("form"),
					self = this;
			
			
				// Reforamt numeric
				numeric.each(function(){
					var el = jQuery(this),
						val = el.val();
						
					if(val===""){
						return "";
					}
					el.val(misc.number(el.val()));
				});
			
				var rowObservers = [];
				
					
				var sourceObserver = new FancyObserver(source[0], function(){
					
					var slectedOpt = jQuery(this.options[this.selectedIndex]);
					var value = slectedOpt.data('source');
					var newVal = "";
					var marginValue = misc.toNumber(margin.val());
					
					price.attr("readonly", !!value);
					rows.find(".price-deviation input").attr("readonly", !!value);
					margin.attr("disabled", !value); // margin is not required
					
					if(value){
						var src = minRow.find("td[data-source='"+value+"']");
							
						if(src.length){
							newVal = src.data('price');
							// include margin
							if(!isNaN(marginValue)){
								newVal = newVal * (1 + (marginValue/100));
							}
						}
					}else{
						newVal = slectedOpt.data('price');
					}
					
					if(newVal){
						price.val(misc.number(newVal));
					}
					
					// Recalacualte 
					rows.each(function(){
						var row = jQuery(this);
						var src = row.find("td[data-source='"+value+"']");
						var input = row.find(".price-deviation input");
						
						if(src.length && newVal){
							var rowPrice = src.data('price');
							if(rowPrice){
								if(value){
									// use caculted price
									if(!isNaN(marginValue)){
										rowPrice = rowPrice * (1 + (marginValue/100));
									}
									input.val(misc.numberEmpty(rowPrice - newVal)); 
								}else{
									// use stored price deviation
									input.val(misc.numberEmpty(rowPrice)); 
								}
							}
						}
					});
					
					priceObserver.forceUpdate();
					
				});	
					
					
				// Recalculate regular price
				var marginObserver = new FancyObserver(margin[0], function(){
					sourceObserver.forceUpdate();
				});
				
				// Recalculate all rows
				var priceObserver = new FancyObserver(price[0], function(){
					rowObservers.forEach(function(observer){
						observer.forceUpdate();
					})
				});
				
				
				// Update single row
				
				var rowHandler = function(){
					var el = jQuery(this),
						rowValue = misc.toNumber(el.val()),
						generalValue = misc.toNumber(price.val());
						
					if(isNaN(rowValue)){
						rowValue = 0;
					}
					if(isNaN(generalValue)){
						generalValue = 0;
					}
					
					var value = rowValue + generalValue;
					
					el.
						parents("tr").
						find(".effective-price").
						text(misc.currency(value))
				}
				
				rows.find(".price-deviation input").each(function(){
					rowObservers.push(new FancyObserver(this, rowHandler));
				})
				
				App.applyNumeric(); // Apply numeric plugin
				
				
				form.validate({
					submitHandler: function(){
						self.handleSave.apply(self, arguments);
						return false;
					},
					messages: {
						"converter_price_type": {
							"priceSource": Translator.translate("The converter price of product or one of child product is not available")
						}
					}
				});
				
				sourceObserver.forceUpdate(); // Fire
			}
			
			
		});
	  
	return new Updater();
});