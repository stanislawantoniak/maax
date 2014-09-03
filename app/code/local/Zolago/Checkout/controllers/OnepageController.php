<?php

class Zolago_Checkout_OnepageController extends Zolago_Checkout_Controller_Abstract {
	
	/**
	 * Index action for logged users
	 * @return type
	 */
	public function indexAction() {
		// Not logged user - redirect to guest controller
		if(!$this->_getCustomerSession()->isLoggedIn()){
			return $this->_redirect("*/guest/login");
		}
		
		// Display checkout page for loged in
		parent::indexAction();
	}
}
