<?php

/**
 * Class GH_Integrator_Model_Resource_Log
 */
class GH_Integrator_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('ghintegrator/log','id');
    }
}