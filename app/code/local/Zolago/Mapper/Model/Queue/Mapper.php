<?php
/**
 * mapper queue model
 */
class Zolago_Mapper_Model_Queue_Mapper extends Zolago_Common_Model_Queue_Abstract {
    protected $itemName = 'mapper_id';
    public function _construct() { 
        $this->_init('zolagomapper/queue_mapper');        
    }
    public function push($itemId) {
    }
}
