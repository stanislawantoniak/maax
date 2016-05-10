<?php

class Zolago_Adminhtml_Sales_Order_ChangeController extends Mage_Adminhtml_Controller_Action
{
    public function changeToCodAction()
    {
        $orderId = $this->getRequest()->getParam("order_id", null);
        if ($orderId) {
            $paymentModel = Mage::getModel("sales/order_payment");
            $paymentModel->load($orderId, "parent_id");

            if ($paymentModel->getId() !== NULL) {
                $paymentModel->setData('method', 'cashondelivery');
                $paymentModel->save();

                $this->_getSession()->addSuccess($this->__('Payment has been successfully changes to COD'));
                $this->_redirectReferer();
            } else {
                $this->_getSession()->addError($this->__('Payment for order not found'));
                $this->_redirectReferer();
            }

        }
        $this->_redirectReferer();

    }
}