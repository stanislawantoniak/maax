
(function(){

	Mall.CheckoutGuest = function(){        
		Mall.CheckoutGuest.superclass.apply(this, arguments);
	};
	
	Mall.extend(Mall.CheckoutGuest, Mall.Checkout);

    /**
	 * How to override methods
	 */
	Mall.CheckoutGuest.prototype.init = function(){
		this.set("logged", 0);
		Mall.CheckoutGuest.superproto.init.apply(this, arguments);
	}
	
	/**
	 * Empty password = guest / Not empty = register
	 * @returns string
	 */
	Mall.CheckoutGuest.prototype.getMethod = function(){
		var addressStep = this.getStepByCode("address");
		if(addressStep && addressStep.isPasswordNotEmpty()){
			return this.METHOD_REGISTER;
		}
		return this.METHOD_GUEST;
	}
	
})();
