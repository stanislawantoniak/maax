<?php
/**
 * Marketing cost model resource
 */
class GH_Marketing_Model_Resource_Marketing_Cost extends Mage_Core_Model_Resource_Db_Abstract {
    protected function _construct() {
        $this->_init('ghmarketing/marketing_cost', "marketing_cost_id");
    }
}