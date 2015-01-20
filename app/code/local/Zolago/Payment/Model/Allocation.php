<?php

class Zolago_Payment_Model_Allocation extends Mage_Core_Model_Abstract {

    const ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT   = 'payment';
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY   = 'overpay'; // nadplata
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_UNDERPAID = 'underpaid'; // niedoplata

    protected function _construct() {
        $this->_init('zolagopayment/allocation');
    }

    public function importDataFromTransaction($transaction) {

        if($transaction instanceof Mage_Sales_Model_Order_Payment_Transaction) {
            if ($transaction->getId() && $transaction->getStatus() == Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED) {
                /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
                $allocation_type = Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT;
                $operator_id = null;
                /** @var Mage_Sales_Model_Order_Payment $payment */
                $payment = Mage::getModel("sales/order_payment")->load($transaction->getPaymentId());
                $comment = $payment->getMethod();

                $data = $this->getResource()->getDataAllocationForTransaction($transaction, $allocation_type, $operator_id, $comment);
                $this->appendAllocations($data);
            }
        }
    }

    /**
     * params data as:
     * array(
     *    'transaction_id'    => $transaction_id,
     *    'po_id'             => $po_id,
     *    'allocation_amount' => $allocation_amount,
     *    'allocation_type'   => $allocation_type,
     *    'operator_id'       => $operator_id,
     *    'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
     *    'comment'           => $comment
     *    'customer_id'       => $po['customer_id']));
     *
     * @param $data
     */
    public function appendAllocations($data) {
        foreach($data as $allocationData) {
            $allocation = Mage::getModel("zolagopayment/allocation");
            $allocation->setData($allocationData);
            $allocation->save();
        }
    }

}