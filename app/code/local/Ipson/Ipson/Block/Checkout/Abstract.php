<?php
/**
 * ceneo script
 */
class Ipson_Ipson_Block_Checkout_Abstract extends Mage_Core_Block_Template {
    protected $_order;
    
    
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }
    protected function _getOrder() {
        if (is_null($this->_order)) {
            $id = $this->_getSession()->getLastOrderId();
            $order = Mage::getModel('sales/order')->load($id);
            $this->_order = false;
            if ($order->getId()) {
                $this->_order = $order;
            }
        }
        return $this->_order;
    }
    
    /**
     * get order id
     */
     public function getOrderId() {
         return empty($this->_getOrder())? '':$this->_getOrder()->getIncrementId();
     }
               
    /**
     * order email
     */
     public function getClientEmail() {
         if (!$order = $this->_getOrder()) {
             return false;
         }
         return $order->getCustomerEmail();
     }


}