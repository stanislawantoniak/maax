<?php
/**
 * model for zolago carrier
 */
class Modago_Integrator_Model_Shipping_Zolagoshipment
	extends Mage_Shipping_Model_Carrier_Abstract
	implements Mage_Shipping_Model_Carrier_Interface {

	const SHIPPING_CARRIER_CODE = 'zolagoshipment';
	const SHIPPING_METHOD_CODE = 'courier';
	const SHIPPING_COST_REGISTRY_KEY = 'zolagoshipmentcost';
	const SHIPPING_ACTIVE_REGISTRY_KEY = 'zolagoshipmentactive';

	protected $_code = self::SHIPPING_CARRIER_CODE;

	/**
	 * Empyt collect
	 * @param Mage_Shipping_Model_Rate_Request $request
	 * @return type
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		if(!$this->isActive()) {
			return false;
		}

		/** @var Mage_Shipping_Model_Rate_Result $result */
		$result = Mage::getModel('shipping/rate_result');
		/** @var Mage_Shipping_Model_Rate_Result_Method $method */
		$method = Mage::getModel('shipping/rate_result_method');

		$price = (float)Mage::registry(self::SHIPPING_COST_REGISTRY_KEY);
		Mage::unregister(self::SHIPPING_COST_REGISTRY_KEY);
		$price = is_null($price) || !is_numeric($price) ? 0.00 : $price;

		$method
			->setCarrier($this->_code)
			->setCarrierTitle('Zolago shipment')
			->setMethod('courier')
			->setMethodTitle('Courier')
			->setMethodDescription('')
			->setPrice($price)
			->setCost(0);

		$result->append($method);

        return $result;
	}
	
	/**
	 * @return array
	 */
	public function getAllowedMethods() {
		return array(self::SHIPPING_METHOD_CODE => 'Delivery');
	}
	/**
	 * Always disabled
	 * @return boolean
	 */
	public function isActive() {
		$apiShippingActive = Mage::registry(self::SHIPPING_ACTIVE_REGISTRY_KEY);
		Mage::unregister(self::SHIPPING_ACTIVE_REGISTRY_KEY);
		return is_null($apiShippingActive) || !$apiShippingActive ? false : true;
	}

	/**
	 * @return string
	 */
	public static function getShippingMethodCode() {
		return self::SHIPPING_CARRIER_CODE."_".self::SHIPPING_METHOD_CODE;
	}
        
}