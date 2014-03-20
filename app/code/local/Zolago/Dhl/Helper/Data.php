<?php

/**
 * helper for dhl module
 */
class Zolago_Dhl_Helper_Data extends Mage_Core_Helper_Abstract {
	protected $_dhlLogFile = 'dhl_tracking.log';
	protected $_dhlClient;
	protected $_dhlLogin;
	protected $_dhlPassword;
	
	const DHL_STATUS_DELIVERED	= 'DOR';
	const DHL_STATUS_RETURNED	= 'ZWN';
	const DHL_STATUS_WRONG		= 'AN';
	const DHL_HEADER				= 'DHL Tracking Info';
	const DHL_CARRIER_CODE		= 'zolagodhl';
	const USER_NAME_COMMENT		= 'API';
	
    public function isDhlEnabledForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
		return (bool)(int)$vendor->getUseDhl();
	}
    public function isDhlEnabledForPos(Zolago_Pos_Model_Pos $pos) {
		return (bool)(int)$pos->getUseDhl();
	}
	
	/**
	 * Initialize DHL Web API Client
	 * 
	 * @param array $dhlSettings Array('login' => 'value', 'password' => 'value')
	 * 
	 * @return Zolago_Dhl_Model_Client DHl Client
	 */
	public function startDhlClient($dhlSettings = false)
	{
		if ($this->_dhlLogin === null || $this->_dhlPassword === null || $this->_dhlClient === null) {
			if ($dhlSettings) {
				$this->_dhlLogin	= $dhlSettings['login'];
				$this->_dhlPassword	= $dhlSettings['password'];					
			} else {
				$this->_dhlLogin	= $this->getDhlLogin();
				$this->_dhlPassword	= $this->getDhlPassword();				
			}
			
			$dhlClient			= Mage::getModel('zolagodhl/client');
			$dhlClient->setAuth($this->_dhlLogin, $this->_dhlPassword);
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
		return Mage::getStoreConfig('carriers/zolagodhl/active');		
	}
	
	/**
	 * Get Dhl Login Data
	 * 
	 * @return string Dhl Login
	 */
	public function getDhlLogin()
	{
		return trim(Mage::getStoreConfig('carriers/zolagodhl/id'));		
	}

	/**
	 * Get Dhl Password Data
	 * 
	 * @return string Dhl Password
	 */	
	public function getDhlPassword()
	{
		return trim(Mage::getStoreConfig('carriers/zolagodhl/password'));		
	}
	
	/**
	 * Get Dhl Account Data: Used to Pay for Shipping Cost
	 * 
	 * @return string Dhl Account
	 */	
	public function getDhlAccount()
	{
		return trim(Mage::getStoreConfig('carriers/zolagodhl/account'));		
	}
	
	/**
	 * Get Dhl Default Weight
	 * 
	 * @return string Dhl Account
	 */	
	public function getDhlDefaultWeight()
	{
		return (int) ceil(Mage::getStoreConfig('carriers/zolagodhl/default_weight'));		
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
		$repeatIn = Mage::getStoreConfig('carriers/zolagodhl/repeat_tracking', $storeId);
		if ($repeatIn <= 0) {
			$repeatIn = 1;
		}
		$repeatIn = $repeatIn*60*60;
		return date('Y-m-d H:i:s', time()+$repeatIn);		
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
			->setUdropshipStatus($udpo->getUdropshipStatus())
			->setUsername($userName);
		$commentModel->save();
	}
}