<?php

/**
 * Source for all carriers from Modago
 *
 * Class Modago_Integrator_Model_Payment_Source_Carrier
 */
class Modago_Integrator_Model_Shipping_Source_Allcarriers {

	protected $_carriers;
	protected $_options;

	public function toOptionArray($isMultiselect = false) {
		if (!$this->_options) {
			foreach ($this->getAllCarriers() as $code => $carrier) {
				$this->_options[] = array(
					'value' => $code,
					'label' => $carrierTitle = Mage::getStoreConfig('carriers/' . $code . '/title')
				);
			}
			if (!$isMultiselect) {
				array_unshift($this->_options, array('value' => '', 'label' => ''));
			}
		}
		return $this->_options;
	}

	/**
	 * Get carriers for mapping
	 * Vendor carrier -> Modago carrier
	 *
	 * @return array
	 */
	public function getAllCarriers() {
		if (!$this->_carriers) {
			/** @var Mage_Shipping_Model_Config $shippingConfig */
			$shippingConfig = Mage::getSingleton('shipping/config');
			$_allCarriers = $shippingConfig->getAllCarriers();
			foreach ($_allCarriers as $code => $carrier) {
				$carrierMethods = $carrier->getAllowedMethods();
				if (!$carrierMethods) {
					continue;
				}
				$this->_carriers[$code] = $carrier;
			}
			unset($this->_carriers['zolagoshipment']);
		}
		return $this->_carriers;
	}
}
