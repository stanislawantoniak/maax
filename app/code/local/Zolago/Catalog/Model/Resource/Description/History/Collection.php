<?php

/**
 * Class Zolago_Catalog_Model_Resource_Description_History_Collection
 */
class Zolago_Catalog_Model_Resource_Description_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocatalog/description_history');
    }

}