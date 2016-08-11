<?php
/**
  
 */
 
class ZolagoOs_OmniChannel_Model_Vendor_Statement_Row extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'vendor_statement_row';
    protected $_eventObject = 'statement_row';

    protected function _construct()
    {
        $this->_init('udropship/vendor_statement_row');
    }
}
