<?php
class Zolago_Rma_Model_Resource_Rma_Reason_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{

    protected function _construct() {
        parent::_construct();
        $this->_init('zolagorma/rma_reason');
    }
	
	public function toOptionHash(){
		
		return $this->_toOptionHash('return_reason_id', 'name');
		
	}
}