
(function(){

	Mall.CheckoutGuest = function(){        
		Mall.CheckoutGuest.superclass.apply(this, arguments);
	};
	
	Mall.extend(Mall.CheckoutGuest, Mall.Checkout);

    /**
	 * How to override methods
	 */
	Mall.CheckoutGuest.prototype.init = function(){
		Mall.CheckoutGuest.superproto.init.apply(this, arguments);
	}
	
})();
