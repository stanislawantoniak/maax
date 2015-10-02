<?php

/**
 * Marketing cost model resource
 */
class GH_Marketing_Model_Resource_Marketing_Cost extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('ghmarketing/marketing_cost', "marketing_cost_id");
    }

    /**
     * Inserts multiple marketing costs at once
     * array should contain:
     * array(
     *      array(
     *          'vendor_id'     =>
     *          'product_id'    =>
     *          'date'          =>
     *          'type_id'       =>
     *          'cost'          =>
     *          'click_count'   =>
     *          'billing_cost'  => calculated cost for vendor
     *          'statement_id'  => always null so can be skipped (it's filled in another models - ghstatements)
     *      )
     * );
     * @param array $data
     */
    public function appendCosts($data)
    {
        $writeConnection = $this->_getWriteAdapter();
        $writeConnection->insertMultiple($this->getTable('ghmarketing/marketing_cost'), $data);
    }
}