<?php
/**
  
 */

class ZolagoOs_OmniChannelPayout_Model_Mysql4_Payout_Adjustment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udpayout/payout_adjustment');
        parent::_construct();
        $this->_setIdFieldName('adjustment_id');
    }
}
