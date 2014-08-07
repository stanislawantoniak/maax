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
	 * Initialize DHL Web API Client
	 * 
	 * @param array $dhlSettings Array('login' => 'value', 'password' => 'value','account' => 'value')
	 * 
	 * @return Zolago_Dhl_Model_Client DHl Client
	 */
	public function startDhlClient($dhlSettings = false)
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
	 * Check if Dhl is Active
	 * 
	 * @return boolean Dhl Service State
	 */
	public function isDhlActive()
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
		return trim(Mage::getStoreConfig('carriers/orbadhl/id'));		
	}

	/**
	 * Get Dhl Password Data
	 * 
	 * @return string Dhl Password
	 */	
	public function getDhlPassword()
	{
		return trim(Mage::getStoreConfig('carriers/orbadhl/password'));		
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
		return date('Y-m-d H:i:s', time()+$repeatIn);		
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
	
    public function addUdpoComment($udpo, $comment, $isVendorNotified=false, $visibleToVendor=false, $userName = false)
    {
		if (!$userName) {
			$userName = self::USER_NAME_COMMENT;
		}
		$commentModel = Mage::getModel('udpo/po_comment')
			->setParentId($udpo->getId())
			->setComment($comment)
			->setCreatedAt(now())
			->setIsVendorNotified($isVendorNotified)
			->setIsVisibleToVendor($visibleToVendor)
			->setUdropshipStatus(Mage::helper("udpo")->getUdpoStatusName($udpo))
			->setUsername($userName);
		$commentModel->save();
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
}