<?php
/**
 * Marketing cost type
 */
class GH_Marketing_Model_Marketing_Cost_Type extends Mage_Core_Model_Abstract {
    
    protected function _construct()
    {
        $this->_init("ghmarketing/marketing_cost_type");
    }

    public function loadByCode($code) {
        return $this->getResourceCollection()->addFieldToFilter('code',$code)->getFirstItem();
    }

}
