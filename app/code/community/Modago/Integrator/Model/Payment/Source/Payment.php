<?php

/**
 * Source for all payment active methods
 *
 * Class Modago_Integrator_Model_Payment_Source_Payment
 */
class Modago_Integrator_Model_Payment_Source_Payment {

	/**
	 * Cache for loaded method
	 */
	protected $_options;

	public function toOptionArray() {
		if (!$this->_options) {

			/** @var Mage_Payment_Model_Config $paymentConfig */
			$paymentConfig = Mage::getSingleton('payment/config');
			$allMethods = $paymentConfig->getActiveMethods();

			$this->_options = array();
			foreach ($allMethods as $code => $method) {
				/** @var Mage_Payment_Model_Method_Abstract $method */
				if ($method->isAvailable() && $code != 'zolagopayment') {
					$this->_options[] = array(
						"value" => $code,
						"label" => $method->getTitle()
					);
				}
			}
		}
		return $this->_options;
	}
}
