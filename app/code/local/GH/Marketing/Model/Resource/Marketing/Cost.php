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

    /**
     * assigns marketing statements to provided statement_id based on array of marketing cost ids
     * example:
     * $statement_id = 5;
     * $ids = array(1,2,3,4,5,6,7);
     * @param $statement_id
     * @param $ids
     */
    public function assignToStatement($statement_id,$ids) {
        $writeConnection = $this->_getWriteAdapter();
        $writeConnection->update(
            $this->getTable('ghmarketing/marketing_cost'),
            array('statement_id' => $statement_id),
            'marketing_cost_id in('.implode(',',$ids).')'
        );
    }
}