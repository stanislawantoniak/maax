<?php
class GH_Inpost_Model_Carrier
	extends Mage_Shipping_Model_Carrier_Abstract
	implements Mage_Shipping_Model_Carrier_Interface
{
	const CODE = "ghinpost";
	protected $_code = self::CODE;
	protected $_helper;

	public function getAllowedMethods()
	{
		return array(
			'standard' => $this->_getStandardTitle() //standard is always available
		);
	}

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		/** @var Mage_Shipping_Model_Rate_Result $result */
		$result = Mage::getModel('shipping/rate_result');

		$result->append($this->_getStandardRate());

		return $result;
	}

	/**
	 * @return GH_Inpost_Helper_Data
	 */
	public function getHelper() {
		if(!$this->_helper) {
			$this->_helper = Mage::helper('ghinpost');
		}
		return $this->_helper;
	}

	protected function _getStandardRate()
	{
		/** @var Mage_Shipping_Model_Rate_Result_Method $rate */
		$rate = Mage::getModel('shipping/rate_result_method');

		$rate->setCarrier($this->_code);
		$rate->setCarrierTitle($this->getConfigData('title'));
		$rate->setMethod('large');
		$rate->setMethodTitle($this->_getStandardTitle());
		$rate->setPrice(10);
		$rate->setCost(0);

		return $rate;
	}

	protected function _getStandardTitle() {
		return $this->getHelper()->__('Standard delivery');
	}
	public function isTrackingAvailable() {
	    return (!empty(Mage::getStoreConfig('carriers/ghinpost/api'))) && Mage::getStoreConfig('carriers/ghinpost/active');
	}

}