<?php

class Zolago_Checkout_GuestController extends Zolago_Checkout_Controller_Abstract {
	
	/**
	 * 1. Is logged - move onepage for logged
	 * 2. Has no guest flag - move login
	 * 3. Else display guest checkout
	 * 
	 * @return type
	 */
	public function indexAction() {
		// Customer logged in - forward to logged controller
		if($this->_getCustomerSession()->isLoggedIn()){
			return $this->_redirect("*/singlepage/index");
		}
		// No is guest checkout flag - go to login
		// Reset flag - every refresh will show login
		// Devs can remove true argument for tests
		if(!$this->_getCustomerSession()->getIsCheckout(true)){
			//return $this->_redirect("*/*/login");
		}
		parent::indexAction();
	}
	
	
	/**
	 * Do login if nesseery
	 * @return type
	 */
	public function loginAction() {
		
		// Is logged in ?
		if($this->_getCustomerSession()->isLoggedIn()){
			return $this->_redirect("checkout/singlepage/index");
		}
		
		// Set checkout context
		$this->getRequest()->setParam("is_checkout", true);
		
		// Forward to Magento login
		return $this->_forward("login", "account", "customer");
	}
	
	
	/**
	 * Set guest mode
	 */
	public function continueAction() {
		$this->_getCustomerSession()->setIsCheckout(true);
		return $this->_redirect("*/*/index");
	}

}
