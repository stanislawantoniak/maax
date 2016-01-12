<?php
/**
 * Marketing cost type
 *
 * @method string getMarketingCostTypeId()
 * @method $this setMarketingCostTypeId($id)
 * @method string getCode()
 * @method $this setCode($code)
 * @method string getName()
 * @method $this setName($name)
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
