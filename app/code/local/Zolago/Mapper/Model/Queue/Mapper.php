<?php
/**
 * mapper queue model
 */
class Zolago_Mapper_Model_Queue_Mapper extends Zolago_Common_Model_Queue_Abstract {

    public function _construct() { 
        $this->_init('zolagomapper/queue_mapper');        
    }

    protected function _getItem() {
        return Mage::getModel('zolagomapper_queue_item/mapper');
    }
    
    protected function _execute() {
    }
}
