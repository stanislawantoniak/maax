(function(){
	
	Mall.Checkout = function(){
		this.METHOD_GUEST    = 'guest';
		this.METHOD_REGISTER = 'register';
		this.METHOD_CUSTOMER = 'customer';

		this._steps = [];
		this._activeIndex = 0;
		this._progressObject = null;
		this._config = {};
		this._addressTemplate = '<dl>\
			  <dd class="shipping">{{firstname}} {{lastname}}</dd>\
			  <dd class="company">{{company}}</dd>\
			  <dd class="billing vat_id">{{vat_id_caption}} {{vat_id}}</dd>\
			  <dd>{{street}}</dd>\
			  <dd>{{postcode}} {{city}}</dd>\
			  <dd class="country">{{country}}</dd> \
			  <dd>{{telephone_caption}} {{telephone}}</dd>\
		  </dl>';

		this.InPost = {
			allowCopyTelephone: 1,

			init: function () {
				this.attachCopyTelephoneNumber();
				this.attachValidation();
			},
			// Copy telephone functionality START
			attachCopyTelephoneNumber:  function() {
				var from1 = jQuery("#shipping_telephone").val(),
					from2 = jQuery("#account_telephone").val(),
					to = jQuery("#telephone_for_locker").val(),
					state = (to == (from1 ? from1 : from2));
				this.setAllowCopyTelephoneFlag(state);
				jQuery("#shipping_telephone, #account_telephone").on('change input keypress keydown', {self: this}, this.copyTelephoneNumber);
				jQuery("#telephone_for_locker").on('keypress keydown', {self: this}, function(event) {event.data.self.setAllowCopyTelephoneFlag(0)});
			},
			getAllowCopyTelephoneFlag: function() {
				return this.allowCopyTelephone;
			},
			setAllowCopyTelephoneFlag: function(state) {
				this.allowCopyTelephone = state;
			},
			/**
			 * Copy shipping telephone number to
			 * InPost telephone number
			 */
			copyTelephoneNumber: function (event) {
				var self = event.data.self;
				var a = jQuery(this).val();
				jQuery(this).valid();
				if (self.getAllowCopyTelephoneFlag()) {
					jQuery("#telephone_for_locker").val(a).valid();
				}
			},
			// Copy telephone functionality END

			attachValidation: function() {
				jQuery('#telephone_for_locker').parents('form').validate(Mall.validate.getOptions({
					ignore: ":hidden",
					rules: {}
				}));
			},

			// Common
			getName: function() {
				var value = jQuery("input[name='delivery_point[name]']").val();
				return value;
			},
			getTelephoneForLocker: function() {
				var value = jQuery("input[name='delivery_point[telephone]']").val();
				return value;
			},
			getStreet: function() {
				var value = jQuery("#inpost-locker-street").html();
				return value;
			},
			getBuildingNumber: function() {
				var value = jQuery("#inpost-locker-building-number").html();
				return value;
			},
			getPostcode: function() {
				var value = jQuery("#inpost-locker-postcode").html();
				return value;
			},
			getTown: function() {
				var value = jQuery("#inpost-locker-town").html();
				return value;
			},
			getLocationDescription: function() {
				var value = jQuery("#inpost-locker-location-description").html();
				return value;
			},
		};
	};

	Mall.Checkout.prototype.getInPost = function () {
		return this.InPost;
	},
		
	Mall.Checkout.prototype.getInPostData = function() {
		var data = {
			inpost_locker_street: this.getInPost().getStreet(),
			inpost_locker_building_number: this.getInPost().getBuildingNumber(),
			inpost_locker_postcode: this.getInPost().getPostcode(),
			inpost_locker_town: this.getInPost().getTown(),
			inpost_locker_location_description: this.getInPost().getLocationDescription()
		};
		return data;
	},
	
	/**
	 * @param string
	 * @param mixed
	 * @returns Mall.Chceckout
	 */
	Mall.Checkout.prototype.set = function(key, value){
		this._config[key] = value;
		return this;
	}
	
	/**
	 * @param string
	 * @returns mixed
	 */
	Mall.Checkout.prototype.get = function(key){
		return this._config[key];
	}
	
	/**
	 * @param object progresObject
	 * @returns Mall.Chceckout
	 */
	Mall.Checkout.prototype.setProgressObject = function(progresObject){
		this._progressObject = progresObject;
		return this;
	}
	
	/**
	 * 
	 * @param {type} stepIndex
	 * @returns {undefined}
	 */
    Mall.Checkout.prototype.init = function(stepIndex){
		this.InPost.init();
		this.go(stepIndex || 0);
	}
	
	/**
	 * @param object step
	 * @returns Mall.Checkout
	 */
    Mall.Checkout.prototype.addStep = function(step){
		 this._steps.push(this._processStep(step)); 
		 return this;
	}

	/**
	 * @returns Array
	 */
    Mall.Checkout.prototype.getSteps = function(){
		 return this._steps;
	}
	
	
	/**
	 * @returns string
	 */
    Mall.Checkout.prototype.getMethod = function(){
		 throw new Error("Need to be overriden");
	}
	
	
	
	
	Mall.Checkout.prototype.go = function(step){
		var self = this;
		jQuery.each(this.getSteps(), function(i){
			if(i==step){
				self.setActive(i);
			}
		});
		jQuery("html,body").scrollTop(0);
	}
	
	/**
	 * @returns Mall.Checkout
	 */
	Mall.Checkout.prototype.next = function(){
		var candidateIndex = this.getActiveStepIndex() + 1;
		if(candidateIndex > this.getTotalSteps()){
			candidateIndex = this.getTotalSteps();
		}
        jQuery('.messages i').click(); //don't show messages in next steeps
        return this.go(candidateIndex);
	}
	
	/**
	 * @returns Mall.Checkout
	 */
	Mall.Checkout.prototype.prev = function(){
		var candidateIndex = this.getActiveStepIndex() - 1;
		if(candidateIndex < 0){
			candidateIndex = 0;
		}
		return this.go(candidateIndex);
	}
	
	/**
	 * @param int stepIndex
	 * @returns Mall.Checkout
	 */
	Mall.Checkout.prototype.setActive = function(stepIndex){
		var steps = this.getSteps();
		var currentStep = steps[stepIndex];
		var self= this
		
		jQuery.each(steps, function(i){
			if(i==self._activeIndex){
				this.onLeave.apply(this, [self]);
			}
			
			/*if(i<=stepIndex && !this.enabled){
				self.setEnabled(this);
			}else if(i>stepIndex && this.enabled){
				self.setDisabled(this);
			}*/
			
			this.active = 0;
			this.content.addClass("hidden");
		});

		currentStep.onEnter.apply(currentStep, [self]);
		currentStep.content.removeClass("hidden");
		currentStep.active = 1;
		
		this._activeIndex = stepIndex;
		
		this._updateInterface();
		// Update interface here
		
		return this;
	}
	
	/**
	 * @param int step
	 * @returns {undefined}
	 */
	Mall.Checkout.prototype._updateInterface = function(){
		var progress = this._progressObject;
		
		progress.attr("class", "");
		// Enable card
		progress.addClass("step_01");
		progress.children().removeClass("current-checkout executed");
		progress.children(":first").addClass("current-checkout executed");
		
		for(var i=0; i<=this._activeIndex; i++){
			progress.addClass("step_0" + this._mapProgressIndex(i));
			var child = progress.children("#step_0" + this._mapProgressIndex(i)).
					addClass("current-checkout");
			if(i<this._activeIndex){
				child.addClass("executed");
			}
		}
	}
	
	Mall.Checkout.prototype._mapProgressIndex = function(stepIndex){
		return stepIndex + 2;
	}
	
	/**
	 * @param int|object step
	 * @returns Mall.Checkout
	 */
	Mall.Checkout.prototype.setEnabled = function(step){
		if(typeof step != 'object'){
			step = this.getStep(step);
		}
		step.onEnable.apply(step, [this]);
		step.enabled = true;
		step.content.addClass("enabled");
		return this;
	}
	
	/**
	 * @param int|object step
	 * @returns Mall.Checkout
	 */
	Mall.Checkout.prototype.setDisabled = function(step){
		if(typeof step != 'object'){
			step = this.getStep(step);
		}
		step.onDisable.apply(step, [this]);
		step.enabled = false;
		step.content.removeClass("enabled");
		return this;
	}
	
	/**
	 * @returns object
	 */
	Mall.Checkout.prototype.getActiveStep = function(){
		return this._steps[this._activeIndex];
	}
	
	/**
	 * @returns array
	 */
	Mall.Checkout.prototype.getSteps = function(){
		return this._steps;
	}
	
	/**
	 * @param index stepIndex
	 * @returns object
	 */
	Mall.Checkout.prototype.getStep = function(stepIndex){
		return this._steps[stepIndex];
	}
	
	/**
	 * @param index stepIndex
	 * @returns object
	 */
	Mall.Checkout.prototype.getStepByCode = function(code){
		var steps = this.getSteps();
		for(var i=0; i<steps.length; i++){
			//console.log(steps[i].code, code);
			if(steps[i].code == code){
				return steps[i];
			}
		}
		return null;
	}
	
	/**
	 * @returns int
	 */
	Mall.Checkout.prototype.getActiveStepIndex = function(){
		return this._activeIndex;
	}
	
	/**
	 * @returns int
	 */
	Mall.Checkout.prototype.getTotalSteps = function(){
		return this._steps.length;
	}
	
	/**
	 * @param int step
	 * @returns Boolean
	 */
	Mall.Checkout.prototype.isStepActive = function(step){
		return this._activeIndex==step;
	}
	
	/**
	 * @returns Boolean
	 */
	Mall.Checkout.prototype.placeOrder = function(){
		this._savePlaceOrder();
	}
	
	/**
	 * 
	 */
	Mall.Checkout.prototype.beforePlaceOrder = function(xhr) {
		// Show modal with sth like 'Thank you, please wait'
		var modal = jQuery("#popup-after-submit-order");
		var pMethod = jQuery("input[type=hidden][name='payment[method]']").val();
		if (pMethod == "cashondelivery" || pMethod == "banktransfer") {
			modal.find(".modal-body").addClass("one-line"); // hide txt about redirecting to payment page
		}
		modal.modal('show'); // Show modal (popup)
		if(!Mall.debug.isOn) {
			jQuery('*').css('pointer-events', 'none'); // Block all actions
		}
		Mall.Gtm.checkoutStep(Mall.Gtm.STEP_CHECKOUT_ORDER); // Send step by GTM
	};

	/**
	 * @param object response
	 */
	Mall.Checkout.prototype.successPlaceOrder = function(response){
		if(response.status==1){
			var dl = response.dataLayer;
			var redirect = response.content.redirect;
			Mall.Checkout.redirect = redirect;

			var callback =
				Mall.debug.isOn ?
					function() {
						jQuery('#popup-after-submit-order').find('.popup-spinner-wrapper').html(
							"<a href=\""+Mall.Checkout.redirect+"\" style=\"font-size:20px;line-height:1em;display:block\">Debug mode is on! Click here to continue redirecting</a>"
						);
					} :
					function() {
						if(typeof Mall.Checkout.waitForExternalGTMTags != 'undefined' && Mall.Checkout.waitForExternalGTMTags) {
							setTimeout(function() {
								window.location = Mall.Checkout.redirect;
							},Mall.Checkout.waitForExternalGTMTags);
						} else {
							window.location = Mall.Checkout.redirect;
						}
					};

			if (dl && redirect && typeof dataLayer != "undefined") {
				dl = JSON.parse(dl);
				// Pushing data from order to data layer
				dataLayer.push(dl);
				// Data layer for measuring purchases by ga

				var measuringPurchases = {
					'event': 'purchases-popup',
					'ecommerce': {
						'purchase': {
							'actionField': {
								'id': dl.transactionId,
								//'affiliation': 'Online Store',
								'revenue': dl.transactionTotal,
								'tax': dl.transactionTax,
								'shipping': dl.transactionShipping,
								'coupon': dl.transactionPromoName,
							},
							'products': dl.transactionProducts
						}
					}
				};
				dataLayer.push(measuringPurchases);
				callback();
			} else if(redirect){
				callback();
			} else {
				window.location = window.location;
			}
		} else {
			window.location = window.location;
		}
	};
	
	/**
	 * @param object response
	 */
	Mall.Checkout.prototype.errorPlaceOrder = function(response){
		//console.log("Error data send");
	}
	
	/**
	 * @param object response
	 */
	Mall.Checkout.prototype.completePlaceOrder = function(xhr){
		//console.log("After data send");
	}
	

	
	/**
	 * @returns Deffered
	 */
	Mall.Checkout.prototype._savePlaceOrder = function(){
		var self = this;
		var url = this.get("placeUrl");
		return jQuery.ajax(url, {
			method:		"post",
			data:		this.collect(),
			beforeSend:	function(){self.beforePlaceOrder.apply(self, arguments)},
			success:	function(){self.successPlaceOrder.apply(self, arguments)},
			error:		function(){self.errorPlaceOrder.apply(self, arguments)},
			complete:	function(){self.completePlaceOrder.apply(self, arguments)},
		});
	}
	
	/**
	 * Return all checkout data
	 * @returns array [{name: "", value: ""},...]
	 */
	Mall.Checkout.prototype.collect = function(){
		var data = [];
		jQuery.each(this.getSteps(), function(){
			if(typeof this.collect == "function"){
				jQuery.each(this.collect(), function(){
					data.push(this);
				})
			}
		});
		return data;
	}
	
	/**
	 * 
	 * @param {type} url
	 * @param {type} data
	 * @returns defered
	 */
	Mall.Checkout.prototype.saveStepData = function(url, data){
		return jQuery.ajax({
			"method": "POST",
			"url": url,
			"data": data
		});
	}
	
	/**
	 * Prepare step object
	 * @param object config
	 * @return object
	 */
    Mall.Checkout.prototype._processStep = function(config){
		
		var self = this;
		
		if(typeof config.id == "undefined" || typeof config.code == "undefined" ){
			throw new Error("Id or code of step undefinded!");
		}
		
		var proto = {
			index: this.getSteps().length,
			enabled: true,
			active: false,
			doSave: false,
			checkout: self,
			content: jQuery('#'+config.id),
			// step.collect() - this = set. 
			// Should returns the serialized values. this = step
			collect: function(){
				return this.content.find("form").serializeArray();
			},
			// before add to checkout object, this = step
			onPrepare: function(checkoutObject, config){},
			// before submit - validation here. this = step
			// if returns false - stop process
			onSubmit: function(checkoutObject){},
			// before step shown; this = step 
			onEnter: function(checkoutObject){},
			// after step leave; this = step
			onLeave: function(checkoutObject){},
			// when step is ready to submit; this = step [DEV]
			onEnable: function(){},
			// when step isnt ready to submit; this = step [DEV]
			onDisable: function(){}
		};
		
		// Disable action
		proto.disable = function(){
			// Is valdidated
			if(proto.onDisable.apply(self)===false){
				return;
			}
			self.setDisabled(proto);
		}
		
		// Disable action
		proto.enable = function(){
			// Is valdidated
			if(proto.onEnable.apply(self)===false){
				return;
			}
			self.setEnabled(proto);
		}
		
		// Submit action - call from 
		proto.submit = function(object){
			// Is valdidated
			if(proto.onSubmit.apply(self)===false){
				return false;
			}
			if(proto.content.find("form").length && proto.doSave){
				var saveUrl = proto.content.find("form").attr("action");
				self.saveStepData(saveUrl, proto.collect()).then(function(response){
					if(response.status==1){
						// part for GTM dataLayer
						var dl = response.dataLayer;
						if (dl && typeof dataLayer != "undefined") {
							// Pushing data (shipping/payment) to data layer
							dataLayer.push(dl);
						}
                                                self.next();
					} else {
					    alert(response.content); 
					    object.submitUnlockButton();

    					}
				})
			} else {
			    self.next();
			}
		}
		
		jQuery.extend(proto, config);
		
		// Trigger on prepare
		proto.onPrepare.apply(proto, [self, config]);
		proto.content.addClass("enabled");
		
		return proto;
	}
	
	/**
	 * @returns {Object}
	 */
	Mall.Checkout.prototype.getBillingAndShipping = function(){
		// Prepare sidebar data
		var billing = {
				telephone_caption: Mall.translate.__("Pho."),
				vat_id_caption: Mall.translate.__("VAT Id")
			},
			shipping = {
				telephone_caption: Mall.translate.__("Pho."),
				vat_id_caption: Mall.translate.__("VAT Id"),
			},
			addressBookStep = this.getStepByCode("addressbook"),
			addressStep = this.getStepByCode("address");

		// Addressbook used
		if(addressBookStep){
			var addressBook = addressBookStep.getAddressBook();
			billing = jQuery.extend(billing, addressBook.getSelectedBilling().getData());
			shipping = jQuery.extend(shipping, addressBook.getSelectedShipping().getData());
		// Regular address form used
		}else if(addressStep){
			billing = jQuery.extend(billing, addressStep.getBillingAddress());
			shipping = jQuery.extend(shipping, addressStep.getShippingAddress());
		}
		
		return {
			billing: billing,
			shipping: shipping
		};
	}
	
	/**
	 * @returns {Object}
	 */
	Mall.Checkout.prototype.getDeliveryAndPayment = function(){
		// Prepare sidebar data
		var step = this.getStepByCode("shippingpayment");
		
		return {
			carrier_name: step.getCarrierName(),
			carrier_method: step.getCarrierMethod(),
			payment_method: step.getPaymentMethod(),
	        hasProviders: step.hasProviders(),
			online_data: step.getProvidersData()
		};
	};
	Mall.Checkout.prototype.getReviewInfo = function () {
		var step = this.getStepByCode("shippingpayment");
		return {
			checkout_review_info: step.getCheckoutReviewInfo(),
		};
		
	};	
	/**
	 * @param {Mall.Customer.Address} billing
	 * @param {Mall.Customer.Address} shipping
	 * @param {Object} sidebar
	 * @param {string} template
	 * @returns {jQuery}
	 */
	Mall.Checkout.prototype.prepareAddressSidebar = function(
			billing, shipping, sidebar, template){

		var hasInvoide = !!parseInt(billing.need_invoice),
			dataObject, self = this;

		dataObject = {
			billing: this._processAddressTemplate(billing, "billing"),
			shipping: this._processAddressTemplate(shipping, "shipping"),
			sales_document: this.getSalesDocument(hasInvoide)
		};

		// Fill sidebar with data
		sidebar.html(Mall.replace(template, dataObject));

		// Show hide invoice
		sidebar.find(".invoice-data")[hasInvoide ? "show" : "hide"]();
		
		// Bind click
		sidebar.find(".prev-button-address").click(function(){
            jQuery("i:not(.popup-spinner)").removeClass('fa fa-spinner fa-spin');
            jQuery("button").prop("disabled", false);
			self.go(0); // Address is always 1st step
			jQuery(window).trigger("resize");
			return false;
		});
			
		return sidebar;
	};
	
	/**
	 * @param {Object} dataObject
	 * @param {Object} sidebar
	 * @param {string} template
	 * @returns {jQuery}
	 */
	Mall.Checkout.prototype.prepareDeliverypaymentSidebar = function(
			dataObject, sidebar, template){

		var self = this,
			hasProviders = dataObject.hasProviders;
			
		
		// Fill sidebar with data
		sidebar.html(Mall.replace(template, dataObject));

		// Show hide bank field
		sidebar.find(".online-data")[hasProviders ? "show" : "hide"]();
		
		// Bind click
		sidebar.find(".prev-button-deliverypaymnet").click(function(){
            jQuery("i:not(.popup-spinner)").removeClass('fa fa-spinner fa-spin');
            jQuery("button").prop("disabled", false);
			self.go(1);
			return false;
		});
			
		return sidebar;
	};
	Mall.Checkout.prototype.prepareReviewInfo = function (
		dataObject, textInfo, template) {
		textInfo.html(Mall.replace(template, dataObject));
		return textInfo;		
	};
	Mall.Checkout.prototype.showWarning = function (textInfo, message) {
		textInfo.html(message);
		return textInfo;
	};
	/**
	 * @param {bool} isInvoice
	 * @returns {string}
	 */
	Mall.Checkout.prototype.getSalesDocument = function(isInvoice){
		return Mall.translate.__(isInvoice ? "Invoice" : "Paragon");
	};
	
	/**
	 * @param {Object} object
	 * @param {string} type
	 * @returns {string}
	 */
	Mall.Checkout.prototype._processAddressTemplate = function(object, type){
		var typeBilling = type=="billing",
			typeShipping = type=="shipping";

		// No company name in bilingaddress - use firstname and lastname
		if(typeBilling && !object.company){
			object.company = object.firstname + " " + object.lastname;
		}
		
		var	address = jQuery(Mall.replace(this._addressTemplate, object)),
			vat_id = address.find(".vat_id"),
			company = address.find(".company"),
			billing = address.find(".billing"), 
			shipping = address.find(".shipping"),
			address;
	
		billing[typeBilling ? "show" : "hide"]();
		shipping[typeShipping ? "show" : "hide"]();
		company[object.company ? "show" : "hide"]();
		vat_id[object.vat_id ? "show" : "hide"]();
		
		
		return address.get(0).outerHTML;
	};
})();