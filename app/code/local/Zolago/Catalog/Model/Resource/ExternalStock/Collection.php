<?php

/**
 * Class Zolago_Catalog_Model_Resource_ExternalStock_Collection
 */
class Zolago_Catalog_Model_Resource_ExternalStock_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocatalog/external_stock');
    }

}