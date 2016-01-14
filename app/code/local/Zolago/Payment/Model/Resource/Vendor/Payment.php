<?php

/**
 * Class Zolago_Payment_Model_Resource_Vendor_Payment
 */
class Zolago_Payment_Model_Resource_Vendor_Payment extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_payment', 'vendor_payment_id');
    }

}