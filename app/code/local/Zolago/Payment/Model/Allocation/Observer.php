<?php

class Zolago_Payment_Model_Allocation_Observer {

    public function __construct() {

    }

    public function allocationTransaction(Varien_Event_Observer $observer) {
        $transaction_id = $observer->getData('transaction_id');
        $allocation_type = $observer->getData('allocation_type');
        $operator_id = $observer->getData('operator_id');
        $comment = $observer->getData('comment');
        if(!empty($transaction_id) && !empty($allocation_type)) {
            /** @var Zolago_Payment_Model_Allocation $model */
            $model = Mage::getModel('zolagopayment/allocation');
            $model->allocationTransaction($transaction_id, $allocation_type, $operator_id , $comment);
        }
    }
}