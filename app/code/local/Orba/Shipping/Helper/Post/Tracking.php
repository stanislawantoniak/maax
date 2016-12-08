<?php

/**
 * helper for pp module
 */
class Orba_Shipping_Helper_Post_Tracking extends Orba_Shipping_Helper_Post {
	const PP_RETURNED_TO_SENDER = 'E';
	const PP_SHIPPING = 'P_NAD';
	const PP_AWIZO = 'P_A';
	const PP_REPEAT_AWIZO = 'P_PA';
	const PP_RETURNED = 'P_ZDUN';
	const PP_DELIVERED = 'P_D';
	const PP_PICKUP = 'P_OWU';

    /**
     * Initialize PP Web API Client for tracking
     */
    public function startClient($settings = false)
    {
        if (!$this->_client) {
            $client = Mage::getModel('orbashipping/post_client_tracking');
            $this->_client = $client;
        }

        return $this->_client;
    }

    public function process($client,$track) {
        $result = $client->getTrackStatus($track->getTrackNumber());
        $this->_processTrackStatus($track,$result);
    }

    /**
     * process track response
     */
    protected function _parseTrackResponse($track,$result,&$message,&$status,&$shipmentIdMessage) {
        $status = $this->__('Ready to Ship');
        if (!empty($result->return) && !empty($result->return->danePrzesylki) && isset($result->return->status)) {
            $stat = $result->return->status;
            if ($stat >= 0) {
                if (!empty($result->return->danePrzesylki->zdarzenia->zdarzenie)) {
                    $events = $result->return->danePrzesylki->zdarzenia->zdarzenie;
                    if (!is_array($events)) {
                        $events = array($events);
                    }
                    $lastState = false;
                    $logMessage = array();
                    foreach ($events as $event) {
                        $logMessage[] = $this->__('Time: ').$event->czas.PHP_EOL.
                                     (!empty($event->jednostka->nazwa)? ($this->__('Terminal: ').$event->jednostka->nazwa.PHP_EOL):'').
                                     $this->__('Description: ').$event->nazwa.PHP_EOL.PHP_EOL;
                        switch ($event->kod) {
                            case self::PP_SHIPPING:
                                if (!$lastState) {
                                    $status = $this->__('Shipped');                        
                                    $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED);
                                    $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_SHIPPED);                                                    
                                }
                                break;
                            case self::PP_DELIVERED:
                            case self::PP_PICKUP:
                                    $status = $this->__('Delivered');
                                    $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
                                    $track->setDeliveredDate($event->czas);
                                    if (!$track->getShippedDate()) {
                                        $track->setShippedDate($event->czas);
                                    }
                                    $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);                        
                                    $lastState = true;
                                break;
                            case self::PP_RETURNED:
                            case self::PP_RETURNED_TO_SENDER:
                                $status = $this->__('Cancelled');
                                $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_CANCELED);
                                $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);                                                    
                                $lastState = true;
                                break;
                            default:
                                ;
                                
                        }
                    }
                    foreach (array_reverse($logMessage) as $mess) { // reverse tracking log
                        $message[] = $mess;
                    }
                    if (!empty($result->return->danePrzesylki->dataNadania)) {
                        $track->setShippedDate($result->return->danePrzesylki->dataNadania);                                                
                    }
                }
            } else {
                switch ($stat) {
                    default:
                    case -99:
                        $message = $this->__('%s server unknown error','POCZTA POLSKA');
                        break;
                    case -2: 
                        $message = $this->__('Wrong tracking number');
                        break;
                    case -1:
                        if (!empty($result->return->numer)) {
                            $message = $this->__('Package with number %s not exists',$result->return->numer);
                        } else {
                            $message = $this->__('Package not exists');
                        }
                        break;
                }
                
                $this->_log('%s Service Error: %s','POCZTA POLSKA',$message);
                $message[] = $this->__('%s Service Error: %s','POCZTA POLSKA',$message);
            }
        } else {
            //UPS Scenario: No T&T Data Recieved
            $this->_log('%s Service Error: Missing Track and Trace Data','POCZTA POLSKA');
            $message[] = $this->__('%s Service Error: Missing Track and Trace Data','POCZTA POLSKA');
        }
        /*
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
            */
    }
}