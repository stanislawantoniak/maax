<?php
class GH_Api_Model_Resource_Session_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct()
	{
		parent::_construct();
		$this->_init('ghapi/session');
	}

    public function filterByUserId($userId) {
        return $this->addFieldToFilter('user_id', $userId);
    }
}