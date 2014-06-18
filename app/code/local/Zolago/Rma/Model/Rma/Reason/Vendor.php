<?php
class Zolago_Rma_Model_Rma_Reason_Vendor extends Mage_Core_Model_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/rma_reason_vendor');
    }

	/**
     * 
     * @return Zolago_Rma_Model_ReturnReason
     */
	public function getReturnReason(){
		
		return Mage::getModel('zolagorma/rma_reason')->load($this->getReturnReasonId());
		
	}
}