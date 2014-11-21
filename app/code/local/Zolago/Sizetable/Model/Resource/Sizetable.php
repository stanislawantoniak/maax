<?php

class Zolago_Sizetable_Model_Resource_Sizetable extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagosizetable/sizetable','sizetable_id');
    }

    public function getSizetableCMS($vendor_id, $store_id, $attribute)
    {
        error_reporting(E_ALL);
        ini_set("display_errors", 1);


        $resource = Mage::getSingleton('zolagosizetable/resource');
        $read = $resource->getConnection('core_read');

        $tableName = $resource->getTableName('catalog/product');


        $query = 'SELECT * FROM ' . $tableName;

        $results = $read->fetchAll($query);


        return $results;
    }
}
