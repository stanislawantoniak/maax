<?php
/**
 * is_in_stock by website
 */
class Zolago_CatalogInventory_Model_Resource_Stock_Website 
    extends  extends Mage_Core_Model_Resource_Db_Abstract {
    
    protected function _construct() {
        $this->_init('zolagocataloginventory/stock_website', 'product_id');              
    }
}