<?php
class Zolago_Checkout_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getPaymentFromSession() {
	    return Mage::getSingleton('checkout/session')->getPayment();
    }
}