<?php

class ZolagoOs_LoyaltyCard_Model_Resource_Card extends Mage_Core_Model_Resource_Db_Abstract {

	/**
	 * Serializable field: additional_information
	 *
	 * @var array
	 */
	protected $_serializableFields   = array(
		'additional_information' => array(null, array())
	);

	protected function _construct() {
		$this->_init('zosloyaltycard/card', "card_id");
	}

	/**
	 * Check if object is unique in DB
	 * Unique key is card_number AND card_type 
	 * 
	 * @param ZolagoOs_LoyaltyCard_Model_Card $object
	 * @return bool
	 */
	public function isUnique($object) {
		/** @var ZolagoOs_LoyaltyCard_Model_Resource_Card_Collection $coll */
		$coll = Mage::getResourceModel('zosloyaltycard/card_collection');
		$coll->addCardNumberFilter($object->getCardNumber());
		$coll->addCardTypeFilter($object->getCardType());
		$elem = $coll->getData();
		$isUnique = empty($elem) || isset($elem[0]['card_id']) && $elem[0]['card_id'] == $object->getId();
		return $isUnique;
	}

}