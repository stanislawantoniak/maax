<?php
/**
 * resource model for mapper queue
 */
class Zolago_Mapper_Model_Resource_Queue_Mapper extends Zolago_Common_Model_Resource_Queue_Abstract {
    protected function _construct() {
        $this->_init('zolagomapper/queue_mapper','queue_id');
    }
}

