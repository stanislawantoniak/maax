<?php
class Zolago_Adminhtml_PaymentController extends Mage_Adminhtml_Controller_Action
{
    protected $_error;
    protected $_success;
    protected $_skipped;
    protected $_paymentSkipped;
    protected $_paymentSkippedData;

    
    /**
     * refund one transaction action
     */

    public function refundAction() {
        $txnId = $this->getRequest()->getParam('txn_id');
        $this->_makeRefunds(array($txnId),"_refundTransaction");
    }
    
    /**
     * confirm one transaction action
     */
     public function confirmAction() {
        $txnId = $this->getRequest()->getParam('txn_id');
        $this->_makeRefunds(array($txnId),"_confirmRefund");
     }
    /**
     * clean counters
     */
     protected function _prepareCounters() {         
        $this->_skipped = 0;
        $this->_paymentSkipped = 0;
        $this->_paymentSkippedData = array();
        $this->_success = 0;
        $this->_error = 0;
     }
    
    
    /**
     * prepare messages after action
     */
    protected function _parseMessages() {
        if($this->_error) {
            $this->_getSession()->addError($this->__('%s transactions has not been refunded due to an error.', $this->_error));
        }
        if($this->_success) {
            $this->_getSession()->addSuccess($this->__('%s transactions has been successfully refunded.', $this->_success));
        }
        if($this->_skipped) {
            $this->_getSession()->addWarning($this->__('%s transactions has been skipped during refunding due to incorrect status or transaction type.', $this->_skipped));
        }
        if($this->_paymentSkipped) {
            $this->_getSession()->addWarning($this->__('%s transactions has been skipped during refunding due to incorrect payment method: '.implode(',', $this->_paymentSkippedData), $this->_paymentSkipped));
        }
    }
    /**
     * mass refunding transactions (only dotpay);
     */

    public function massRefundAction()
    {
        $transactions = $this->getRequest()->getParam('txn');
        $this->_makeRefunds($transactions,"_refundTransaction");
    }
    
    /**
     * mass confirm transaction (only not dotpay)
     */

    public function massConfirmRefundAction()
    {
        $transactions = $this->getRequest()->getParam('txn');
        $this->_makeRefunds($transactions,"_confirmRefund");
    }
    
    /**
     * start action
     */

    protected function _makeRefunds($transactions,$function) {

        $this->_prepareCounters();
        /** @var Zolago_Dotpay_Model_Client $dotpay */

        foreach ($transactions as $txnId) {
            $this->$function($txnId);
        }
        $this->_parseMessages();
        $this->_redirectReferer();
    }
    
    /**
     * refund one transaction (dotpay);
     */

