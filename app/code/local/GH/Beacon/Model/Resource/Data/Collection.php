<?php

/**
 * Class GH_Beacon_Model_Resource_Data_Collection
 */
class GH_Beacon_Model_Resource_Data_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _construct() {
        parent::_construct();
        $this->_init('ghbeacon/data');
    }

    /**
     * @param $email
     * @return $this
     */
    public function addEmailFilter($email) {
        $this->addFieldToFilter('email', array('eq' => $email));
        return $this;
    }
}