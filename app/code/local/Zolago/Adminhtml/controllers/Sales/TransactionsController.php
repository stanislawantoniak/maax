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

    public function saveAction()
    {
        $request = $this->getRequest();

        $orderId = $this->getRequest()->getParam('order_id');

        $order = Mage::getModel("sales/order")->load($orderId);
        krumo($order->getData());
        die("test");


        $payment = Mage::getModel("sales/order_payment")->load($orderId,"parent_id");
        //krumo($payment->getData());

        $model = Mage::getModel("sales/order_payment_transaction");

        $helper = Mage::helper('zolagosales');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("id");

        $this->_getSession()->setFormData(null);

        try {

            if ($this->getRequest()->isPost()) {

                $model->load($modelId);
                $model->addData($data);

                $validErrors = $model->validate();


                if ($validErrors === true) {
                    $model->save();
                } else {
                    $this->_getSession()->setFormData($data);
                    foreach ($validErrors as $error) {
                        $this->_getSession()->addError($error);
                    }
                    return $this->_redirectReferer();
                }
                $this->_getSession()->addSuccess($helper->__("Bank Transfer Saved"));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError($helper->__("Some error occurred!"));
            $this->_getSession()->setFormData($data);
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        return $this->_redirect("*/*");
        die("test");
    }

}