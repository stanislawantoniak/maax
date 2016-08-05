<?php

/**
 * Class ZolagoOs_Pwr_Model_Resource_Point
 */
class ZolagoOs_Pwr_Model_Resource_Point extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zospwr/point', 'id');
    }

}