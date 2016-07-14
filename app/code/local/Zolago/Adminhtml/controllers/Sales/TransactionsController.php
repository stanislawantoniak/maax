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
        $txn = Mage::getModel('sales/order_payment_transaction')->load(
            $this->getRequest()->getParam('txn_id')
        );

        $paymentModel = Mage::getModel("sales/order_payment")->load($txn->getPaymentId());
        if ($paymentModel->getMethod() == "banktransfer")
            return $this->_redirect("*/*/edit", array("txn_id" => $this->getRequest()->getParam('txn_id')));


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
        $txn = Mage::getModel('sales/order_payment_transaction')->load(
            $this->getRequest()->getParam('txn_id')
        );

        $paymentModel = Mage::getModel("sales/order_payment")->load($txn->getPaymentId());
        if ($paymentModel->getMethod() !== "banktransfer")
            return $this->_redirect("*/*/view", array("txn_id" => $this->getRequest()->getParam('txn_id')));


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
        $orderId = $this->getRequest()->getParam('order_id');
        $txn_id = $this->getRequest()->getParam('txn_id');

        $order = Mage::getModel("sales/order")->load($orderId);

        $txnAmount = $this->getRequest()->getParam("txn_amount");
        $id = $this->getRequest()->getParam("txn_id");
        $txnKey = $this->getRequest()->getParam('txn_key');


        $transaction = Mage::getModel("sales/order_payment_transaction")->load($id);
        $transaction->setOrderPaymentObject($order->getPayment());

        $status = Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED;
        $transaction
            ->setTxnStatus($status)
            ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER)
            ->setTxnId($txnKey)
            ->setCustomerId($order->getCustomerId())
            ->setTxnAmount($txnAmount)
            ->setIsClosed(1);

        $transaction->save();
        return $this->_redirect("*/*");

    }

}