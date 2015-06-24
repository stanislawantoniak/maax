<?php

class GH_Dhl_Model_Resource_Dhl_Vendor_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('ghdhl/dhl_vendor');
    }

}