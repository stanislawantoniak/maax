<?php
/**
 * manual tracking
 */
class Orba_Shipping_Helper_Carrier_Manual extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'manual_tracking.log';

    const MANUAL_HEADER = 'Tracking info';


    public function getHeader() {
        return self::MANUAL_HEADER;
    }

    public function isActive() {
        return true;
    }
    public function startClient($settings = false) {
        return null;
    }
    public function process($client,$_track) {
        $status = Mage::app()->getRequest()->getParam('track_status');
        $id = $_track->getId();
        if (!empty($status[$id])) {
            $this->_processTrackStatus($_track,$status[$id]);
        }
    }
    /**
     * parsing track response
     */
    protected function _parseTrackResponse($track,$result,&$message,&$status,&$shipmentIdMessage) {
        $trackingStatusList = Mage::getSingleton('udropship/source')->getTrackingStatusList();
        $oldState = isset($trackingStatusList[$track->getUdropshipStatus()])? $trackingStatusList[$track->getUdropshipStatus()]: $this->__('Unknown');
        $date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        switch ($result) {
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED:
            $status = $this->__('Delivered');
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
            $track->setDeliveredDate($date);
            if (!$track->getShippedDate) {
                $track->setShippedDate($date);
            }
            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_CANCELED:
        case Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED:
            $status = $this->__('Returned');
            $track->setUdropshipStatus(Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED);
            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED:
            $status = $this->__('Shipped');
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED);
            $track->setShippedDate($date);
            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_SHIPPED);
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_READY:
            $status = $this->__('Ready');
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_READY);
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING:
            $status = $this->__('Pending');
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING);
            break;
        default:
            break;
        }
        $newState = isset($trackingStatusList[$track->getUdropshipStatus()])? $trackingStatusList[$track->getUdropshipStatus()]: $this->__('Unknown');
        $message[] = $this->__('Track status manually changed from %s to %s',$oldState,$newState);
    }
}