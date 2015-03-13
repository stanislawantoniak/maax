<?php

/**
 * Class Zolago_Payment_Model_Observer
 */
class Zolago_Payment_Model_Observer
{

    public static function processRefunds()
    {
        /* @var $refundsModel Zolago_Payment_Model_Refund */
        $refundsModel = Mage::getModel("zolagopayment/refund");
        $collection = $refundsModel->getTransactionLastOverpayments();

        if (count($collection) > 0) {
            $orderModel = Mage::getModel('sales/order');
            //make refund transactions
            foreach ($collection as $item) {

                $amountToRefund = $item->getMaxAllocationAmount();
                $amount = - $amountToRefund;

                $orderId = $item->getOrderId();
                $parentTransactionId = $item->getTransactionId();

                $order = $orderModel->load($orderId);

                $status = Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW;

                /* @todo change to dotpay txn_id */
                $txnId = 'MODAGO_TEST_' . Mage::helper('zolagopayment')->RandomStringForRefund(20);

                $parentsTxtId = $item->getTxnId();
                $txnType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;

                /* @var $client Zolago_Dotpay_Model_Client */
                $client = Mage::getModel("zolagodotpay/client");
                $client->saveTransaction($order, $amount, $status, $txnId, $txnType, array(), '', $parentTransactionId,$parentsTxtId);

            }
        }
    }

}