<?php
/**
 * qty and is_in_pos by website
 */
class Zolago_CatalogInventory_Model_Resource_Stock_Item_Collection 
    extends Mage_CatalogInventory_Model_Resource_Stock_Item_Collection {
    
    public function joinStockWebsite($storeId) {
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $this->getSelect()
            ->joinLeft(
                array('stock_website' => $this->getTable('zolagocataloginventory/stock_website')),
                'stock_website.product_id = main_table.product_id AND '.
                    $this->getConnection()->quoteInto('stock_website.website_id = ?',$websiteId),
                array('ifnull(stock_website.is_in_stock,0) as website_is_in_stock')
            );        
        return $this;
    }
}