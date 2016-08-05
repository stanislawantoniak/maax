<?php

/**
 * Class ZolagoOs_Pwr_Model_Resource_Point_Collection
 */
class ZolagoOs_Pwr_Model_Resource_Point_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zospwr/point');
    }

}