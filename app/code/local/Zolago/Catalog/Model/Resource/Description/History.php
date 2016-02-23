<?php

/**
 * Class Zolago_Catalog_Model_Resource_Description_History
 */
class Zolago_Catalog_Model_Resource_Description_History extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocatalog/description_history', 'history_id');
    }

}