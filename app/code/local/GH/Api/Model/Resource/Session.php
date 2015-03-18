<?php
class GH_Api_Model_Resource_Session extends Mage_Core_Model_Resource_Db_Abstract {
	const GH_API_TOKEN_LENGTH = 64;

	public function _construct() {
		$this->_init('ghapi/session', "session_id");
	}
}