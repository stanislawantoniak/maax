<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Dotpay_Dotpay') . DS . "NotificationController.php";

class Zolago_Dotpay_ProcessingController extends Dotpay_Dotpay_NotificationController {
    public function indexAction() {

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($this->getRequest()->getPost('control'));
        if (!$order->getId())
            die('ERR');

        if (!$this->isDataIntegrity($order->getPayment()->getMethodInstance()->getConfigData('pin')))
            die('ERR');

        list($amount, $currency) = explode(' ', $this->getRequest()->getPost('orginal_amount'));
        if (!($order->getOrderCurrencyCode() == $currency && round($order->getGrandTotal(), 2) == $amount))
            die('ERR');

        if ($this->getRequest()->getPost('t_status') == 2)
            $order->addStatusToHistory(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage::helper('dotpay')->__('The payment has been accepted.'));
/* never cancel                
        elseif ($this->getRequest()->getPost('t_status') == 3) {        
            $order->cancel(); 
            $order->addStatusToHistory(
                Mage_Sales_Model_Order::STATE_CANCELED,
                Mage::helper('dotpay')->__('The order has been canceled.'));
*/                
        }

        $order->save();

        die('OK');
    }

}