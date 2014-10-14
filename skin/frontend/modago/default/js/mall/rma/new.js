jQuery(function($){
	var _rma = {
		step1: $("#step-1"),
		step2: $("#step-2"),
		step3: $("#step-3"),
		newRma: $("#new-rma"),
		steps: [],
		currentStep: -1, // init value
		
		init: function () {
			"use strict";
			this._init();
			this.go(0);
			// Fix footer
			jQuery(window).resize();
		},
		
		////////////////////////////////////////////////////////////////////////
		// Navigation
		////////////////////////////////////////////////////////////////////////
		_init: function(){
			this.steps = [this.step1, this.step2, this.step3];
		},
		
		////////////////////////////////////////////////////////////////////////
		// Navigation
		////////////////////////////////////////////////////////////////////////
		
		next: function(){
			if(this.currentStep<this.steps.length-1){
				this.go(this.currentStep+1);
			}
		},
		prev: function(){
			if(this.currentStep>0){
				this.go(this.currentStep-1);
			}
		},
		go: function(step){
			if(this.currentStep==step){
				return this;
			}
			var self = this;
			$.each(this.steps, function(i){
				if(i==step){
					self._showStep(this);
				}else{
					self._hideStep(this);
				}
			});
			return this;
		},
		_getStep: function(step){
			if(typeof step == "object"){
				return step;
			}
			return this.steps[step];
		},
		_hideStep: function(step){
			this._getStep(step).hide().removeClass("active");
		},
		_showStep: function(step){
			this._getStep(step).show().addClass("active");
		}
/*(function() {
			var newRma = $("new-rma");
			var form = new VarienForm("new-rma");
			var oldOldSubmit = form.submit;
			var step1 = $("step-1");
			var step2 = $("step-2");
			var step3 = $("step-3");
			var steps = [step1, step2, step3];
			var currentStep = 1;
			var returnReasons = <?php echo Mage::helper('zolagorma')->getReturnReasons($_po, true); ?>;
			
			var showStep = function(step) {
				steps.each(function(el) {
					el.style.display = 'none';
				});
				currentStep = step;
				collectData();
				steps[currentStep - 1].style.display = 'block';
			}
			
			var collectData = function(){
				// Rma items
				var items = [];
				newRma.select(".rma-checkbox").each(function(item) {
					if ($(item).checked) {
						var tr = $(item).up('tr');
						items.push({
							name: tr.getAttribute('data-name'),
							reasonText: tr.select('option[selected]')[0].innerHTML
						});
					}
				});
				
				var reviewItems = $("review-items");
				
				reviewItems.innerHTML = "";
				var lp = 1;
				items.each(function(item){
					reviewItems.insert("<tr><td>"+(lp++)+"</td><td>"+item.name+"</td><td>"+item.reasonText+"</td></tr>");
				})
				
				// Comment 
				var comment = $("comment-text");
				var commentReview = $("review-comment-text");
				
				commentReview.innerHTML = "";
				if(comment.value){
					commentReview.innerHTML = "<strong><?php echo $_helper->__("Additional information");?>:</strong> " +
						comment.value;
				}
				
				// Address
				$("review-shipping-address").innerHTML = $("shipping-address").innerHTML;
				
				// Pickup date
				var dateText = "<?php echo $_helper->__("Carrier");?>",
					carrierDate = $("carrier-date");
			
					dateText += " " + escape(carrierDate.value) + "<br/>";
					dateText += "<?php echo $_helper->__("Between");?>";
					dateText += " " + $F('carrier-time-from');
					dateText += " <?php echo $_helper->__("and");?>"
					dateText += " " + $F('carrier-time-to');
					
				$("pickup-date-review").innerHTML = dateText;
				
				// Account
				$("customer-account-review").innerHTML = $F("customer-account") ? 
					$F("customer-account") : "<?php echo $_helper->__('N/A');?>";
			}

			var checkHandler = function() {
				var el = $(this);
				el.up("tr").down(".condition-wrapper").style.display = el.checked ? "block" : "none";
				validateItems();
			}
			
			var selectHandler = function() {
				var el = $(this),
					value = el.value,
					initialClass = el.className,
					initialId = el.id,
					advice,
					initialAdviceId,
					newClass,
					newAdviceId;
				
				
				if(value){
					newClass = 'must-be-available-' + value;
				}
				else{
					newClass = 'required-entry';
				}		
				
				el.removeClassName(initialClass).addClassName(newClass);
				
				//find advice
				initialClass = initialClass.replace('validation-passed', '');
				initialClass = initialClass.replace('validation-failed', '');
				initialClass = initialClass.replace(' ', '');
				initialAdviceId = 'advice-' + initialClass + '-' + initialId;
				
				advice = $(initialAdviceId);
				
				if(advice){
					advice.remove();				
				}
			}

			//custom validator
		    
		    <?php foreach($_helper->getItemConditionTitles() as $_key=>$_label):?>
		    
		    Validation.add('must-be-available-<?php echo $_key; ?>',returnReasons[<?php echo $_key; ?>].message,function(value){
		    	
		    	var currentReason = returnReasons[value];
		    	
		    	if(!currentReason){
		    		return false;
		    	}
		    	
		        return currentReason.isAvailable;
		    });
			
			<?php endforeach;?>
		        
			// Validate conditions
			var validateItems = function(e) {
				var checked = false;
				var hasItems = $("rma-has-items");
				newRma.select(".rma-checkbox").each(function(item) {
					if ($(item).checked) {
						checked = true;
					}
				});

				hasItems.value = "";
				if (checked) {
					hasItems.value = 1;
				}
			};

			// Register click
			newRma.select(".rma-checkbox").each(function(item) {
				$(item).observe('click', checkHandler);
			});
			
			newRma.select(".condition-wrapper > select option").each(function(item) {
				
				var value = item.value,
					currentReason;
				
				if(value && value != ""){
					
					currentReason = returnReasons[value];
					if(currentReason && !currentReason.isAvailable){
						
						item.text += ' (Not available)';
					}
				}
			});
			
			newRma.select(".condition-wrapper > select").each(function(item) {
				
				$(item).observe('change', selectHandler);
			});
			
			$("step-1-submit").observe("click", function() {
				if (form.validator.validate()) {
					showStep(2);
					
					//check which flow is selected
					applyFlow();
					
				}
			});

			var applyFlow = function(){
				
				var isAcknowledged = false,
					selects;
				
				selects = $$('.step-1 .form-list select');
				
				// Loop through all selects and find selected ones
				selects.each(function(item){
					
					if(item.value){
						
						if(returnReasons[item.value].flow == <?php echo Zolago_Rma_Model_Rma::FLOW_ACKNOWLEDGED; ?>){
							isAcknowledged = true;
						}
					}
						
				});
				<?php  $vendor = $_po->getVendor();?>				
				<?php  if (!Mage::helper('orbashipping/carrier_dhl')->isEnabledForVendor($vendor)): ?>
					isAcknowledged = true;
				<?php endif; ?>

				if(isAcknowledged){
			  		$('pickup-address-form').hide();
			  		$('pickup-date-form').hide();
			  		$('pickup-address-overview').hide();
			  		$('pickup-date-overview').hide();
			  		$('overview-message').hide();
				}
				else{
					$('pickup-address-form').show();
			  		$('pickup-date-form').show();
			  		$('pickup-address-overview').show();
			  		$('pickup-date-overview').show();
			  		$('overview-message').show();
				}
		  		
		  		return true;
			};
			
			$("step-2-submit").observe("click", function() {
				if (form.validator.validate()) {
					showStep(3);
				}
			});
			
			$("step-1-back").observe("click", function() {
				showStep(1)
			})
			
			$("step-2-back").observe("click", function() {
				showStep(2)
			})

			// Calendar setup
			Calendar.setup({
				inputField : 'carrier-date',
				ifFormat : '%d-%m-%Y',
				button : false,
				align : 'Bl',
				singleClick : true
			});

			// trigger chaneg
			newRma.select(".rma-checkbox").each(function(item) {
				checkHandler.bind(item)();
			});
			
			// check submit possible
			newRma.observe('submit', function(e){
				if(currentStep!=steps.length){
					Event.stop(e);
				}
			})
			
			// Check on beginig
			validateItems();
			showStep(1);
		})();*/
	};
	jQuery.extend(true, Mall, {rma: {"new": _rma}});
	Mall.rma.new.init();
});
