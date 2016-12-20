<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Dotpay_Dotpay') . DS . "ProcessingController.php";

class Zolago_Dotpay_ProcessingController extends Dotpay_Dotpay_ProcessingController {
	/**
	 * Override redirect
	 */
	public function successAction() {
		  parent::successAction();
		  $this->_redirect($this->_getRedirectRoute(true),array('_secure'=>true));
	}
	/**
	 * Override redirect
	 */
	public function cancelAction() {
//		  parent::cancelAction(); // no cancel order
          Mage::getSingleton('core/session')->addError(Mage::helper('zolagopayment')->__('Payment failed'));
		  $this->_redirect($this->_getRedirectRoute(true),array('_secure'=>true));
	}

	/**
	 * @param bool $success
	 * @return string
	 */
	protected function _getRedirectRoute($success) {
		$urlArray = array(
			"checkout",
			Mage::getSingleton('customer/session')->isLoggedIn() ? "singlepage" : "guest",
			$success ? "success" : "error"
		);
		return implode("/", $urlArray);
	}
}