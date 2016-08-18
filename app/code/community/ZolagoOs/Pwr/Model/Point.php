<?php

/**
 * Class ZolagoOs_Pwr_Model_Point
 *
 * @method string getName()
 * @method string getIsActive()
 * @method string getType()
 * @method string getProvince()
 * @method string getStreet()
 * @method string getDistrict()
 * @method string getBuildingNumber()
 * @method string getTown()
 * @method string getLatitude()
 * @method string getLongitude()
 * @method string getOperatingHours()
 * @method string getLocationDescription()
 * @method string getUpdatedAt()
 * @method string getPaymentAvailable()
 * @method string getPaymentType()
 * @method string getPsd()
 * 
 * @method ZolagoOs_Pwr_Model_Point setName($value)
 * @method ZolagoOs_Pwr_Model_Point setIsActive($value)
 * @method ZolagoOs_Pwr_Model_Point setType($value)
 * @method ZolagoOs_Pwr_Model_Point setProvince($value)
 * @method ZolagoOs_Pwr_Model_Point setStreet($value)
 * @method ZolagoOs_Pwr_Model_Point setDistrict($value)
 * @method ZolagoOs_Pwr_Model_Point setBuildingNumber($value)
 * @method ZolagoOs_Pwr_Model_Point setTown($value)
 * @method ZolagoOs_Pwr_Model_Point setLatitude($value)
 * @method ZolagoOs_Pwr_Model_Point setLongitude($value)
 * @method ZolagoOs_Pwr_Model_Point setOperatingHours($value)
 * @method ZolagoOs_Pwr_Model_Point setLocationDescription($value)
 * @method ZolagoOs_Pwr_Model_Point setUpdatedAt($value)
 * @method ZolagoOs_Pwr_Model_Point setPaymentAvailable($value)
 * @method ZolagoOs_Pwr_Model_Point setPaymentType($value)
 * @method ZolagoOs_Pwr_Model_Point setPsd($value)
 */
class ZolagoOs_Pwr_Model_Point extends Mage_Core_Model_Abstract {

	/** types */
	const TYPE_PSD						= 'PSD';			// ??
	const TYPE_PSP						= 'PSP';			// ??
	/** payment available */
	const PAYMENT_AVAILABLE				= 1;				// obsługujących pobrania
	const PAYMENT_NOT_AVAILABLE			= 0;				// nie obsługujących pobrań
	/** payment type */
	const PAYMENT_TYPE_NOT_AVAILABLE	= 0; 				// brak możliwości zapłaty za przesyłkę
	const PAYMENT_TYPE_CASH				= 1; 				// możliwość zapłaty za przesyłkę gotówką

	const STATUS_ACTIVE					= 1;
	const STATUS_INACTIVE				= 0;
	
	
	protected function _construct() {
		$this->_init('zospwr/point');
		parent::_construct();
	}

	/**
	 * @param $name
	 * @return GH_Inpost_Model_Locker
	 */
	public function loadByName($name) {
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

	/**
	 * Retrieve shipping address info
	 *
	 * @return array
	 */
	public function getShippingAddress() {
		$data['street'][]	= trim($this->getStreet());
		$data['postcode']	= $this->getPostcode();
		$data['city']		= $this->getTown();
		$data['country_id']	= $this->getCountryId();
		$data['region_id']	= $this->getRegionId();
		$data['region']		= $this->getRegion();
		return $data;
	}

	/**
	 * Temporary only in Poland
	 *
	 * @return string
	 */
	public function getCountryId() {
		return 'PL';
	}
}