<?php
/**
 * ceneo script
 */
class Ipson_Ipson_Block_Checkout_Ceneo extends Ipson_Ipson_Block_Checkout_Abstract {
     
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('ipson/checkout/ceneo.phtml');
    }


    /**
     * check agreement
     */

    public function getCeneoAgreement() {
        if ($this->_getOrder() && $this->getGuid()) { // no order no script
            return Mage::getSingleton('checkout/session')->getCeneoAgreement();
        }         
        return false;
    }
    
    /**
     * get guid 
     * @todo make configurable
     */

    public function getGuid() {
         return Mage::helper('ipson')->getCeneoAgreementGuid();
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

}