<?php
class Zolago_Mapper_Model_Queue_Item_Product extends Zolago_Common_Model_Queue_ItemAbstract {

    public function _construct() {
        $this->_init('zolagomapper_queue_item/product');
    }
    
    public function setItemId($itemId) {
        $this->setProductId($itemId);
    }
}
?>