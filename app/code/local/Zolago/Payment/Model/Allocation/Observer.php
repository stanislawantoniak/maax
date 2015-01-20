<?php

class Zolago_Payment_Model_Allocation_Observer {

    /**
     *
     * @param Varien_Event_Observer $observer Mage_Sales_Model_Order_Payment_Transaction
     */
    public function salesOrderPaymentTransactionSaveAfter(Varien_Event_Observer $observer) {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $observer */

        Mage::log("salesOrderPaymentTransactionSaveAfter START", null, "aloc.log");

        Mage::log($observer->getData());
        Mage::log("salesOrderPaymentTransactionSaveAfter START---", null, "aloc.log");
        $transaction = $observer->getData('transaction');
        $allocation_type = $observer->getData('allocation_type');
        $operator_id = $observer->getData('operator_id');
        $comment = $observer->getData('comment');


        Mage::getModel("zolagopayment/allocation")->importDataFromTransaction($transaction, $allocation_type, $operator_id, $comment);
        Mage::log("salesOrderPaymentTransactionSaveAfter end", null, "aloc.log");
    }
}