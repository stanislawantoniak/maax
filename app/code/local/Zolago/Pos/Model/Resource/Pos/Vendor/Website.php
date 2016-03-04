<?php

/**
 * Class Zolago_Pos_Model_Resource_Pos_Vendor_Website
 */
class Zolago_Pos_Model_Resource_Pos_Vendor_Website extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopos/pos_vendor_website', 'id');
    }

}