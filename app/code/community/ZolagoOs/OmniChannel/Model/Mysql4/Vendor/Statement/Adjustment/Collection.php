<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Statement_Adjustment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_statement_adjustment');
        parent::_construct();
        $this->_setIdFieldName('adjustment_id');
    }
}
