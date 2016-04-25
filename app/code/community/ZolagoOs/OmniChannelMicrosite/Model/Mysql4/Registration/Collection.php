<?php
/**
  
 */
 
class ZolagoOs_OmniChannelMicrosite_Model_Mysql4_Registration_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('umicrosite/registration');
        parent::_construct();
    }
}