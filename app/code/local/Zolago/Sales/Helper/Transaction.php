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

    public function createRefundTransaction($order,$customerId,$amount,$customerAccount = null,$rmaId = null) {
        return $this->_createTransaction($order,$customerId,$amount,$customerAccount,Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,0,Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW,$rmaId);
    }
    
    /**
     * create transaction 
     * @param 
     * @return 
     */

    protected function _createTransaction($order,$customerId,$amount,$customerAccount,$type,$closed,$status,$rmaId) {
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
            ->setRmaId($rmaId)
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

    public function createDeliveryTransaction($order,$customerId,$amount,$rmaId) {
        return $this->_createTransaction($order,$customerId,$amount,'',Zolago_Sales_Model_Order_Payment_Transaction::TYPE_DELIVERY_CHARGE,1,Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED,$rmaId);                
    }
    
    
    
    /**
     * get charge delivery value from rma
     * @return float;
     */
     public function getChargeDeliveryTransactionValue($rmaId) {
        $existRefunds = Mage::getModel('sales/order_payment_transaction')->getCollection()
                        ->addFieldToFilter('rma_id', $rmaId)
                        ->addFieldToFilter('txn_type', Zolago_Sales_Model_Order_Payment_Transaction::TYPE_DELIVERY_CHARGE);
        $sum = 0;
        foreach ($existRefunds as $item) {
            $sum += $item->getData('txn_amount');
        }        
        return $sum;         
     }
 
    /**
     * check if charge transaction exists in rma
     * @return bool (ok if not exists)
     */
     public function checkChargeTransaction($rma) {	
        $rmaId = $rma->getId();
        $existRefunds = Mage::getModel('sales/order_payment_transaction')->getCollection()
                        ->addFieldToFilter('rma_id', $rmaId)
                        ->addFieldToFilter('txn_type', Zolago_Sales_Model_Order_Payment_Transaction::TYPE_DELIVERY_CHARGE);
        return ($existRefunds->count() === 0); 

     }

}