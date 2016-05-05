<?php

/**
 * helper for dhl module
 */
class Orba_Shipping_Helper_Carrier_Ups extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'ups_tracking.log';
    protected $_upsClient;
    protected $_upsLogin;
    protected $_upsPassword;
    protected $_upsAccount;
    protected $_upsDir;

    const UPS_STATUS_DELIVERED	= '011';
    const UPS_STATUS_BILLING_RECEIVED	= '001';
    const UPS_STATUS_TRANSIT		= '002';
    const UPS_STATUS_EXCEPTION    = '003';
    const UPS_STATUS_DELIVERED_ORIGIN_CFS = '004';
    const UPS_STATUS_DELIVERED_DESTINATION_CFS = '005';
    const UPS_STATUS_WAREHOUSING = '006';
    const UPS_STATUS_OUT_FOR_DELIVERY = '007';
    const UPS_STATUS_NOT_AVAILABLE = '111';
    const UPS_STATUS_NOT_AVAILABLE_2          = '222';

    const UPS_TYPE_DELIVERED = 'D';
    const UPS_TYPE_IN_TRANSIT = 'I';
    const UPS_TYPE_EXCEPTION = 'X';
    const UPS_TYPE_PICKUP = 'P';
    const UPS_TYPE_MANIFEST_PICKUP = 'M';

    const UPS_HEADER = 'UPS tracking info';
    const UPS_DIR		= 'ups';
    const UPS_FILE_EXT	= 'pdf';


    public function getHeader() {
        return self::UPS_HEADER;
    }

    public function isEnabledForVendor(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
        return (bool)(int)$vendor->getUseUps();
    }
    public function isEnabledForRma(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
        return (bool)(int)$vendor->getUpsRma();
    }
    public function isEnabledForPos(Zolago_Pos_Model_Pos $pos) {
        return (bool)(int)$pos->getUseUps();
    }

    /**
     * Initialize UPS Web API Client
     *
     * @param array $upsSettings Array('login' => 'value', 'password' => 'value','account' => 'value')
     *
     * @return Orba_Shipping_Model_Carrier_Client_Ups ups client
     */
    public function startClient($upsSettings = false)
    {
        if ($this->_upsLogin === null || $this->_upsPassword === null || $this->_upsClient === null) {
            if ($upsSettings) {
                $this->_upsLogin	= $upsSettings['login'];
                $this->_upsPassword	= $upsSettings['password'];
                $this->_upsAccount  = $upsSettings['account'];
            } else {
                $this->_upsLogin	= $this->getUpsLogin();
                $this->_upsAccount	= $this->getUpsAccount();
                $this->_upsPassword	= $this->getUpsPassword();
            }

            $upsClient			= Mage::getModel('orbashipping/carrier_client_ups');
            $upsClient->setAuth($this->_upsLogin, $this->_upsPassword,$this->_upsAccount);
            $this->_upsClient	= $upsClient;
        }

        return $this->_upsClient;
    }


    /**
     * Check if Ups is Active
     *
     * @return boolean Ups Service State
     */
    public function isActive()
    {
        return Mage::getStoreConfig('carriers/orbaups/active');
    }

    /**
     * Get Ups Login Data
     *
     * @return string Ups Login
     */
    public function getUpsLogin()
    {
        return trim(Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/orbaups/id')));
    }

    /**
     * Get Ups Password Data
     *
     * @return string Ups Password
     */
    public function getUpsPassword()
    {
        return trim(Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/orbaups/password')));
    }

    /**
     * Get Ups Account Data: Used to Pay for Shipping Cost
     *
     * @return string Ups Account
     */
    public function getUpsAccount()
    {
        return trim(Mage::getStoreConfig('carriers/orbaups/account'));
    }



    public function getUpsFileDir()
    {
        if ($this->_upsDir === null) {
            $this->_upsDir = $this->setUpsFileDir();
        }

        return $this->_upsDir;
    }

    public function setUpsFileDir()
    {
        if ($this->_upsDir === null) {
            $ioAdapter = new Varien_Io_File();
            $this->_upsDir = Mage::getBaseDir('media') . DS . self::UPS_DIR . DS;
            $ioAdapter->checkAndCreateFolder($this->_upsDir);
        }

        return $this->_upsDir;
    }



    /**
     * Check if UPS Waybill cna be shown
     *
     * @param type $track
     * @param type $shipment
     *
     * @return boolean $canShow Boolean Value
     */
    public function canShowWaybill($track, $shipment)
    {
        $canShow = false;
        if ($track->getCarrierCode() == Zolago_Ups_Helper_Data::UPS_CARRIER_CODE
                && $track->getNumber()
                && $shipment->getUdropshipStatus() != ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED) {
            $canShow = true;
        }

        return $canShow;
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
     * @param string $code ups delivery code
     * @param Mage_Sales_Model_Order_Shipment_Track $track
     * @return string
     */
    protected function _parseStatusCode($code,$track) {
        $status			= $this->__('Ready to Ship');
        switch ($code) {
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_DELIVERED:
            $status = $this->__('Delivered');
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
            $track->setDeliveredDate(Varien_Date::now());
            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);
            break;
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_EXCEPTION:
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_NOT_AVAILABLE:
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_NOT_AVAILABLE_2:
            $status = $this->__('Canceled');
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_CANCELED);
            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED);
            break;
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_BILLING_RECEIVED:
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_TRANSIT:
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_DELIVERED_ORIGIN_CFS:
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_DELIVERED_DESTINATION_CFS:
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_WAREHOUSING:
        case Orba_Shipping_Helper_Carrier_Ups::UPS_STATUS_OUT_FOR_DELIVERY:
            $status = $this->__('Shipped');
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED);
            $track->setShippedDate(Varien_Date::now());
            $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_SHIPPED);
            break;
        default:
            break;
        }
        return $status;
    }
    //}}}

    /**
     *
     * @param
     * @return
     */
    protected function _parseActivity($events,&$upsMessage) {
        foreach ($events as $singleEvent) {
            $description = isset($singleEvent->Description)? $singleEvent->Description: $singleEvent->Status->Description;
            if (isset($singleEvent->ActivityLocation)) {
                $location = isset($singleEvent->ActivityLocation->City)? $singleEvent->ActivityLocation->City: (empty($singleEvent->ActivityLocation->Address->City)? '':$singleEvent->ActivityLocation->Address->City);
            } else {
                $location = '';
            }
            $upsMessage[] =
                $this->__('Description: ') . $description . PHP_EOL
                . (empty($location)? '':($this->__('Terminal: ') . $location . PHP_EOL))
                . $this->__('Time: ') . date('Y-m-d H:i:s',strtotime($singleEvent->Date.$singleEvent->Time)) . PHP_EOL.PHP_EOL;
        }
    }

    
    /**
     * parsing track response
     */
     protected function _parseTrackResponse($track,$result,&$message,&$status,&$shipmentIdMessage) {
         
        if (is_array($result) && array_key_exists('error', $result)) {
            //Ups Error Scenario
            $this->_log('UPS Service Error: ' .$result['error']);
            $message[] = 'UPS Service Error: ' .$result['error'];
        }
        elseif (property_exists($result, 'Shipment')) {
            $number = empty($result->Shipment->InquiryNumber->Value)? $result->Shipment->ReferenceNumber->Value: $result->Shipment->InquiryNumber->Value;
            $shipmentIdMessage = $this->__('Tracking ID') . ': '. $number . PHP_EOL;
            if (!empty($result->Shipment->PickupDate))  {
                    $date = strtotime($result->Shipment->PickupDate);
                    $track->setShippedDate(date('Y-m-d H:i:s',$date));
            }

            if (!empty($result->Shipment->CurrentStatus->Code)) {
                $status = $this->_parseStatusCode($result->Shipment->CurrentStatus->Code,$track);
                if (isset($result->Shipment->Activity)) {
                    if (is_array($result->Shipment->Activity)) {
                        $events = array_reverse($result->Shipment->Activity);
                    } else {
                        $events = array($result->Shipment->Activity);
                    }
                    $this->_parseActivity($events,$message);
                }
            } elseif (!empty($result->Shipment->Package)) {
            
                if (!is_array($result->Shipment->Package)) {
                    $package = array($result->Shipment->Package);
                } else {
                    $package = $result->Shipment->Package;
                }
                foreach ($package as $pack) {
                    if ($pack->Activity) {
                        if (is_array($pack->Activity)) {
                            $events = $pack->Activity;
                        } else {
                            $events = array($pack->Activity);
                        }
                        $this->_parseActivity($events,$message);
                        foreach ($events as $event) {
                            if (isset($event->Status->Type)) {
                                $trackingNumber = isset($event->TrackingNumber)? $event->TrackingNumber:'';
                                if ($trackingNumber == $track->getTrackNumber) {
                                    switch ($event->Status->Type) {
                                    case Orba_Shipping_Helper_Carrier_Ups::UPS_TYPE_DELIVERED:
                                        $status = $this->__('Delivered');
                                        $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED);
                                        $track->setDeliveredDate(date('Y-m-d H:i:s',strtotime($event->Date.$event->Time)));
                                        $track->getShipment()->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED);
                                        break;
                                    default:
                                        ;
                                    }
                                }
                            }
                        }
                    }

                }
            }

        }
        else {
            //UPS Scenario: No T&T Data Recieved
            $this->_log('UPS Service Error: Missing Track and Trace Data');
            $message[] = $this->__('UPS Service Error: Missing Track and Trace Data');
        }
     }

}