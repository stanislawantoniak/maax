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
//            $this->_getSession()->addError($this->__('Wrong transaction ID specified.'));
//            $this->_redirect('*/*/');
//            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
//            return false;
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

    public function editAction()
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
}