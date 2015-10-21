<?php

/**
 * Class Zolago_Payment_Model_Vendor_Invoice
 */
class Zolago_Payment_Model_Vendor_Invoice extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_invoice');
    }

}