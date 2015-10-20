<?php

/**
 * Class Zolago_Payment_Model_Vendor_Payment
 */
class Zolago_Payment_Model_Vendor_Payment extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_payment');
    }

}