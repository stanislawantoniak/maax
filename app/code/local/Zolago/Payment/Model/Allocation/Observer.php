<?php

class Zolago_Payment_Model_Allocation_Observer {

    /**
     * Fires when payment transaction is saved
     * to import data from transaction to create allocations
     *
     * @param Varien_Event_Observer $observer Mage_Sales_Model_Order_Payment_Transaction
     */
    public function salesOrderPaymentTransactionSaveAfter(Varien_Event_Observer $observer) {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $observer */

        $transaction = $observer->getData('transaction');
        $allocation_type = $observer->getData('allocation_type');
        $operator_id = $observer->getData('operator_id');
        $comment = $observer->getData('comment');

		/** @var Zolago_Payment_Model_Allocation $model */
		$model = Mage::getModel("zolagopayment/allocation"); 
        $model->importDataFromTransaction($transaction, $allocation_type, $operator_id, $comment);
    }
}