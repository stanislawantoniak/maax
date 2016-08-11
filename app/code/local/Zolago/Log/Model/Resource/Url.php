<?php

class Zolago_Log_Model_Resource_Url extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('log/url_table', 'url_id');
    }

}