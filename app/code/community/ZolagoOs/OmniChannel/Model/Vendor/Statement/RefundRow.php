<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Vendor_Statement_RefundRow extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'vendor_statement_refund)row';
    protected $_eventObject = 'statement_refund_row';

    protected function _construct()
    {
        $this->_init('udropship/vendor_statement_refundRow');
    }
}
