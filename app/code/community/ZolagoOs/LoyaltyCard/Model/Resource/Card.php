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

}