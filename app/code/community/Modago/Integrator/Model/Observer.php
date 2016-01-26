<?php
/**
 * Observer model for Modago integration purposes
 *
 * Class Modago_Integrator_Model_Observer
 */
class Modago_Integrator_Model_Observer {



    /**
     * check if order is changed
     *
     * @param Varien_Event_Observer $observer
     */
    public function check_order_changes($observer) {
        $helperApi = Mage::helper('modagointegrator/api');
        /** @var Modago_Integrator_Helper_Api $helperApi */

        if ($helperApi->isEnabled()) {
            $registryKey = Modago_Integrator_Model_Payment_Zolagopayment::PAYMENT_METHOD_ACTIVE_REGISTRY_KEY;
            Mage::unregister($registryKey, true);
            Mage::register($registryKey, true);
            // set order as collected
            $api = Mage::getModel('modagointegrator/api');

            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $orderId = $order->getData('modago_order_id');
            $incrementId = $order->getData('increment_id');
            if ($orderId) {
                if ($helperApi->getBlockShipping()) {
                    $ret = $api->getChangeOrderMessage($orderId);
                    if (!empty($ret->list) && !empty($ret->list->message)) {
                        $message = Mage::helper('modagointegrator')->__('Error: Order %s (%s) was changed', $incrementId, $orderId);
                        $helperApi->log($message);
                        Mage::throwException(Mage::helper('modagointegrator')->__('Cannot save shipment. Order was changed on Modago.pl.'));
                    }
                }
                $api->setOrderAsCollected($orderId);
            }
        }

    }
    /**
     * save tracking info in modago api
     *
     * @param Varien_Event_Observer $observer
     */

    public function send_track_info($observer) {
        /** @var Modago_Integrator_Helper_Api $helperApi */
        $helperApi = Mage::helper('modagointegrator/api');
        /** @var Modago_Integrator_Helper_Data $helper */
        $helper = Mage::helper('modagointegrator');
        if ($helperApi->isEnabled()) {
            $track = $observer->getEvent()->getTrack();
            $order = $track->getShipment()->getOrder();
            $orderId = $order->getData('modago_order_id');
            if (!empty($orderId)) {
                /** @var Modago_Integrator_Model_Soap_Client $client */
                $client = Mage::getModel('modagointegrator/soap_client');
                $key = $helperApi->getKey($client);
                if ($key != -1) {
                    $dateShipped = $track->getCreatedAt();
                    $trackNumber = $track->getTrackNumber();
                    $carrierCode = $track->getCarrierCode();
                    $carrier = $helperApi->getCarrier($carrierCode);
                    if (empty($carrier)) {
                        $message = $helper->__('Error: Modago order %s tracking info cannot be saved because there is no carrier mapping for %s', $orderId, $carrierCode);
                        $helperApi->log($message);
                        Mage::throwException($message);
                    }

                    $ret = $client->setOrderShipment($key, $orderId, $dateShipped, $carrier, $trackNumber);
                    if (empty($ret->status)) { // no answer or error

                        if (!empty($ret->message)) {
                            $message = $helper->__('Error: sending track info to Modago API fail (%s)', $helperApi->translate($ret->message));
                        } else {
                            $message = $helper->__('Error: no response from API server');
                        }
                        $helperApi->log($message);
                        Mage::throwException($message);
                    } else {
                        $message = $helper->__('Success: Modago order %s tracking send', $orderId);
                    }
                } else {
                    $message = $helper->__('Error: Modago order %s tracking info cannot be saved', $orderId);
                    $helperApi->log($message);
                    Mage::throwException($message);
                }
                $helperApi->log($message);
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
            // Must by XML
            $element = new Mage_Core_Model_Config_Element('
                    <carrier_' . $carrierCode . ' translate="label">
                    <label>' . $carrierTitle . '</label>
                    <frontend_type>select</frontend_type>
                    <source_model>modagointegrator/shipping_source_modagocarrier</source_model>
                    <sort_order>' . $sortOrder . '</sort_order>
                    <show_in_default>1</show_in_default>
                    <show_in_website>0</show_in_website>
                    <show_in_store>0</show_in_store>
                    </carrier_' . $carrierCode . '>');
            /** @var Mage_Core_Model_Config_Element $adminSectionGroups */
            $adminApiFields = $config->getNode('sections/modagointegrator/groups/carriers/fields');
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
        /** @var Modago_Integrator_Model_Shipping_Source_Allcarriers $shippingSource */
        $shippingSource = Mage::getSingleton('modagointegrator/shipping_source_allcarriers ');
        $carriers = $shippingSource->getAllCarriers();
        return $carriers;
    }
}
