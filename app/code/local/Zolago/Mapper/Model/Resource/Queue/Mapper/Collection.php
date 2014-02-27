<?php
class Zolago_Mapper_Model_Resource_Queue_Mapper_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct() {
        $this->_init('zolagomapper_queue_item/mapper');
    }
}