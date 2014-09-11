
(function(){

	Mall.CheckoutLogged = function(){        
		Mall.CheckoutLogged.superclass.apply(this, arguments);
	};
	
	Mall.extend(Mall.CheckoutLogged, Mall.Checkout);

    /**
	 * How to override methods
	 */
	Mall.CheckoutLogged.prototype.init = function(){
		this.set("logged", 1);
		Mall.CheckoutLogged.superproto.init.apply(this, arguments);
	}
	
	/**
	 * @returns string
	 */
	Mall.CheckoutLogged.prototype.getMethod = function(){
		  return this.METHOD_CUSTOMER;
	}
	
})();
