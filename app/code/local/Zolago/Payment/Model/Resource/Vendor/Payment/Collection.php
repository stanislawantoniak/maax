<?php

/**
 * Class Zolago_Payment_Model_Resource_Vendor_Payment_Collection
 */
class Zolago_Payment_Model_Resource_Vendor_Payment_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_payment');
    }

}