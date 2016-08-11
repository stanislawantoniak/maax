<?php

/**
 * Resource for refund statement
 */
class GH_Statements_Model_Resource_Refund extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('ghstatements/refund','id');
    }

	/**
	 * assigns refunds statements to provided statement_id based on array of refund statements ids
	 * example:
	 * $statement_id = 5;
	 * $ids = array(1,2,3,4,5,6,7);
	 * @param $statement_id
	 * @param $ids
	 */
	public function assignToStatement($statement_id,$ids) {
		$writeConnection = $this->_getWriteAdapter();
		$writeConnection->update(
			$this->getTable('ghstatements/refund'),
			array('statement_id'=>$statement_id),
			"id IN (".implode(',',$ids).")");
	}
}