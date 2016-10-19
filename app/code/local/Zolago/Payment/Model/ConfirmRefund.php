<?php

/**
 * Class Zolago_Payment_Model_Refund
 */
class Zolago_Payment_Model_ConfirmRefund extends Zolago_Payment_Model_Abstract
{
    public function makeConfirmRefund($order, $transaction)
    {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        if($transaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND && //if is refund
            $transaction->getTxnStatus() == Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW && //and status is new
            $transaction->getTxnAmount() < 0) { //and amount is negative
            try {
                $transaction->setTxnStatus(Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED);
                $transaction->setIsClosed(1);
                $transaction->save();
                return true;
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
        return false;
    }
}