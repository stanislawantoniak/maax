<?php

/**
 * Class Wf_OldStoreCustomer_Model_Resource_Customer_Collection
 */
class Wf_OldStoreCustomer_Model_Resource_Customer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('wfoldstorecustomer/customer');
    }

}