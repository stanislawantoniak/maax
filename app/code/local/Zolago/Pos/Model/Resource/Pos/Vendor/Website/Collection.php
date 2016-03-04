<?php

/**
 * Class Zolago_Pos_Model_Resource_Pos_Vendor_Website_Collection
 */
class Zolago_Pos_Model_Resource_Pos_Vendor_Website_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopos/pos_vendor_website');
    }

}