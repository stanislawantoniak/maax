<?php

/**
 * Locker object for InPost
 * 
 * @method GH_Inpost_Model_Resource_Locker getResource()
 * 
 * @method string getId()
 * @method string getName()
 * @method string getType()
 * @method string getPostcode()
 * @method string getProvince()
 * @method string getStreet()
 * @method string getBuildingNumber()
 * @method string getTown()
 * @method string getLatitude()
 * @method string getLongitude()
 * @method string getPaymentAvailable()
 * @method string getOperatingHours()
 * @method string getLocationDescription()
 * @method string getLocationDescription2()
 * @method string getPaymentPointDescription()
 * @method string getPartnerId()
 * @method string getPaymentType()
 * @method string getStatus()
 * @method string getIsActive()
 * @method string getUpdatedAt()
 * 
 * @method $this setId($value)
 * @method $this setName($value)
 * @method $this setType($value)
 * @method $this setPostcode($value)
 * @method $this setProvince($value)
 * @method $this setStreet($value)
 * @method $this setBuildingNumber($value)
 * @method $this setTown($value)
 * @method $this setLatitude($value)
 * @method $this setLongitude($value)
 * @method $this setPaymentAvailable($value)
 * @method $this setOperatingHours($value)
 * @method $this setLocationDescription($value)
 * @method $this setLocationDescription2($value)
 * @method $this setPaymentPointDescription($value)
 * @method $this setPartnerId($value)
 * @method $this setPaymentType($value)
 * @method $this setStatus($value)
 * @method $this setIsActive($value)
 * @method $this setUpdatedAt($value)
 */
class GH_Inpost_Model_Locker extends Mage_Core_Model_Abstract {

	/** pick up point */
	const TYPE_POK						= 'POK';			// Punkty odbioru
	const TYPE_PACK_MACHINE				= 'Pack Machine';	// Paczkomaty
	/** payment available */
	const PAYMENT_AVAILABLE				= 1;				// obsługujących pobrania
	const PAYMENT_NOT_AVAILABLE			= 0;				// nie obsługujących pobrań
	/** payment type */
	const PAYMENT_TYPE_NOT_AVAILABLE	= 0; 				// brak możliwości zapłaty za przesyłkę
	const PAYMENT_TYPE_CASH				= 1; 				// możliwość zapłaty za przesyłkę gotówką
	const PAYMENT_TYPE_CC				= 2; 				// możliwość zapłaty za przesyłkę kartą płatniczą
	const PAYMENT_TYPE_CASH_AND_CC		= 3; 				// możliwość zapłaty za przesyłkę gotówką bądź kartą płatniczą
	
	protected function _construct() {
		$this->_init('ghinpost/locker');
		parent::_construct();
	}

	/**
	 * @param $name
	 * @return GH_Inpost_Model_Locker
	 */
	public function loadByLockerName($name) {
		$this->load($name, 'name');
		return $this;
	}

	protected function _beforeSave() {
		$this->setUpdatedAt(Mage::getSingleton('core/date')->timestamp());
		return parent::_beforeSave();
	}

	/**
	 * Better performance on mass single object save
	 * 
	 * @return bool
	 */
	public function hasDataChanges() {
		$res = $this->getResource();
		$return = $res->hasDataChanged($this);
		return $return;
	}


}

