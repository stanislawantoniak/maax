<?php

/**
 * Class Wf_OldStoreCustomer_Model_Resource_Customer
 */
class Wf_OldStoreCustomer_Model_Resource_Customer extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('wfoldstorecustomer/customer', 'id');
    }

}