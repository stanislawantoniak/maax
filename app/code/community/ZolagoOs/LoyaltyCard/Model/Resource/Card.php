<?php

class ZolagoOs_LoyaltyCard_Model_Resource_Card extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zosloyaltycard/card', "card_id");
	}

}