    protected function _refundTransaction($txnId) {
        $dotpay = Mage::getModel("zolagodotpay/client");
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $transaction = Mage::getModel("sales/order_payment_transaction")->load($txnId);
        if ($transaction->getData('txn_type') == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND &&
                $transaction->getData('txn_status') == Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW &&
                $transaction->getData('is_closed') == "0"
           ) {

            $order = $transaction->getOrder();
            $payment = $order->getPayment();

            $transaction->setOrderPaymentObject($payment);

            //todo: if we'll add more payment providers handle refunds here

            $paymentMethod = $payment->getMethod();
            $sendEmail = false;
            switch ($paymentMethod) {
            case Zolago_Dotpay_Model_Client::PAYMENT_METHOD: //'dotpay'
                $dotpay->setStore($order->getStore());
                if ($dotpay->makeRefund($order, $transaction)) {
                    $sendEmail=true;
                    $this->_success++;
                } else {
                    $this->_error++;
                }
                break;
            default:
                $this->_paymentSkipped++;
                $this->_paymentSkippedData[] = $transaction->getTransactionId();
            }
            if($sendEmail) {
                //send refund done email
                /** @var Zolago_Payment_Helper_Data $paymentHelper */
                $paymentHelper = Mage::helper('zolagopayment');
                /** @var Zolago_Rma_Helper_Data $rmaHelper */
                $rmaHelper = Mage::helper('zolagorma');

                $email = $order->getCustomerEmail();
                $amount = $paymentHelper->getCurrencyFormattedAmount(abs($transaction->getTxnAmount()));

                $useAllocation = $paymentHelper->getConfigUseAllocation();

                $rmaId = $transaction->getRmaId();
                $rma = NULL;
                if($useAllocation === false) {
                    if($rmaId) {
                        $rma = Mage::getModel('zolagorma/rma')->load($rmaId);
                    }
                } else {
                    $rma = $paymentHelper->getTransactionRma($transaction);
                }
                if($rma) { //refund is for rma
                    if($paymentHelper->sendRmaRefundEmail(
                                $email,
                                $rma,
                                $amount,
                                $paymentMethod
                            )) {
                        //if email has been sent then add comments
                        $po = $rma->getPo();

                        $po->addComment($rmaHelper->__("Email about RMA refund was sent to customer (RMA id: %s, amount: %s)", $rma->getIncrementId(), $amount), false, true);
                        $rma->addComment($rmaHelper->__("Email about refund was sent to customer (Amount: %s)", $amount));

                        $po->saveComments();
                        $rma->saveComments();
                    }
                } else {                
                    if ($useAllocation === false) {
                        $poList = $order->getPoListByOrder();
                    } else {
                        $poList = array($paymentHelper->getTransactionPo($transaction));
                    }
                    if($paymentHelper->sendRefundEmail(
                                $email,
                                $order,
                                $amount,
                                $paymentMethod)
                      ) {
                        //if email has been sent then add comment
                        foreach ($poList as $po) {
                            $po->addComment($rmaHelper->__("Email about refund was sent to customer (Amount: %s)", $amount),false,true);
                            $po->saveComments();
                        }
                    }
                }
            }
        } else {
            $this->_skipped++;
        }

    }
    
    /**
     * confirm transaction (not dotpay)
     */

    protected function _confirmRefund($txnId) {
        /** @var Zolago_Dotpay_Model_Client $dotpay */
        $confirmRefund = Mage::getModel("zolagopayment/confirmRefund");
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $transaction = Mage::getModel("sales/order_payment_transaction")->load($txnId);
        if ($transaction->getData('txn_type') == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND &&
                $transaction->getData('txn_status') == Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW &&
                $transaction->getData('is_closed') == "0"
           ) {

            $order = $transaction->getOrder();
            $payment = $order->getPayment();

            $transaction->setOrderPaymentObject($payment);

            $paymentMethod = $payment->getMethod();
            $sendEmail = false;
            if($paymentMethod != Zolago_Dotpay_Model_Client::PAYMENT_METHOD) {
                if ($confirmRefund->makeConfirmRefund($order, $transaction)) {
                    $sendEmail=true;
                    $this->_success++;
                } else {
                    $this->_error++;
                }
            } else {
                $this->_paymentSkipped++;
                $this->_paymentSkippedData[] = $transaction->getTransactionId();
            }
            if($sendEmail) {
                //send refund done email
                /** @var Zolago_Payment_Helper_Data $paymentHelper */
                $paymentHelper = Mage::helper('zolagopayment');
                /** @var Zolago_Rma_Helper_Data $rmaHelper */
                $rmaHelper = Mage::helper('zolagorma');

                $email = $order->getCustomerEmail();
                $amount = $paymentHelper->getCurrencyFormattedAmount(abs($transaction->getTxnAmount()));

                $rmaId = $transaction->getRmaId();
                $rma = NULL;
                if($rmaId) {
                    $rma = Mage::getModel('zolagorma/rma')->load($rmaId);
                }

                if($rma) { //refund is for rma
                    if($paymentHelper->sendRmaRefundEmail(
                                $email,
                                $rma,
                                $amount,
                                $paymentMethod
                            )) {
                        //if email has been sent then add comments
                        $po = $rma->getPo();

                        $po->addComment($rmaHelper->__("Email about RMA refund was sent to customer (RMA id: %s, amount: %s)", $rma->getIncrementId(), $amount), false, true);
                        $rma->addComment($rmaHelper->__("Email about refund was sent to customer (Amount: %s)", $amount));

                        $po->saveComments();
                        $rma->saveComments();
                    }
                }
            }
        } else {
            $this->_skipped++;
        }
    }
}
