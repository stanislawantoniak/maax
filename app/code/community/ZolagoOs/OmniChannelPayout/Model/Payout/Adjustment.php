<?php
/**
  
 */

class ZolagoOs_OmniChannelPayout_Model_Payout_Adjustment extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udpayout_payout_adjustment';
    protected $_eventObject = 'adjustment';

    protected function _construct()
    {
        $this->_init('udpayout/payout_adjustment');
        parent::_construct();
    }
}
