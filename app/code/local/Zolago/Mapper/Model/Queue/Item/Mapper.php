<?php
class Zolago_Mapper_Model_Queue_Item_Mapper extends Zolago_Common_Model_Queue_ItemAbstract {

    public function _construct() {
        $this->_init('zolagomapper_queue_item/mapper');
    }
    
    public function setItemId($itemId) {
        $this->setMapperId($itemId);
    }
}
?>