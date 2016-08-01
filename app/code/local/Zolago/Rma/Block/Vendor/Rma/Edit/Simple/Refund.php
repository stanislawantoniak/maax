<?php

class Zolago_Rma_Block_Vendor_Rma_Edit_Simple_Refund extends Zolago_Rma_Block_New_Abstract
{
    public function getFormAction($id)
    {
        return $this->getUrl('*/*/makeSimpleRefund',array('id' => $id));
    }

    public function getPriceValue($price) {
        return Mage::getModel('directory/currency')->format(
            $price,
            array('display'=>Zend_Currency::NO_SYMBOL),
            false
        );
    }

    public function getSumExistTransactions(){
        $_rma = $this->getRma();
        $customerId = $_rma->getCustomerId();
        $order = $_rma->getOrder();
        $orderId = $order->getId();
        $paymentId = $_rma->getOrder()->getPayment()->getId();
        $existTransactions = Mage::getModel('sales/order_payment_transaction')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('txn_type', Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
            ->addFieldToFilter('payment_id', $paymentId);

        $amount = 0;
        foreach($existTransactions as $existTransaction){
            $amount +=  abs($existTransaction->getTxnAmount());
        }
        return $amount;
    }
}