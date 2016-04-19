<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPayout_Model_Payout_Row extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udpayout_payout_row';
    protected $_eventObject = 'payout';

    protected function _construct()
    {
        $this->_init('udpayout/payout_row');
    }
}