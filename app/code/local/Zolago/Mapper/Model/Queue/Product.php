<?php
/**
 * product queue model
 */
class Zolago_Mapper_Model_Queue_Product extends Zolago_Common_Model_Queue_Abstract {
    
    public function _construct() { 
        $this->_init('zolagomapper/queue_product');                
    }
    protected function _getItem() {
        return Mage::getModel('zolagomapper/queue_item_product');
    }
    protected function _execute() {
        $productList = array();
        foreach ($this->_collection as $item) {
            $productList[$item->getWebsiteId()][$this->getProductId()] 
                = $this->getProductId();
        }
        $indexer = Mage::getResourceModel('zolagomapper/index');
        $fullList = array();
        foreach ($productList as $websiteId=>$productList) {
            $indexer->reindexForProducts($productList,$websiteId);        
            $fullList = array_merge($fullList,$productList);
        }
        $list = array_unique($fullList);
        $indexer->assignWithCatalog($list);            

    }
}
