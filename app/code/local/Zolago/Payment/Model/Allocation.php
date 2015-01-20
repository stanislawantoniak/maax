<?php

class Zolago_Payment_Model_Allocation extends Mage_Core_Model_Abstract {

    const ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT   = 'payment';
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY   = 'overpay'; // nadplata
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_UNDERPAID = 'underpaid'; // niedoplata

    protected function _construct() {
        $this->_init('zolagopayment/allocation');
    }

    /**
     * @param $transaction Mage_Sales_Model_Order_Payment_Transaction
     * @param $allocation_type
     * @param $operator_id
     * @param $comment
     */
    public function importDataFromTransaction($transaction, $allocation_type, $operator_id = null, $comment = '') {

        Mage::log("start importDataFromTransaction", null, "aloc.log");
        Mage::log($transaction->getId(),null, "aloc.log");
        Mage::log($allocation_type,null, "aloc.log");
        Mage::log($operator_id,null, "aloc.log");

        if($transaction instanceof Mage_Sales_Model_Order_Payment_Transaction) {

            if ($transaction->getId() && $transaction->getStatus() == Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED) {
                Mage::log("is", null, "aloc.log");
                if (empty($comment)) {
                    /** @var Mage_Sales_Model_Order_Payment $payment */
                    $payment = Mage::getModel("sales/order_payment")->load($transaction->getPaymentId());
                    $comment = $payment->getMethod();
                }
                Mage::log($comment,null, "aloc.log");
                $data = $this->getResource()->getDataAllocationForTransaction($transaction, $allocation_type, $operator_id, $comment);
                Mage::log($data,null, "aloc.log");
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