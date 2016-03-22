<?php

/**
 * Locker object for InPost
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
 */
class GH_Inpost_Model_Locker extends Mage_Core_Model_Abstract {

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
}

