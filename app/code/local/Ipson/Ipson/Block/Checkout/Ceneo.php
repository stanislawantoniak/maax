<?php
/**
 * ceneo script
 */
class Ipson_Ipson_Block_Checkout_Ceneo extends Mage_Core_Block_Template {
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
     * check agreement
     */

    public function getCeneoAgreement() {
        if ($this->_getOrder()) { // no order no script
            return Mage::getSingleton('checkout/session')->getCeneoAgreement();
        }         
        return false;
    }
    
    /**
     * get guid 
     * @todo make configurable
     */

    public function getCeneoGuid() {
        return '550b968a-1189-420c-acb2-09f4df09f89b';
    }
    
    /**
     * get order id
     */
     public function getOrderId() {
         return empty($this->_getOrder())? '':$this->_getOrder()->getIncrementId();
     }
     
     
    /**
     * get formatted product ids
     */
     public function getCeneoProductIds() {
         if (!$order = $this->_getOrder()) {
             return '';
         }
         $out = array();
         foreach ($order->getItemsCollection() as $item) {
             $out[] = $item->getSku();
         }
         return '#'.implode('#',$out).'#';
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