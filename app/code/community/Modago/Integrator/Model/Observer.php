<?php

/**
 * Observer model for Modago integration purposes
 *
 * Class Modago_Integrator_Model_Observer
 */
class Modago_Integrator_Model_Observer {

	/**
	 * Add custom dynamic fields into admin config
	 * see system -> configuration -> Modago -> integration -> Creating orders from Modago
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addConfigCarrierMapperFields(Varien_Event_Observer $observer) {
		/** @var Modago_Integrator_Helper_Data $helper */
		$helper = Mage::helper('modagointegrator');
		/** @var Mage_Core_Model_Config_Base $config */
		$config = $observer->getConfig();

		$sortOrder = 1000;
		foreach ($this->getAllCarriers() as $carrier) {
			/** @var Mage_Shipping_Model_Carrier_Abstract $carrier */
			$carrierCode = $carrier->getCarrierCode();
			$carrierTitle = $carrier->getConfigData('title');
			$label = $helper->__('Map %s to', $carrierTitle);
			// Must by XML
			$element = new Mage_Core_Model_Config_Element('
						<carrier_' . $carrierCode . ' translate="label">
							<label>' . $label . '</label>
							<frontend_type>select</frontend_type>
							<source_model>modagointegrator/shipping_source_modagocarrier</source_model>
							<sort_order>' . $sortOrder . '</sort_order>
							<show_in_default>1</show_in_default>
							<show_in_website>0</show_in_website>
							<show_in_store>0</show_in_store>
							<depends>
								<enabled>1</enabled>
							</depends>
						</carrier_' . $carrierCode . '>');
			/** @var Mage_Core_Model_Config_Element $adminSectionGroups */
			$adminApiFields = $config->getNode('sections/modagointegrator/groups/orders/fields');
			$adminApiFields->appendChild($element);
			$sortOrder++;
		}
		return;
	}

	/**
	 * Get carriers for mapping
	 * Vendor carrier -> Modago carrier
	 *
	 * @return array
	 */
	private function getAllCarriers() {
		/** @var Mage_Shipping_Model_Config $shippingConfig */
		$shippingConfig = Mage::getSingleton('shipping/config');
		$allCarriers = $shippingConfig->getAllCarriers();
		return $allCarriers;
	}
}
