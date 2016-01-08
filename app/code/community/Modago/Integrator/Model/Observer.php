<?php
/**
 * Observer model for Modago integration purposes
 *
 * Class Modago_Integrator_Model_Observer
 */
class Modago_Integrator_Model_Observer {


    public function send_track_info($observer) {
        $track = $observer->getEvent()->getTrack();
        $order = $track->getShipment()->getOrder();
        $orderId = $order->getData('modago_order_id');
        if (!empty($orderId)) {
            $client = Mage::getModel('modagointegrator/soap_client');
            $key = Mage::helper('modagointegrator/api')->getKey($client);
            if ($key) {
                $dateShipped = $track->getCreatedAt();
                $trackNumber = $track->getTrackNumber();
                $carrierCode = $track->getCarrierCode();
                $carrier = Mage::helper('modagointegrator/api')->getCarrier($carrierCode);
				if (empty($carrier)) {
					$message = Mage::helper('modagointegrator')->__('Modago order %s tracking info cannot be saved because there is no carrier mapping for %s',$orderId, $carrierCode);
					Mage::helper('modagointegrator/api')->log($message);
					return; // aborting
				}
                $ret = $client->setOrderShipment($key,$orderId,$dateShipped,$carrier,$trackNumber);
                if (empty($ret->status)) { // error
                    if (!empty($ret->message)) {
						Mage::helper('modagointegrator/api')->log($ret->message);
                    }
                }
            } else {
                $message = Mage::helper('modagointegrator')->__('Modago order %s tracking info cannot be saved',$orderId);
				Mage::helper('modagointegrator/api')->log($message);
            }
        }
    }    

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
