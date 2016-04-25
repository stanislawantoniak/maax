<?php
/**
  
 */
 
class ZolagoOs_OmniChannelMicrosite_Model_Mysql4_Registration extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('umicrosite/registration', 'reg_id');
        #parent::_construct();
    }
}