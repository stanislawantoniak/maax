<?php

class Zolago_Payment_Model_Allocation extends Mage_Core_Model_Abstract {

    const ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT   = 'payment';
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY   = 'overpay'; // nadplata
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_UNDERPAID = 'underpaid'; // niedoplata

    protected function _construct() {
        $this->_init('zolagopayment/allocation');
    }

    public function allocationTransaction($transaction_id, $allocation_type, $operator_id = null, $comment = '') {
        Mage::log("allocationTransaction",null,"allocation.log");
        $data = $this->getResource()->getDataAllocationForTransaction($transaction_id, $allocation_type, $operator_id, $comment);
        $this->getResource()->appendAllocations($data);

    }

}