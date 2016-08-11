<?php

class Modago_Integrator_Model_Payment_Zolagopayment extends Mage_Payment_Model_Method_Abstract {

	const PAYMENT_METHOD_CODE = 'zolagopayment';
	const PAYMENT_METHOD_ACTIVE_REGISTRY_KEY = 'zolagopaymentactive';

	protected $_code = self::PAYMENT_METHOD_CODE;
	protected $_isGateway = true;
	
	
	// should be compatible with parent
	public function isAvailable($quote = null) {
		$apiPaymentAvailable = Mage::registry(self::PAYMENT_METHOD_ACTIVE_REGISTRY_KEY);
		Mage::unregister(self::PAYMENT_METHOD_ACTIVE_REGISTRY_KEY);
		return is_null($apiPaymentAvailable) || !$apiPaymentAvailable ? false : true;
	}
}