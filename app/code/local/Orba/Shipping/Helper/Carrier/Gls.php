<?php

/**
 * helper for gls
 */
class Orba_Shipping_Helper_Carrier_Gls extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'gls_tracking.log';
    protected $_glsClient;

    const GLS_STATUS_DELIVERED	= 'DELIVERED';
    const GLS_STATUS_CANCELED	= 'CANCELED';
    const GLS_STATUS_RETURNED   = 'FINAL';
    const GLS_HEADER = 'GLS tracking info';


    public function getHeader() {
        return self::GLS_HEADER;
    }


    /**
     * Initialize GLS Web API Client
     *
     *
     * @return Orba_Shipping_Model_Carrier_Client_Gls ups client
     */
    public function startClient($glsSettings = false)
    {
        if ($this->_glsClient === null) {
            $glsClient			= Mage::getModel('orbashipping/carrier_client_gls');
            $this->_glsClient	= $glsClient;
        }

        return $this->_glsClient;
    }


    /**
     * Check if Gls is Active
     *
     * @return boolean Gls Service State
     */
    public function isActive()
    {
        return Mage::getStoreConfig('carriers/zolagogls/active');
    }




    //{{{
    /**
     * Collect tracking for UPS
     * @param Zolago_Carrier_Model_Client $client
     * @param type $track
     * @return
     */

    public function process($client,$_track) {
        $result = $client->getTrackAndTraceInfo($_track->getTrackNumber());
        //Process Single Track and Trace Object
        $this->_processTrackStatus($_track, $result);

    }


    /**
     *
     * @param
     * @return
     */
    protected function _parseActivity($events,&$message) {
        if (!empty($events->history)) {
            $list = array_reverse($events->history,true);
            foreach ($list as $key => $singleEvent) {
                $description = isset($singleEvent->evtDscr)? $singleEvent->evtDscr: '';
                if (isset($singleEvent->address)) {
                    $location = isset($singleEvent->address->city)? $singleEvent->address->city:'';
                } else {
                    $location = '';
                }
                $mess = $this->__('Description: ') . $description . PHP_EOL
                    . (empty($location)? '':($this->__('Terminal: ') . $location . PHP_EOL));
                if (!empty($date = $this->_parseDate($events,$key))) {
                    $mess .= $this->__('Time: ') . $this->_parseDate($events,$key) . PHP_EOL;
                }
                $message[] = $mess . PHP_EOL;
            }
        }
    }

    /**
     * parse date
     * @param stdObj $trackInfo
     * @return string
     */
    protected function _parseDate($trackInfo,$num = 0,$defaultDate = '') {
        $date = empty($defaultDate)? Varien_Date::now():$defaultDate;
        if (!empty($trackInfo->history[$num]->date)) {
            $date = $trackInfo->history[$num]->date;
            if (!empty($trackInfo->history[$num]->time)) {
                $date .= ' '.$trackInfo->history[$num]->time;
            }
        }
        return $date;
    }

    /**
     * parsing track response
     */
    protected function _parseTrackResponse($track,$result,&$message,&$status,&$shipmentIdMessage) {
        if (is_object($result)) {
            if (!empty($result->exceptionText)) {
                $mess = $this->__('GLS Service Error:Track number:%s -  %s',$track->getTrackNumber(),$result->exceptionText);
                $this->_log($mess);
                $message[] = $mess;
            }
            elseif (!empty($result->tuStatus)) {
                $status	= $this->__('Ready to Ship');
                foreach ($result->tuStatus as $trackInfo) {
                    if (!empty($trackInfo->progressBar) && !empty($trackInfo->progressBar->statusInfo)) {
                    switch ($trackInfo->progressBar->statusInfo) {
                        case Orba_Shipping_Helper_Carrier_Gls::GLS_STATUS_DELIVERED:
                            $status = $this->__('Delivered');
                            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
                            $date = $this->_parseDate($trackInfo);
                            $track->setDeliveredDate($date);
                            if (!$track->getShippedDate) {
                                $track->setShippedDate($date);
                            }
                            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);
                            $this->_parseActivity($trackInfo,$message);
                            break;
                        case Orba_Shipping_Helper_Carrier_Gls::GLS_STATUS_RETURNED:
                            $status = $this->__('Returned');
                            $track->setUdropshipStatus(Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED);
                            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);                        
                            break;
                        default:
                            break;
                        }
                    }
                }
            }
        } else {
            //UPS Scenario: No T&T Data Recieved
            $mess = $this->__('GLS Service Error: Wrong track and trace Data (%s)',$result);
            $this->_log($mess);
            $message[] = $mess;
        }
    }

}