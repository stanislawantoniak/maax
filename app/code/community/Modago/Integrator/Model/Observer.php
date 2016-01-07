<?php
/**
 * modago integrator observer
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
                $ret = $client->setOrderShipment($key,$orderId,$dateShipped,$carrier,$trackNumber);
                if (empty($ret->status)) { // error
                    if (!empty($ret->message)) {
                         Modago_Integrator_Model_Log::log($ret->message);
                    }
                }
            } else {
                $message = Mage::helper('modagointegrator')->__('Modago order %s tracking info cannot be saved',$orderId);
                Modago_Integrator_Model_Log::log($message);
            }
        }
    }    
}