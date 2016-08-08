<?php

/**
 * Class ZolagoOs_LoyaltyCard_Model_Card
 * 
 * @method string getCardId()
 * @method string getStoreId()
 * @method string getVendorId()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getEmail()
 * @method string getCardNumber()
 * @method string getShopCode()
 * @method string getOperatorId()
 * @method string getCardType()
 * @method string getExpireDate()
 * @method string getAdditionalInformation()
 * @method string getFirstName()
 * @method string getSurname()
 * @method string getSex()
 * @method string getTelephoneNumber()
 * 
 * @method ZolagoOs_LoyaltyCard_Model_Resource_Card getResource()
 * 
 * @method $this setCardId($value)
 * @method $this setStoreId($value)
 * @method $this setVendorId($value)
 * @method $this setCreatedAt($value)
 * @method $this setUpdatedAt($value)
 * @method $this setEmail($value)
 * @method $this setCardNumber($value)
 * @method $this setShopCode($value)
 * @method $this setOperatorId($value)
 * @method $this setCardType($value)
 * @method $this setExpireDate($value)
 * @method $this setAdditionalInformation($value)
 * @method $this setFirstName($value)
 * @method $this setSurname($value)
 * @method $this setSex($value)
 * @method $this setTelephoneNumber($value)
 */
class ZolagoOs_LoyaltyCard_Model_Card extends Mage_Core_Model_Abstract {

	/**
	 * Prefix of model events names
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'loyalty_card';

	const DELETE_ONLY_CARD         = "delete-only-card";
	const DELETE_WITH_SUBSCRIPTION = "delete-with-subscription";

	protected function _construct() {
		$this->_init("zosloyaltycard/card");
	}

	protected function _beforeSave() {
		$this->beforeSaveValidation();
		$date = Mage::getSingleton('core/date')->gmtDate();
		if (!$this->hasCreatedAt()) {
			$this->setCreatedAt($date);
		}
		$this->setUpdatedAt($date);

		$this->setAdditionalInformation($this->getRawAdditionalInformation());

		return parent::_beforeSave();
	}

	/**
	 * check if card is unique (card_number + card_type)
	 */
	public function beforeSaveValidation() {
		$isUnique = $this->getResource()->isUnique($this);
		if (!$isUnique) {
			Mage::throwException(Mage::helper("zosloyaltycard")->__("Card with such number and type already exist"));
		}
	}

	protected function _afterLoad() {
		$this->addData($this->getAdditionalInformation());
		return parent::_afterLoad();
	}
	
	protected function getRawAdditionalInformation() {
		$desc = $this->getResource()->getReadConnection()->describeTable($this->getResource()->getMainTable());
		$data = $this->getData();
		foreach ($desc as $key => $spec) {
			unset($data[$key]);
		}
		return $data;
	}
}