<?php

/**
 * Class Zolago_Payment_Model_Resource_Vendor_Invoice
 */
class Zolago_Payment_Model_Resource_Vendor_Invoice extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_invoice', 'vendor_invoice_id');
    }
}