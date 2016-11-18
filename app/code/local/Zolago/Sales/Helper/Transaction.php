<?php

class Zolago_Sales_Helper_Transaction extends Mage_Core_Helper_Abstract
{
    
    /**
     * find existing transaction for order
     */

    public function getExistingTransaction($order,$customerId) {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $existTransaction */
        $existTransactionCollection = $this->_getTransactionList($order,$customerId,Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
        $existTransaction = $existTransactionCollection->getFirstItem();
        return $existTransaction;

    }
    
    /**
     * prepare transaction collecttion
     */

    protected function _getTransactionList($order,$customerId,$type) {
        $orderId = $order->getId();
        $paymentId = $order->getPayment()->getId();
        $existTransactionCollection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                                      ->addFieldToFilter('order_id', $orderId)
                                      ->addFieldToFilter('txn_type', $type)
                                      ->addFieldToFilter('payment_id', $paymentId);
        return $existTransactionCollection;
    }
    
    /**
     * create refund transaction
     */

    public function createRefundTransaction($order,$customerId,$amount,$customerAccount = null) {
        return $this->_createTransaction($order,$customerId,$amount,$customerAccount,Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,0,Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW);
    }
    
    /**
     * create transaction 
     * @param 
     * @return 
     */

    protected function _createTransaction($order,$customerId,$amount,$customerAccount,$type,$closed,$status) {
        $transaction = Mage::getModel("sales/order_payment_transaction");
        $transaction->setOrderPaymentObject($order->getPayment());

        $existTransaction = $this->getExistingTransaction($order,$customerId);

        $transaction
            ->setTxnId(uniqid())
            ->setTxnType($type)
            ->setIsClosed($closed)
            ->setTxnAmount($amount)
            ->setTxnStatus($status)
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
    
    /**
     * create transaction for delivery charge in rma
     * @param 
     * @return 
     */

    public function createDeliveryTransaction($order,$customerId,$amount) {
        return $this->_createTransaction($order,$customerId,$amount,'',Zolago_Sales_Model_Order_Payment_Transaction::TYPE_DELIVERY_CHARGE,1,Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED);                
    }
}