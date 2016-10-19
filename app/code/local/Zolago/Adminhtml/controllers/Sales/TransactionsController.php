<?php
/**
 * Zolago_Adminhtml_Sales_TransactionsController
 */
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'TransactionsController.php';

class Zolago_Adminhtml_Sales_TransactionsController
    extends Mage_Adminhtml_Sales_TransactionsController
{

    /**
     * Initialize payment transaction model
     *
     * @return Mage_Sales_Model_Order_Payment_Transaction | bool
     */
    protected function _initTransactionModel()
    {
        $txn = Mage::getModel('sales/order_payment_transaction')->load(
            $this->getRequest()->getParam('txn_id')
        );

        if (!$txn->getId()) {
            //New transaction
        }
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $txn->setOrderUrl(
                $this->getUrl('*/sales_order/view', array('order_id' => $orderId))
            );
        }

        Mage::register('current_transaction', $txn);
        return $txn;
    }

    /**
     * View Transaction Details action
     */
    public function viewAction()
    {

        $txn = $this->_initTransactionModel();
        if (!$txn) {
            return;
        }
        $this->_title($this->__('Sales'))
            ->_title($this->__('Transactions'))
            ->_title(sprintf("#%s", $txn->getTxnId()));

        $this->loadLayout()
            ->_setActiveMenu('sales/transactions')
            ->renderLayout();
    }

    public function editAction()
    {
        $txn_id = $this->getRequest()->getParam('txn_id', 0);

        $txn = Mage::getModel('sales/order_payment_transaction')->load(
            $this->getRequest()->getParam('txn_id')
        );
        if (($txn_id > 0) && !empty($txn->getDotpayId())) {
            if (!empty($txn->getDotpayId()))
                return $this->_redirect("*/*/view", array("txn_id" => $txn_id));
        }


        $txn = $this->_initTransactionModel();
        if (!$txn->getId()) {
            // Default values for form
            $txn->setDefaults();
        }
        $this->_title($this->__('Sales'))
            ->_title($this->__('Transactions'))
            ->_title(sprintf("#%s", $txn->getTxnId()));

        $this->loadLayout()
            ->_setActiveMenu('sales/transactions')
            ->renderLayout();
    }

    public function saveAction()
    {
        $poId = $this->getRequest()->getParam('order_id');

        /* @var $_po Zolago_Po_Model_Po*/
        $_po = Mage::getModel("udpo/po")->load($poId);
        $order = $_po->getOrder();
        $orderId = $order->getId();

        $txnAmount = $this->getRequest()->getParam("txn_amount");
        $id = $this->getRequest()->getParam("txn_id", 0);
        $txnKey = $this->getRequest()->getParam('txn_key');
        $date = $this->getRequest()->getParam("date");


        if ($this->getRequest()->isPost()) {
            try {
                $transaction = Mage::getModel("sales/order_payment_transaction")->load($id);
                $isNewTransaction = $transaction->isObjectNew();
                $transaction->setOrderPaymentObject($order->getPayment());

                $status = Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED;
                $transaction
                    ->setTxnStatus($status)
                    ->setBankTransferCreateAt($date)
                    ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER)
                    ->setTxnId($txnKey)
                    ->setCustomerId($order->getCustomerId())
                    ->setTxnAmount($txnAmount)
                    ->setOrderId($orderId)
                    ->setIsClosed(1);

                $transactionNew = $transaction->save();
                $transaction->setTxnId($transactionNew->getId())->save();

                if ($isNewTransaction) {
                    $this->_getSession()->addSuccess($this->__('Bank payment has been successfully created.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('Bank payment has been successfully changed.'));
                }

                /* @var $statusModel Zolago_Po_Model_Po_Status */
                $statusModel = $_po->getStatusModel();
                if ($_po->getDebtAmount() >= 0) {
                    $statusModel->processDirectRealisation($_po, true);
                } else {
                    $statusModel->changeStatus($_po, Zolago_Po_Model_Po_Status::STATUS_PAYMENT);
                }

            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect("*/*");

    }


    /**
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function rejectAction()
    {
        $id = $this->getRequest()->getParam('txn_id');


        try {
            /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
            $transaction = Mage::getModel("sales/order_payment_transaction")->load($id);
            $order = Mage::getModel("sales/order")->load($transaction->getOrderId());

            $paymentModel = Mage::getModel("sales/order_payment")->load($transaction->getPaymentId());
            if ($id > 0 && $paymentModel->getMethod() !== "banktransfer") {
                if ($paymentModel->getMethod() !== "banktransfer")
                    return $this->_redirect("*/*/view", array("txn_id" => $id));
            }

            if ($order->getId()) {
                $status = Zolago_Payment_Model_Client::TRANSACTION_STATUS_REJECTED;
                $transaction->setOrderPaymentObject($order->getPayment());

                $transaction
                    ->setTxnStatus($status)
                    ->setIsClosed(1);

                $transaction->save();

            }
            $this->_getSession()->addSuccess($this->__('Bank payment has been rejected.'));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect("*/*");

    }

}