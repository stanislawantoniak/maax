<?php

class Zolago_Sales_Helper_Transaction extends Mage_Core_Helper_Abstract
{
    public function getExistingTransaction($order,$customerId) {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $existTransaction */
        $existTransactionCollection = $this->_getTransactionList($order,$customerId,Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
        $existTransaction = $existTransactionCollection->getFirstItem();
        return $existTransaction;

    }
    protected function _getTransactionList($order,$customerId,$type) {
        $orderId = $order->getId();
        $paymentId = $order->getPayment()->getId();
        $existTransactionCollection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                                      ->addFieldToFilter('order_id', $orderId)
                                      ->addFieldToFilter('txn_type', $type)
                                      ->addFieldToFilter('payment_id', $paymentId);
        return $existTransactionCollection;
    }
    public function createRefundTransaction($order,$customerId,$amount,$customerAccount = null) {
        $transaction = Mage::getModel("sales/order_payment_transaction");
        $transaction->setOrderPaymentObject($order->getPayment());

        $existTransaction = $this->getExistingTransaction($order,$customerId);

        $transaction
            ->setTxnId(uniqid())
            ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
            ->setIsClosed(0)
            ->setTxnAmount($amount)
            ->setTxnStatus(Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW)
            ->setOrderId($order->getId())
            ->setCustomerId($customerId);
        if ($customerAccount) {
            $transaction->setBankAccount($customerAccount);
        }
        if($existTransaction->getId()) {
            $transaction
                ->setParentId($existTransaction->getTransactionId())
                ->setParentTxnId($existTransaction->getTxnId(), $transaction->getTransactionId())
                ->setDotpayId($existTransaction->getDotpayId());
        } else {
            $transaction
                ->setPaymentId($order->getPayment()->getId());
        }
        return $transaction->save();

    }
}