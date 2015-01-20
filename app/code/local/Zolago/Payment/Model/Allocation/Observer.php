<?php

class Zolago_Payment_Model_Allocation_Observer {

    /**
     *
     * @param Varien_Event_Observer $observer Mage_Sales_Model_Order_Payment_Transaction
     */
    public function salesOrderPaymentTransactionSaveAfter(Varien_Event_Observer $observer) {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $observer */
        Mage::getModel("zolagopayment/allocation")->importDataFromTransaction($observer);

    }
}