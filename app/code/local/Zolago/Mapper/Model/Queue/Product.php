<?php
/**
 * product queue model
 */
class Zolago_Mapper_Model_Queue_Product extends Zolago_Common_Model_Queue_Abstract {
    
    public function _construct() { 
        $this->_init('zolagomapper/queue_product');                
    }
    protected function _getItem() {
        return Mage::getModel('zolagomapper_queue_item/product');
    }
    protected function _execute() {
        // do nothing
    }
}
