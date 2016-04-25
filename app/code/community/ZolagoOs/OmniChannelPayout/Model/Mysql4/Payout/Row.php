<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPayout_Model_Mysql4_Payout_Row extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('udpayout/payout_row', 'row_id');
    }
}