<?php
/**
 * stock website
 */
class Zolago_CatalogInventory_Model_Stock_Website
    extends Mage_Core_Model_Abstract {


    protected function _construct() {
        $this->_init('zolagocataloginventory/stock_website');
        return parent::_construct();
    }
}