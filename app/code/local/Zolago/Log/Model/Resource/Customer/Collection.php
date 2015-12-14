<?php

/**
 * Log customer collection
 */
class Zolago_Log_Model_Resource_Customer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    /**
     * Initialize collection model
     */
    protected function _construct() {
        $this->_init('log/customer');
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId) {
        $this->addFieldToFilter('customer_id', array('eq' => $customerId));
        return $this;
    }
}
