<?php

class GH_Dhl_Model_Resource_Dhl_Vendor extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('ghdhl/dhl_vendor', "id");
    }
}