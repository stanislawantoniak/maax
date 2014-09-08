(function(){
	
	Mall.Checkout = function(){
      this._steps = [];
	  this._activeIndex = 0;
	  this._progressObject = null;
    };
    
	/**
	 * @param object progresObject
	 * @returns Mall.Chceckout
	 */
	Mall.Checkout.prototype.setProgressObject = function(progresObject){
		this._progressObject = progresObject;
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
		
		jQuery.each(steps, function(i){
			if(i==this._activeIndex){
				this.onLeave.apply(this);
			}
			this.active = 0;
			this.content.addClass("hidden");
		});
		
	
		currentStep.onEnter.apply(currentStep);
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
		progress.children().removeClass("current-checkout");
		progress.children(":first").addClass("current-checkout");
		
		for(var i=0; i<=this._activeIndex; i++){
			progress.addClass("step_0" + this._mapProgressIndex(i));
			progress.children("#step_0" + this._mapProgressIndex(i)).addClass("current-checkout");
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
		step.onEnable.apply(step);
		step.enabled = true;
		step.content.addClass("enabled");
		return this;
	}
	
	/**
	 * @param int|object step
	 * @returns Mall.Checkout
	 */
	Mall.Checkout.prototype.setDisable = function(step){
		if(typeof step != 'object'){
			step = this.getStep(step);
		}
		step.onDisable.apply(step);
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
	 * Prepare step object
	 * @param object config
	 * @return object
	 */
    Mall.Checkout.prototype._processStep = function(config){
		
		var self = this;
		
		var object = {
			id: config.id,
			index: this.getSteps().length,
			enabled: config.enabled || false,
			active: config.active || false,
			content: jQuery('#'+config.id),
			onPrepare: config.onPrepare || function(){},// Frame prepare
			onSubmit: config.onSubmit || function(){},	// Next clicked
			onEnter: config.onEnter || function(){},	// Frme enter
			onLeave: config.onLeave || function(){},	// Frame leave
			onEnable: config.onEnable || function(){},	// Frame enable
			onDisable: config.onDisable || function(){}	// Frame disable
			
		};
		
		// Submit nadle
		object.submit = function(){
			// Is valdidated
			if(object.onSubmit.apply(object)===false){
				return;
			}
			self.next();
		}
		
		// Trigger on prepare
		object.onPrepare.apply(object, arguments);
		
		return object;
	}

})();