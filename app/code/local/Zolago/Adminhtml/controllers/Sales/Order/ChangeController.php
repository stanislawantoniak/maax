<?php

class Zolago_Adminhtml_Sales_Order_ChangeController extends Mage_Adminhtml_Controller_Action
{
    public function changeToCodAction()
    {
        $orderId = $this->getRequest()->getParam("order_id", NULL);
        if ($orderId== NULL) {
            $this->_getSession()->addError($this->__('Order not found'));
            $this->_redirectReferer();
            return;
        }
        $paymentModel = Mage::getModel("sales/order_payment");
        $paymentModel->load($orderId, "parent_id");

        if ($paymentModel->getId() == NULL) {
            $this->_getSession()->addError($this->__('Payment for order not found'));
            $this->_redirectReferer();
        }

        try {
            $paymentModel->setData('method', 'cashondelivery');
            $paymentModel->save();
            $this->_getSession()->addSuccess($this->__('Payment has been successfully changes to COD'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error occurred on change to COD action.'));
            Mage::logException($e);
        }

        $this->_redirectReferer();


    }
}