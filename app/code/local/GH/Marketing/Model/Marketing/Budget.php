<?php
/**
 * Marketing cost
 *
 * @method string getMarketingBudgetId()
 * @method string getVendorId()
 * @method string getMarketingCostTypeId()
 * @method string getDate()
 * @method string getBudget()
 */
class GH_Marketing_Model_Marketing_Budget extends Mage_Core_Model_Abstract {
    
    protected function _construct()
    {
        $this->_init("ghmarketing/marketing_budget");
    }

}
