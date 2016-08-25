<?php

/**
 * helper for dpd
 */
class Orba_Shipping_Helper_Carrier_Dpd extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'dpd_tracking.log';
    protected $_dpdClient;

    const DPD_STATUS_DELIVERED	= 'Przesyłka doręczona';
    //TODO change status values for CANCEL and RETURN to real
    const DPD_STATUS_CANCELED	= 'CANCELED';
    const DPD_STATUS_RETURNED   = 'FINAL';
    const DPD_HEADER = 'DPD tracking info';

    public function getHeader() {
        return self::DPD_HEADER;
    }

    /**
     * Initialize DPD Web Client
     * @return Orba_Shipping_Model_Carrier_Client_Dpd client
     */
    public function startClient($dpdSettings = false)
    {
        if ($this->_dpdClient === null) {
            $dpdClient			= Mage::getModel('orbashipping/carrier_client_dpd');
            $this->_dpdClient	= $dpdClient;
        }
        return $this->_dpdClient;
    }

    /**
     * Check if Dpd is Active
     * @return boolean Dpd Service State
     */
    public function isActive()
    {
        return Mage::getStoreConfig('carriers/zolagodpd/active');
    }

    /**
     * Collect tracking for DPD
     * @param Orba_Shipping_Model_Carrier_Client_Dpd $client
     * @param $_track
     */

    public function process($client,$_track) {
        $result = $client->getTrackAndTraceInfo($_track->getTrackNumber());
        //Process Single Track and Trace Object
        $this->_processTrackStatus($_track, $result);
    }

    /**
     * @param
     */
    protected function _parseActivity($events,&$message) {
        if (!empty($events)) {
            $list = array_reverse($events,true);
            foreach ($list as $key => $singleEvent) {
                $description = isset($singleEvent[4])? $singleEvent[4] : '';
                if (isset($singleEvent[6])) {
                    $location = $singleEvent[6];
                } else {
                    $location = '';
                }
                $mess = $this->__('Description: ') . $description . PHP_EOL
                    . (empty($location)? '':($this->__('Filial Branch: ') . $location . PHP_EOL));
                if (isset($singleEvent[0])) {
                    $mess .= $this->__('Time: ') . $singleEvent[0];
                    if(isset($singleEvent[2])){
                        $mess .= " ". $singleEvent[2];
                    }
                    $mess .= PHP_EOL;
                }
                $message[] = $mess . PHP_EOL;
            }
        }
    }

    /**
     * parsing track response
     */
    protected function _parseTrackResponse($track,$result,&$message,&$status,&$shipmentIdMessage) {
        if (is_object($result) && $result->length) {
            $shipmentIdMessage = $this->__('Tracking ID') . ': '.$track->getTrackNumber() . PHP_EOL;
            $responseData = array();
            $table = $result->item(0);

            $i=0;
            $historyArray = array();
            foreach($table->childNodes as $tr){
                foreach($tr->childNodes as $td){
                    if($i==0){
                        $responseData[] = $td->nodeValue;
                    }
                    $historyArray[$i][] = $td->nodeValue;
                }
                $i++;
            }
            if (!empty($responseData[4])) {
                $pos = substr_count($responseData[4], Orba_Shipping_Helper_Carrier_Dpd::DPD_STATUS_DELIVERED);
                if($pos){
                    $responseData[4] = Orba_Shipping_Helper_Carrier_Dpd::DPD_STATUS_DELIVERED;
                    $historyArray[0][4] = Orba_Shipping_Helper_Carrier_Dpd::DPD_STATUS_DELIVERED;
                }
                $status	= $this->__('Ready to Ship');
                switch ($responseData[4]) {
                    case Orba_Shipping_Helper_Carrier_Dpd::DPD_STATUS_DELIVERED:
                        $status = $this->__('Delivered');
                        $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
                        $date = $responseData[0];
                        $track->setDeliveredDate($date);
                        if (!$track->getShippedDate) {
                            $track->setShippedDate($date);
                        }
                        $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);
                        $this->_parseActivity($historyArray,$message);
                        break;
                    case Orba_Shipping_Helper_Carrier_Dpd::DPD_STATUS_RETURNED:
                        $status = $this->__('Returned');
                        $track->setUdropshipStatus(Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED);
                        $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);
                        break;
                    default:
                        break;
                }
            }
        } else {
            $mess = Mage::helper('orbashipping')->__('DPD does not have a response for tracking number: '.$track->getTrackNumber());
            $this->_log($mess);
            $message[] = $mess;
        }
    }
}