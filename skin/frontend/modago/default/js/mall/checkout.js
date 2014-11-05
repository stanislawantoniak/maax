(function(){
	
	Mall.Checkout = function(){
		this.METHOD_GUEST    = 'guest';
		this.METHOD_REGISTER = 'register';
		this.METHOD_CUSTOMER = 'customer'

		this._steps = [];
		this._activeIndex = 0;
		this._progressObject = null;
		this._config = {};
		
		this._addressTemplate = '<dl>\
			  <dd>{{firstname}} {{lastname}}</dd>\
			  <dd>{{street}}</dd>\
			  <dd>{{postcode}} {{city}}</dd>\
			  <dd>{{telephone_caption}} {{telephone}}</dd>\
		  </dl>';

    };
    
	
	
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
	}
	
	/**
	 * @returns Mall.Checkout
	 */
	Mall.Checkout.prototype.next = function(){
		var candidateIndex = this.getActiveStepIndex() + 1;
		if(candidateIndex > this.getTotalSteps()){
			candidateIndex = this.getTotalSteps();
		}
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
	Mall.Checkout.prototype.beforePlaceOrder = function(xhr){
		console.log("Before data send");
	}
	
	/**
	 * @param object response
	 */
	Mall.Checkout.prototype.successPlaceOrder = function(response){
		console.log("Sucess data send", response);
		if(response.status==1){
			console.log(response.content)
			if(response.content.redirect){
				window.location.replace(response.content.redirect);
			}
		}else{
			alert(response.message);
		}
	}
	
	/**
	 * @param object response
	 */
	Mall.Checkout.prototype.errorPlaceOrder = function(response){
		console.log("Error data send");
	}
	
	/**
	 * @param object response
	 */
	Mall.Checkout.prototype.completePlaceOrder = function(xhr){
		console.log("After data send");
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
		proto.submit = function(){
			// Is valdidated
			if(proto.onSubmit.apply(self)===false){
				return;
			}
			
			if(proto.content.find("form").length && proto.doSave){
				var saveUrl = proto.content.find("form").attr("action");
				self.saveStepData(saveUrl, proto.collect()).then(function(response){
					if(response.status==1){
						console.log(response);
						self.next();
					}else{
						alert(response.content);
					}
				})
			}else{
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
				telephone_caption: Mall.translate.__("Pho.")
			},
			shipping = {
				telephone_caption: Mall.translate.__("Pho.")
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
			online: step.isOnlinePayment(),
			online_data: step.getOnlineData(),
		};
	}
	
	/**
	 * @param {Mall.Customer.Address} billing
	 * @param {Mall.Customer.Address} shipping
	 * @param {Object} sidebar
	 * @param {string} template
	 * @returns {jQuery}
	 */
	Mall.Checkout.prototype.prepareAddressSidebar = function(
			billing, shipping, sidebar, template){

		var addressTemplate = this._addressTemplate,
			hasInvoide = !!parseInt(billing.need_invoice),
			dataObject, self = this;

		dataObject = {
			billing: Mall.replace(addressTemplate, billing),
			shipping: Mall.replace(addressTemplate, shipping),
			sales_document: this.getSalesDocument(hasInvoide)
		};

		// Fill sidebar with data
		sidebar.html(Mall.replace(template, dataObject));

		// Show hide invoice
		sidebar.find(".invoice-data")[hasInvoide ? "show" : "hide"]();
		
		// Bind click
		sidebar.find(".prev-button-address").click(function(){
			self.go(0); // Address is always 1st step
			return false;
		});
			
		return sidebar;
	},
	
	/**
	 * @param {Object} dataObject
	 * @param {Object} sidebar
	 * @param {string} template
	 * @returns {jQuery}
	 */
	Mall.Checkout.prototype.prepareDeliverypaymentSidebar = function(
			dataObject, sidebar, template){

		var self = this,
			online = dataObject.online;
			
		
		// Fill sidebar with data
		sidebar.html(Mall.replace(template, dataObject));

		// Show hide bank field
		sidebar.find(".online-data")[online ? "show" : "hide"]();
		
		// Bind click
		sidebar.find(".prev-button-deliverypaymnet").click(function(){
			self.go(1);
			return false;
		});
			
		return sidebar;
	},
		
	/**
	 * @param {bool} isInvoice
	 * @returns {string}
	 */
	Mall.Checkout.prototype.getSalesDocument = function(isInvoice){
		return Mall.translate.__(isInvoice ? "Invoice" : "Paragon");
	}

})();