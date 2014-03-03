<?php
class Zolago_Mapper_Model_Queue_Item_Product extends Zolago_Common_Model_Queue_ItemAbstract {

    public function _construct() {
        $this->_init('zolagomapper_queue_item/product');
    }
    
    public function setItemId($itemId) {
        if (!is_array($itemId) 
            || !isset($itemId['product_id'])) {
            Mage::throwException(Mage::helper('zolagomapper')->__('Error: wrong type of queue element'));
        }
        $this->setProductId($itemId['product_id']);
        $this->setWebsiteId(isset($itemId['website_id'])? $itemId['website_id']:0);
    }
}
?>