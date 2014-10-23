jQuery(function($){
	var _rma = {
		step1: $("#step-1"),
		step2: $("#step-2"),
		step3: $("#step-3"),
		newRma: $("#new-rma"),
		validation: null,
		steps: [],
		currentStep: -1, // init value
		returnReasons: [],
		unloadMessage: 'Do You really want to leave RMA process?',
        selectReturnReasonMessage: "Select return reason",
		ignoreUnload: 0,
		daysOfWeek: [],
		txtReason: "",
		txtComment: "",
		txtCarrierTime: "",
		_txtCarrierTimeFrom: "carrier_time_from",
		_txtCarrierTimeTo: "carrier_time_to",
		
		////////////////////////////////////////////////////////////////////////
		// Init steps and general
		////////////////////////////////////////////////////////////////////////
		init: function () {
			"use strict";
			this._init();
			this.go(0);
			// Fix footer
			jQuery(window).resize();
		},
		
		// Internal init
		_init: function(){
			var self = this;
			this.steps = [this.step1, this.step2, this.step3];
			var _validSettings = {
				errorPlacement: function(error, element) {
					if(element.next().is(".sbHolder")){
						error.insertAfter(element.next());
					}else {
						error.insertAfter(element);
					}
				}
			};
			
//			this.validation = this.newRma.validate(
//				Mall.validate.getOptions(_validSettings)
//			);
	
			$(window).bind('beforeunload', function() {
				if (self.currentStep>0 && !self.ignoreUnload) {
					return self.unloadMessage;
				}
			}); 
	
			this._initStep1();
            this._initStep2();
            this._initStep3();
		},
		
		// Step 1 init
		_initStep1: function(){
			var s = this.step1,
				self = this,
                returnMessage = this.selectReturnReasonMessage,
				next = s.find("button.next");


			// Style selects
            s.find("select").selectbox({
                onChange: changeCorrespondedItems
            });
		
			// Chexboxes
			var checkboxHandler = function(){
				var el = $(this);
				next[s.find(":checkbox:checked").length ? "removeClass" : "addClass"]('hidden');
				el.parents("tr").find(".condition-wrapper")
						[el.is(":checked") ? "addClass" : "removeClass"]('active')
						[el.is(":checked") ? "removeClass" : "addClass"]('inactive');
//				el.parents("tr").find(".condition-wrapper select").
//						selectbox(el.is(":checked") ? "enable" : "disable");
			};
			s.find(":checkbox").change(checkboxHandler).change();
            s.find(":checkbox").change(function(){
                var el = $(this);
                if(!el.is(":checked")){
                    var tr = el.closest("tr");
                    var select = tr.find("select");

                    select.val("").prop('selected', true);

                    //clear indicator of validation
                    tr.data("reasonselected" , 0);
                    select.selectbox("detach").selectbox({
                        onChange: changeCorrespondedItems
                    });

                }
            });
            function changeCorrespondedItems(val){
                var el = $(this);
                var tr = $(this).closest("tr");

                var checkbox = tr.find("input[type=checkbox]");
                if(val.length > 0){
                    //checkbox handler
                    checkbox.prop("checked", true).change();

                    //selectbox validation
                    el.closest("tr").data("reasonselected" , 1);

                    //clear errors
                    tr.find("span.error").fadeOut().remove();
                } else {
                    checkbox.prop("checked", false).change();

                    //selectbox validation
                    el.closest("tr").data("reasonselected" , 0);
                }

            }

			
			// Handle next click
			s.find(".next").click(function(){
                var valid = {};
                valid.result = false;
                valid.items = [];
                s.find("tr[target=list]").each(function (i, item) {

                    if ($(item).data("reasonselected") === 1) {
                        valid.result = true;
                    } else {
                        if($(item).find("input[type=checkbox]").is(":checked")){
                            valid.items.push(i);
                            valid.result = false;
                        }
                    }
                });


				if(valid.result){
					self.next();
				} else {
                    $.each(valid.items,function (i, index) {
                        var item = s.find("tr[target=list]").eq(index);
                        if(item.find(".error").length > 0){
                            item.find(".sbHolder .error")
                                .html(returnMessage);
                        } else {
                            item.find(".sbHolder")
                                .after("<span class='error'>" + returnMessage + "</span>");
                        }

                    });
                }
				return false;
			});
		},
		
        // Step 2 init
        _initStep2: function(){
            var s = this.step2,
                self = this,
                next = s.find("button.next");

            // Handle back click
            s.find(".back").click(function () {
                self.prev();
                return false;
            });

            // Handle next click
            s.find(".next").click(function(){
                var valid = true,
	                from = s.find('input[name="rma[carrier_time_from]"]').val().split(":")[0],
		            to = s.find('input[name="rma[carrier_time_to]"]').val().split(":")[0],
	                account = s.find('input[name="rma[customer_account]"]').val().replace(new RegExp('pl','gi'),"").replace(new RegExp(' ','g'),"");

	            //check if those blocks are displayed
	            if($('#pickup-address-form') && $('#pickup-date-form')) {
	            //validate if user has chosen pickup date
		            if (!s.find('input[name="rma[carrier_date]"]:checked').length) {
		                valid = false;
		                console.log("date");
	                }
	            //validate if chosen timespan is minimum 3 hours
		            if (to - from < 3) {
		                console.log("hour");
		                valid = false;
		            }
	            }

	            //validate if entered account number is correct (optional field)
	            if(account && (account.length != 26 || !$.isNumeric(account))) {
		            console.log("account");
		            valid = false
	            }

                //--validation
                if(valid){
	                self.fillRmaSummary();
                    self.next();
                }
                return false;
            });
        },
		
        // Step 3 init
        _initStep3: function(){
            var s = this.step3,
                self = this,
                next = s.find("button.next");

            // Handle back click
            s.find(".back").click(function () {
                self.prev();
                return false;
            });

            // Handle next click
            s.find(".next").click(function(){
				// Submit form
	            $(window).unbind('beforeunload');
                $('#new-rma').submit();
            });
        },

		// Step 3 functions
		_getRmaAddress: function() {
			return this.step2.find('.current-rma-address').html();
		},

		_getPickup: function() {
			var s = this.step2,
				out = {};
			out.carrier_date = s.find('input[name="rma[carrier_date]"]:checked').val();
			out.carrier_time_from = s.find('input[name="rma[carrier_time_from]"]').val();
			out.carrier_time_to = s.find('input[name="rma[carrier_time_to]"]').val();
			return out;
		},

		_getAccount: function() {
			return this.step2.find('input[name="rma[customer_account]"]').val();
		},

		_getRmaItems: function() {
			var s = this.step1,
				out = [];
			s.find('input[type=checkbox][name^="rma[items_single]"]:checked').each(function() {
				var item = jQuery(this);
				var tmp = {},
					id = item.prop('id').split("_")[2];
				tmp.img = item.parent('td').next('td').find('img').prop('src');
				tmp.desc = item.parent('td').next('td').next('td').find('.desc-holder').html();
				tmp.reason = s.find('select[name="rma[items_condition_single]['+id+'][0]"]').find(':selected').html();
				out.push(tmp);
			});
			return out;
		},

		_getRmaComment: function() {
			return this.step1.find('textarea[name="rma[comment_text]"]').val();
		},

		_getRmaSummaryData: function() {
			var out = {};
			out.address = this._getRmaAddress();
			out.pickup = this._getPickup();
			out.account = this._getAccount();
			out.items = this._getRmaItems();
			out.comment = this._getRmaComment();
			return out;
		},

		_getItemHtml: function(item) {
			return "" +
			"<tr>" +
			"   <td rowspan='2' class='summary-image'>" +
			"       <img src='" + item.img + "' />" +
			"   </td>" +
			"   <td class='summary-item-desc'>" +
			"       " + item.desc +
			"   </td>" +
			"</tr>" +
			"<tr>" +
			"   <td class='summary-reason'>" +
			"       <span class=\"bold\">" + this.txtReason + "</span><br />" +
			"       " + item.reason +
			"   </td>" +
			"</tr>";
		},

		_getCommentHtml: function(comment) {
			return "" +
			"<tr>" +
			"   <td colspan='2' class='summary-comment'>" +
			"       <p id='review-comment-text'>" +
			"           <span class='bold'>" + this.txtComment + "</span> " +
						$("<div/>").text(comment).html() +
					"</p>" +
			"   </td>" +
			"</tr>";
		},

		fillRmaSummary: function() {
			var data = this._getRmaSummaryData();
			var date = new Date(data.pickup.carrier_date);
			var month = date.getMonth() + 1;
			var day = this.daysOfWeek[date.getDay()] + " " +
					date.getDate() + "-" +
					(month < 10 ? "0" + month : month) + "-" +
					date.getFullYear(),
				pickup = $("#pickup-date-review"),
				accountFieldset = $(".customer-account-fieldset"),
				account = $("#customer-account-review"),
				items = $("#review-items").find("tbody"),
				address = $("#review-shipping-address");

			pickup.find('.pickup-day').html(day);
			pickup.find('.pickup-time').html(
				this.txtCarrierTime
					.replace(this._txtCarrierTimeFrom, data.pickup.carrier_time_from)
					.replace(this._txtCarrierTimeTo, data.pickup.carrier_time_to)
			);

			if(data.account) {
				account.html(data.account);
				accountFieldset.show();
			} else {
				account.html("");
				accountFieldset.hide();
			}

			items.html("");

			for(var i = 0; i < data.items.length; i++) {
				items.append(this._getItemHtml(data.items[i]));
			}

			if(data.comment) {
				items.append(this._getCommentHtml(data.comment));
			}

			address.html(data.address);

			return false;
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
			this.currentStep = step;
			// Fix footer
			jQuery(window).resize();

			// scroll to top
			jQuery(window).scrollTop(0);
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
		},
		
		
		////////////////////////////////////////////////////////////////////////
		// Misc
		////////////////////////////////////////////////////////////////////////
		addValidator: function(name, message, fn){
			jQuery.validator.addMethod(name, fn, message);
		},
		
		setReturnReasons: function(data){
			this.returnReasons = data;
		},
		
		setUnloadMessage: function(msg){
			this.unloadMessage = msg;
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
