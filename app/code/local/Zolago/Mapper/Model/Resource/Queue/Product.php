<?php
/**
 * resource model for product queue
 */
class Zolago_Mapper_Model_Resource_Queue_Product extends Zolago_Common_Model_Resource_Queue_Abstract {
    protected function _construct() {
        $this->_init('zolagomapper/queue_product','queue_id');
    }
}

