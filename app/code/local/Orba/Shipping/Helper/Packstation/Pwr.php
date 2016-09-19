<?php

/**
 * helper for inpost module
 */
class Orba_Shipping_Helper_Packstation_Pwr extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'pwr_tracking.log';

    const FILE_DIR		= 'pwr';
    const FILE_EXT	= 'pdf';

    const PWR_HEADER				= 'PWR Tracking Info';


    public function getHeader() {
        return self::PWR_HEADER;
    }

    public function startClient($settings = false) {
        $hlp = Mage::helper('zospwr');
        $client = Mage::getModel('orbashipping/packstation_client_pwr');
        $login = $hlp->getPartnerId();
        $pass = $hlp->getPartnerKey();
        $client->setAuth($login,$pass);
        return $client;
    }
    /**
     * inpost is active
     */
    public function isActive() {
        return Mage::helper('zospwr')->isActive();
    }
    /**
     * process tracking
     */
    public function process($client,$track) {
        $result = $client->getPackStatus($track->getTrackNumber());
        $this->_processTrackStatus($track,$result);
    }

    /**
     * process track response
     * 
     * @todo - skończyć jak będą testowe numery
     */
    protected function _parseTrackResponse($track,$result,&$message,&$status,&$shipmentIdMessage) {
        Mage::log($result);
        /*
        $status = $this->__('Ready to Ship');
        if (is_array($result)) {
            if (array_key_exists('error', $result)) {
                $this->_log($this->__('%s Service Error: %s', 'INPOST',$result['error']));
                $message[] = $this->__('%s Service Error: %s', 'INPOST', $result['error']);
            } else {
                switch ($result['status']) {
                    case self::INPOST_STATUS_SENT:
                    case self::INPOST_STATUS_IN_TRANSIT:
                    case self::INPOST_STATUS_STORED:
                    case self::INPOST_STATUS_AVIZO:
                    case self::INPOST_STATUS_CUSTOMER_STORED:
                        $status = $this->__('Shipped');                        
                        $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED);
                        $track->setShippedDate($result['statusDate']);                        
                        $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_SHIPPED);                        
                        break;
                    case self::INPOST_STATUS_LABEL_EXPIRED:
                    case self::INPOST_STATUS_EXPIRED:
                    case self::INPOST_STATUS_CANCELLED:
                        $status = $this->__('Cancelled');
                        $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_CANCELED);
                        $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);                        
                        break;
                    case self::INPOST_STATUS_DELIVERED:
                        $status = $this->__('Delivered');
                        $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
                        $track->setDeliveredDate($result['statusDate']);
                        if (!$track->getShippedDate()) {
                            $track->setShippedDate($result['statusDate']);
                        }
                        $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);                        
                        break;
                    case self::INPOST_STATUS_RETURNED_TO_AGENCY:
                        $status = $this->__('Returned');
                        $track->setUdropshipStatus(Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED);
                        $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);                        
                        break;
                    case self::INPOST_STATUS_CLAIMED:
                    case self::INPOST_STATUS_CUSTOMER_DELIVERING:
                    case self::INPOST_STATUS_CLAIM_PROCESSED:
                    case self::INPOST_STATUS_CREATED:
                    case self::INPOST_STATUS_PREPARED:
                    default:
                        
                }    
                $shipmentIdMessage = $this->__('Tracking ID') . ': '. $track->getTrackNumber() . PHP_EOL.
                    $this->__('New status: %s',$result['status']) . PHP_EOL.
                    $this->__('Change status date: %s', $result['statusDate']). PHP_EOL;
            }
        } else {
            //UPS Scenario: No T&T Data Recieved
            $this->_log('%s Service Error: Missing Track and Trace Data','INPOST');
            $message[] = $this->__('%s Service Error: Missing Track and Trace Data','INPOST');
        }
        */
    }
}