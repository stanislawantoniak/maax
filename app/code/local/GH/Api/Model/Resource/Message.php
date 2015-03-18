<?php
class GH_Api_Model_Resource_Message extends Mage_Core_Model_Resource_Db_Abstract {
	public function _construct() {
		$this->_init('ghapi/message', "message_id");
	}
}