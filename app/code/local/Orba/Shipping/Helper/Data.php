<?php

/**
 * helper for dhl module
 */
class Orba_Shipping_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function canPosUseCarrier(Zolago_Pos_Model_Pos $pos) {
        return (bool)(int)($pos->getUseDhl()) | (bool)(int)($pos->getUseOrbaups());
    }
    public function canVendorUseCarrier(Unirgy_Dropship_Model_Vendor $vendor) {
		return (bool)(int)($vendor->getUseDhl()) | (bool)(int)($vendor->getUseOrbaups())
		    | (bool)(int)($vendor->getDhlRma());
	}

    public function isDhlEnabledForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
		return (bool)(int)$vendor->getUseDhl();
	}
    public function isDhlEnabledForRma(Unirgy_Dropship_Model_Vendor $vendor) {
		return (bool)(int)$vendor->getDhlRma();
	}
    public function isDhlEnabledForPos(Zolago_Pos_Model_Pos $pos) {
		return (bool)(int)$pos->getUseDhl();
	}
	
	/**
	 * Special Log Message Function
	 * 
	 * @param string $message	Message to Log
	 * @param string $logFile	Log file name. Default: dhl_tracking.log
	 */
	public function _log($message, $logFile = false) {
		if (!$logFile) {
			$logFile = $this->_dhlLogFile;
		}
		
		Mage::log($message, null, $logFile, true);
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
		return date('Y-m-d H:i:s', time()+$repeatIn);		
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
            $zipModel = Mage::getModel('zolagodhl/zip');
            $source = $zipModel->load($zip, 'zip')->getId();
            if (!empty($source)) {
                return true;
            } else {
                $dhlClient = Mage::getModel('zolagodhl/client');
                $login = Mage::helper('core')->decrypt($this->getDhlLogin());
                $password = Mage::helper('core')->decrypt($this->getDhlPassword());
                $dhlClient->setAuth($login, $password);                
                $ret = $dhlClient->getPostalCodeServices($zip, date('Y-m-d'));
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
                        $dhlValidZip = false;
                    } else {
                        $dhlValidZip = true;
                    }

                    if ($dhlValidZip) {
                        $zipModel = Mage::getResourceModel('zolagodhl/zip');
                        $zipModel->updateDhlZip($country, $zip);
                    }

                } else {
                    if (isset($ret['error'])) {
                        $this->_log("Check PL zip availability:" . $ret['error'], 'dhl_zip.log');
                    } else {
                        $this->_log("Check PL zip availability:error", 'dhl_zip.log');
                    }
                    //if there was an communication error forms should PASS validation
                    $dhlValidZip = false;
                }
            }
        }
        return $dhlValidZip;
    }
    
    /**
     * 
     * @param 
     * @return 
     */
     public function getShippingManager($carrier) {
         switch ($carrier) {
             case Orba_Shipping_Model_Carrier_Dhl::CODE:
                 $model = Mage::getModel('orbashipping/carrier_dhl');
                 break;
             case Orba_Shipping_Model_Carrier_Ups::CODE:
                 $model = Mage::getModel('orbashipping/carrier_ups');
                 break;
             default:
                 $model = Mage::getModel('orbashipping/carrier_default');
         }
         return $model;
     }
}