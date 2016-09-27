<?php

/**
 * helper for inpost module
 */
class Orba_Shipping_Helper_Packstation_Pwr extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'pwr_tracking.log';

    const FILE_DIR		= 'pwr';
    const FILE_EXT	= 'pdf';

    const PWR_HEADER				= 'PWR Tracking Info';


    const PWR_STATUS_SORT_LOCAL = 100;
    const PWR_STATUS_DELIVERY_FROM_SC_TO_EXP = 110;
    const PWR_STATUS_DELIVERY_FROM_SENDER = 200;
    const PWR_STATUS_CANCELLED_AVIZO = 201;
    const PWR_STATUS_SEND_STAND = 210;
    const PWR_STATUS_DELIVERY_FROM_STAND = 230;
    const PWR_STATUS_SORT_CENTRAL_1 = 300;
    const PWR_STATUS_SORT_CENTRAL_2 = 400;
    const PWR_STATUS_DELIVERY_FROM_EXP_TO_SC = 450;
    const PWR_STATUS_IN_EXPEDITION = 653;
    const PWR_STATUS_DELIVERY_TO_STAND = 680;
    const PWR_STATUS_IN_STAND = 690;
    const PWR_STATUS_IN_STAND_SMS = 695;
    const PWR_STATUS_NOT_RECEIVED = 700;
    const PWR_STATUS_NOT_RECEIVED_RETURN = 709;
    const PWR_STATUS_NOT_RECEIVED_WRONG_STAND = 729;
    const PWR_STATUS_RMA = 749;
    const PWR_STATUS_RETURN_TO_EXPEDITION = 790;
    const PWR_STATUS_RETURN_TO_SORT = 800;
    const PWR_STATUS_RETURN_TO_SENDER = 900;
    const PWR_STATUS_LOST = 999;
    const PWR_STATUS_DELIVERED_BY_CLIENT = 1000;
    const PWR_STATUS_DELIVERED = 1100;


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
        $status = $this->__('Ready to Ship');
        if (is_array($result)) {
            if (!empty($result['NewDataSet']['PackStatus'])) {
                $item = $result['NewDataSet']['PackStatus'];
                // sprawdzamy czy paczka pojedyncza, czy lista
                $date = $item['Data'];
                switch ($item['Trans']) {
                case self::PWR_STATUS_DELIVERED_BY_CLIENT:
                case self::PWR_STATUS_DELIVERED:
                    $status = $this->__('Delivered');
                    $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
                    $track->setDeliveredDate($date);
                    if (!$track->getShippedDate()) {
                        $track->setShippedDate($date);
                    }
                    $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);
                    break;
                case self::PWR_STATUS_RETURN_TO_SENDER:
                case self::PWR_STATUS_RETURN_TO_SORT:
                case self::PWR_STATUS_NOT_RECEIVED_RETURN:
                case self::PWR_STATUS_NOT_RECEIVED_WRONG_STAND:
                case self::PWR_STATUS_RETURN_TO_EXPEDITION:
                case self::PWR_STATUS_RETURN_TO_SENDER:
                case self::PWR_STATUS_NOT_RECEIVED:
                    $status = $this->__('Returned');
                    $track->setUdropshipStatus(Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED);
                    $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);
                    break;
                case self::PWR_STATUS_LOST:
                    $status = $this->__('Cancelled');
                    $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_CANCELED);
                    $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);
                    break;
                default:
                }
                $shipmentIdMessage = $this->__('Tracking ID') . ': '. $track->getTrackNumber() . PHP_EOL.
                                     $this->__('New status: %s',$item['Trans_Des']) . PHP_EOL.
                                     $this->__('Change status date: %s', $date). PHP_EOL;
            }
        } else {
            $this->_log('%s Service Error: Missing Track and Trace Data','PWR');
            $message[] = $this->__('%s Service Error: Missing Track and Trace Data','PWR');
        }
    }
}