<?php

/**
 * helper for dhl module
 */
class Orba_Shipping_Helper_Carrier_Dhl extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'dhl_tracking.log';
    protected $_dhlClient;
    protected $_dhlLogin;
    protected $_dhlPassword;
    protected $_dhlAccount;
    protected $_dhlDir;

    const DHL_DIR		= 'dhl';
    const DHL_FILE_EXT	= 'pdf';

    const DHL_STATUS_DELIVERED	= 'DOR';
    const DHL_STATUS_RETURNED	= 'ZWN';
    const DHL_STATUS_WRONG		= 'AN';
    const DHL_STATUS_SHIPPED    = 'DWP';
    const DHL_STATUS_SORT       = 'SORT';
    const DHL_STATUS_LP         = 'LP';
    const DHL_STATUS_LK         = 'LK';
    const DHL_STATUS_AWI         = 'AWI';
    const DHL_STATUS_BGR         = 'BGR';
    const DHL_STATUS_OP          = 'OP';
    const DHL_HEADER				= 'DHL Tracking Info';
    const DHL_CARRIER_CODE		= 'orbadhl';
    const ALERT_DHL_ZIP_ERROR = 1;

    public function getHeader() {
        return self::DHL_HEADER;
    }

	/**
	 * @param Zolago_Rma_Model_Rma_Track $track
	 * @return string
	 */
	public function getRmaDocument(Zolago_Rma_Model_Rma_Track $track) {
		return $this->getDhlFileDir() . $track->getTrackNumber() . '.pdf';
	}
    public function isEnabledForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
        return (bool)(int)$vendor->getUseDhl();
    }
    public function isEnabledForRma(Unirgy_Dropship_Model_Vendor $vendor) {
        return (bool)(int)$vendor->getDhlRma();
    }
    public function isEnabledForPos(Zolago_Pos_Model_Pos $pos) {
        return (bool)(int)$pos->getUseDhl();
    }


	public function getDhlRmaSettings($vendorId) {
		$dhlSettings = false;
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        $useRma = $vendor->getDhlRma();
        $useDhl = $vendor->getUseDhl();
        if ((!$account = $vendor->getDhlRmaAccount()) || (!$useRma)) {            
            if ((!$account = $vendor->getDhlAccount()) || (!$useDhl)) {
                return false;
            }
        }
        if ((!$login = $vendor->getDhlRmaLogin()) || (!$useRma)) {
            if (!$login = $vendor->getDhlLogin()) {
                return false;
            }
        }

        if ((!$password = $vendor->getDhlRmaPassword()) || (!$useRma)) {
            if (!$password = $vendor->getDhlPassword()) {
                return false;
            }
        }
        // default params
        $dhlSettings = array (
            'login' => $login,
            'password' => $password,
            'account' => $account,            
            'weight' => 2,
            'height' => 1,
            'length' => 1,
            'width' => 1,
            'quantity' => 1,            
            'type' => Orba_Shipping_Model_Carrier_Client_Dhl::SHIPMENT_TYPE_PACKAGE,
        );
        return $dhlSettings;
	}

    /**
     * Initialize DHL Web API Client
     *
     * @param array $dhlSettings Array('login' => 'value', 'password' => 'value','account' => 'value')
     *
     * @return Zolago_Dhl_Model_Client DHl Client
     */
    public function startClient($dhlSettings = false)
    {
        if ($this->_dhlLogin === null || $this->_dhlPassword === null || $this->_dhlClient === null) {
            if ($dhlSettings) {
                $this->_dhlLogin	= $dhlSettings['login'];
                $this->_dhlPassword	= $dhlSettings['password'];
                $this->_dhlAccount  = $dhlSettings['account'];
            } else {
                $this->_dhlLogin	= $this->getDhlLogin();
                $this->_dhlAccount	= $this->getDhlAccount();
                $this->_dhlPassword	= $this->getDhlPassword();
            }

            $dhlClient			= Mage::getModel('orbashipping/carrier_client_dhl');
            $dhlClient->setAuth($this->_dhlLogin, $this->_dhlPassword,$this->_dhlAccount);
            $this->_dhlClient	= $dhlClient;
        }

        return $this->_dhlClient;
    }


    /**
     * Check if Dhl is Active
     *
     * @return boolean Dhl Service State
     */
    public function isActive()
    {
        return Mage::getStoreConfig('carriers/orbadhl/active');
    }

    /**
     * Get Dhl Login Data
     *
     * @return string Dhl Login
     */
    public function getDhlLogin()
    {
        return trim(Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/orbadhl/id')));
    }

    /**
     * Get Dhl Password Data
     *
     * @return string Dhl Password
     */
    public function getDhlPassword()
    {
        return trim(Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/orbadhl/password')));
    }

    /**
     * Get Dhl Account Data: Used to Pay for Shipping Cost
     *
     * @return string Dhl Account
     */
    public function getDhlAccount()
    {
        return trim(Mage::getStoreConfig('carriers/orbadhl/account'));
    }

    /**
     * Get Dhl Default Weight
     *
     * @return string Dhl Account
     */
    public function getDhlDefaultWeight()
    {
        return (int) ceil(Mage::getStoreConfig('carriers/orbadhl/default_weight'));
    }

    /**
     * Get Dhl Next Check Date
     *
     * @param integer $storeId
     *
     * @return date	Date Object of Next Check
     */
    public function getNextDhlCheck($storeId)
    {
        $repeatIn = Mage::getStoreConfig('carriers/orbadhl/repeat_tracking', $storeId);
        if ($repeatIn <= 0) {
            $repeatIn = 1;
        }
        $repeatIn = $repeatIn*60*60;
        $time = Mage::getModel('core/date')->timestamp();
        return date('Y-m-d H:i:s', $time+$repeatIn);
    }

    public function getDhlFileDir()
    {
        if ($this->_dhlDir === null) {
            $this->_dhlDir = $this->setDhlFileDir();
        }

        return $this->_dhlDir;
    }

    public function setDhlFileDir()
    {
        if ($this->_dhlDir === null) {
            $ioAdapter = new Varien_Io_File();
            $this->_dhlDir = Mage::getBaseDir('media') . DS . self::DHL_DIR . DS;
            $ioAdapter->checkAndCreateFolder($this->_dhlDir);
        }

        return $this->_dhlDir;
    }


    public function getIsDhlFileAvailable($trackNumber)
    {
        $dhlFile = false;
        if ($this->_dhlDir === null) {
            $this->setDhlFileDir();
            $dhlFile = $this->getIsDhlFileAvailable($trackNumber);
        } else {
            $this->setDhlFileDir();
            if (count($trackNumber)):
                    $ioAdapter = new Varien_Io_File();
            $dhlFileLocation = $this->_dhlDir . $trackNumber . '.' . self::DHL_FILE_EXT;
            if ($ioAdapter->fileExists($dhlFileLocation)) {
                $dhlFile = $dhlFileLocation;
            }
            endif;
        }
        return $dhlFile;
    }


    /**
     * Check if DHL Waybill cna be shown
     *
     * @param type $track
     * @param type $shipment
     *
     * @return boolean $canShow Boolean Value
     */
    public function canShowWaybill($track, $shipment)
    {
        $canShow = false;
        if ($track->getCarrierCode() == Zolago_Dhl_Helper_Data::DHL_CARRIER_CODE
                && $track->getNumber()
                && $shipment->getUdropshipStatus() != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED) {
            $canShow = true;
        }

        return $canShow;
    }
    public static function getAlertText($int) {
        switch ($int) {
        case self::ALERT_DHL_ZIP_ERROR:
            return "Zip code in shipment address is not valid. There will be a problem when shipping to that address.";
            break;
        }
        return "";
    }
    /**
     * dhl params via postcode
     * @param string $zip
     * @param int $timestamp
     * @return array
     */
    protected function _getDhlPostalService($zip,$timestamp) {
        $dhlClient = Mage::getModel('orbashipping/carrier_client_dhl');
        $login = $this->getDhlLogin();
        $password = $this->getDhlPassword();
        $dhlClient->setAuth($login, $password);
        $ret = $dhlClient->getPostalCodeServices($zip, date('Y-m-d',$timestamp));
        if (is_object($ret) && property_exists($ret, 'getPostalCodeServicesResult')) {
            $empty = new StdClass;
            $empty->domesticExpress9 = false;
            $empty->domesticExpress12 = false;
            $empty->deliveryEvening = false;
            $empty->deliverySaturday = false;
            $empty->exPickupFrom     = 'brak';
            $empty->exPickupTo       = 'brak';
            $empty->drPickupFrom     = 'brak';
            $empty->drPickupTo       = 'brak';
            if ($ret->getPostalCodeServicesResult == $empty) {
                $this->_log("Check PL zip availability:empty " . date('Y-m-d',$timestamp), 'dhl_zip.log');
                return null;
            } else {
                return $ret;
            }

        } else {
            if (isset($ret['error'])) {
                $this->_log("Check PL zip availability:" . $ret['error'], 'dhl_zip.log');
            } else {
                $this->_log("Check PL zip availability:error", 'dhl_zip.log');
            }
            //if there was an communication error forms should PASS validation
            return null;
        }
    }
    /**
     * get courier pickup hours
     * @param int $timestamp
     * @return stdClass
     */
    public function getDhlPickupParamsForDay($timestamp,$zip) {
        $ret = $this->_getDhlPostalService($zip,$timestamp);
        return $ret;
    }
    /**
     * Check if entered zip available on DHL
     * @param $country
     * @param $zip
     *
     * @return bool
     */
    public function isDHLValidZip($country, $zip)
    {
        $dhlValidZip = true;
        if (!empty($zip)) {
            $zip = str_replace('-', '', $zip);
            $zipModel = Mage::getModel('orbashipping/zip');
            $source = $zipModel->load($zip, 'zip')->getId();
            if (!empty($source)) {
                return true;
            } else {
                $ret = $this->_getDhlPostalService($zip,time());
                if ($ret) {
                    $zipModel = Mage::getResourceModel('orbashipping/zip');
                    $zipModel->updateDhlZip($country, $zip);
                } else {
                    $dhlValidZip = false;
                }

            }
        }
        return $dhlValidZip;
    }
//{{{
    /**
     * Collect tracking for DHL
     * @param Zolago_Carrier_Model_Client $client
     * @param type $track
     * @return
     */

    public function process($client,$_track) {
        $result = $client->getTrackAndTraceInfo($_track->getTrackNumber());
        //Process Single Track and Trace Object
        $this->_processDhlTrackStatus($_track, $result);

    }
//}}}

    /**
     * Process Single Dhl Track and Trace Record
     *
     * @param type $track
     * @param type $dhlResult
     *
     * @return boolean
     */
    protected function _processDhlTrackStatus($track, $dhlResult) {
        $dhlMessage = array();
        $status			= $this->__('Ready to Ship');
        $shipmentIdMessage = '';
        $shipment		= $track->getShipment();
        $oldStatus = $track->getUdropshipStatus();

        if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
            //Dhl Error Scenario
            Mage::helper('orbashipping/carrier_dhl')->_log(Mage::helper('zolagopo')->__('DHL Service Error: %s', $dhlResult['error']));
            $dhlMessage[] = 'DHL Service Error: ' .$dhlResult['error'];
        }
        elseif (property_exists($dhlResult, 'getTrackAndTraceInfoResult') && property_exists($dhlResult->getTrackAndTraceInfoResult, 'events') && property_exists($dhlResult->getTrackAndTraceInfoResult->events, 'item')) {
            $shipmentIdMessage = $this->__('Tracking ID') . ': '. $dhlResult->getTrackAndTraceInfoResult->shipmentId . PHP_EOL;
            $events = $dhlResult->getTrackAndTraceInfoResult->events;
            //DHL: Concatenate T&T Message History
            $shipped = false;
            foreach ($events->item as $singleEvent) {
                $dhlMessage[$singleEvent->status] =
                    (!empty($singleEvent->receivedBy) ? $this->__('Received By: ') . $singleEvent->receivedBy . PHP_EOL : '')
                    . $this->__('Description: ') . $singleEvent->description . PHP_EOL
                    . $this->__('Terminal: ') . $singleEvent->terminal . PHP_EOL
                    . $this->__('Time: ') . $singleEvent->timestamp . PHP_EOL.PHP_EOL;
                switch ($singleEvent->status) {
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_DELIVERED:
                    $status = $this->__('Delivered');
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED);
                    $date = date('Y-m-d',strtotime($singleEvent->timestamp));
                    $track->setDeliveredDate($date);
                    $track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
                    $shipped = false;
                    break;
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_RETURNED:
                    $status = $this->__('Returned');
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
                    $track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
                    $shipped = false;
                    break;
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_WRONG:
                    $status = $this->__('Canceled');
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
                    $track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
                    $shipped = false;
                    break;
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_SHIPPED:
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_SORT:
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_LP:
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_LK:
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_AWI:
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_BGR:
                case Orba_Shipping_Helper_Carrier_Dhl::DHL_STATUS_OP:
                    if (!$shipped) {
                        $status = $this->__('Shipped');
                        $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED);
                        $date = date('Y-m-d',strtotime($singleEvent->timestamp));
                        $track->setShippedDate($date);
                        $track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
                        $shipped = true;
                    }
                    break;
                default:
                    break;
                }
            }
        }
        else {
            //DHL Scenario: No T&T Data Recieved
            Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: Missing Track and Trace Data');
            $dhlMessage[] = $this->__('DHL Service Error: Missing Track and Trace Data');
        }
        if ($oldStatus != $track->getUdropshipStatus()) {
            $this->_trackingHelper->addComment($track,$shipmentIdMessage,$dhlMessage,$status);
        }
        if (!in_array($status, array($this->__('Delivered'), $this->__('Returned'), $this->__('Canceled')))) {
            $track->setNextCheck(Mage::helper('orbashipping/carrier_dhl')->getNextDhlCheck($shipment->getOrder()->getStoreId()));
        }

        try {
            $track->setWebApi(true);
            $track->save();
            $track->getShipment()->save();
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return true;
    }

	/**
	 * gets package size from source class and returns them as array(width,height,depth), if key does not exist then it returns array(0,0,0)
	 * @param string $key
	 * @return array
	 */
	public function getDhlParcelDimensionsByKey($key) {
		/** @var Orba_Shipping_Model_System_Source_PkgSizes $pkgSizesSingleton */
		$pkgSizesSingleton = Mage::getSingleton('orbashipping/system_source_pkgSizes');
		$validationArray = $pkgSizesSingleton->toOptionHash();
		return isset($validationArray[$key]) ? explode('x',$key) : array(0,0,0);
	}

	/**
	 * gets package rate from vendor config by specified key, if key does not exist then it returns -1
	 * @param Zolago_Dropship_Model_Vendor $vendor
	 * @param string $key
	 * @return float
	 */
	public function getDhlVendorRateByKey(Zolago_Dropship_Model_Vendor $vendor,$key) {
		/** @var Orba_Shipping_Model_System_Source_PkgRateTypes $pkgRateTypesSingleton */
		$pkgRateTypesSingleton = Mage::getSingleton('orbashipping/system_source_pkgRateTypes');
		$validationArray = $pkgRateTypesSingleton->toOptionHash();
		return isset($validationArray[$key]) && $vendor->getData($key) !== "" ? floatval($vendor->getData($key)) : -1;
	}

	public function getDhlParcelTypeByKey($key) {
		switch ($key) {
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_ENVELOPE :
				$dhlType = Orba_Shipping_Model_Carrier_Client_Dhl::SHIPMENT_TYPE_ENVELOPE;
				break;

			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_0_5 :
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_5_10 :
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_10_20 :
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_20_31_5 :
				$dhlType = Orba_Shipping_Model_Carrier_Client_Dhl::SHIPMENT_TYPE_PACKAGE;
				break;

			default:
				throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("Unknown DHL package type"));
		}
		return $dhlType;
	}

	public function getDhlParcelWeightByKey($key) {
		switch ($key) {
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_0_5 :
				$weight = 5;
				break;
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_5_10 :
				$weight = 10;
				break;
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_10_20 :
				$weight = 20;
				break;
			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_PARCEL_20_31_5 :
				$weight = 31.5;
				break;

			case Orba_Shipping_Model_System_Source_PkgRateTypes::DHL_RATES_ENVELOPE :
			default:
				$weight = 1;
		}
		return $weight;
	}
}