<?php
/**
 * Class Zolago_CatalogInventory_Model_Resource_Stock_Website_Collection
  */
class Zolago_CatalogInventory_Model_Resource_Stock_Website_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _construct() {
        parent::_construct();
        $this->_init('zolagocataloginventory/stock_website');
    }
    public function setRowIdFieldName($field) {
        return $this->_setIdFieldName($field);
    }
}

