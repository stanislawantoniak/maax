<?php
/**
 * is_in_stock by website
 */
class Zolago_CatalogInventory_Model_Resource_Stock_Website 
    extends Mage_Core_Model_Resource_Db_Abstract {
    
    protected function _construct() {
        $this->_init('zolagocataloginventory/stock_website', 'product_id');              
    }
    public function saveStockWebsite(Mage_CatalogInventory_Model_Stock_Item $object)
    {
        $qty = $object->getWebsiteQty();
        $instock = $object->getInStock();
        $productId = $object->getProductId();
        $adapter = $this->_getWriteAdapter();
        foreach ($instock as $website=>$status) {
            $bind = array(
                'product_id' => $productId,
                'website_id' => $website,
                'is_in_stock'=> !empty($qty[$website])? $status:0,  // save only if qty > 0
            );
            $adapter->insertOnDuplicate($this->getMainTable(),$bind,array('is_in_stock'));
        }
        return $this;
    }

}