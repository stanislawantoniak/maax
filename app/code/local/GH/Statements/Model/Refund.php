<?php
/**
 * Class GH_Statements_Model_Refund
 * @method int getId()
 * @method GH_Statements_Model_Refund setId(int $id)
 * @method int getStatementId()
 * @method GH_Statements_Model_Refund setStatementId(int $statement_id)
 * @method int getPoId()
 * @method GH_Statements_Model_Refund setPoId(int $po_id)
 * @method string getPoIncrementId()
 * @method GH_Statements_Model_Refund setPoIncrementId(string $po_increment_id)
 * @method int getRmaId()
 * @method GH_Statements_Model_Refund setRmaId(int $rma_id)
 * @method string getRmaIncrementId()
 * @method GH_Statements_Model_Refund setRmaIncrementId(string $rma_increment_id)
 * @method string getDate()
 * @method GH_Statements_Model_Refund setDate(string $date)
 * @method int getOperatorId()
 * @method GH_Statements_Model_Refund setOperatorId(int $operator_id)
 * @method string getOperatorName()
 * @method GH_Statements_Model_Refund setOperatorName(string $operator_name)
 * @method int getVendorId()
 * @method GH_Statements_Model_Refund setVendorId(int $vendor_id)
 * @method float getValue()
 * @method GH_Statements_Model_Refund setValue(float $value)
 * @method float getRegisteredValue()
 * @method GH_Statements_Model_Refund setRegisteredValue(float $registered_value)
 */

class GH_Statements_Model_Refund extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('ghstatements/refund');
        parent::_construct();
    }
}

