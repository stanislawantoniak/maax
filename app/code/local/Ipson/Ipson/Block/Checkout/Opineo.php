<?php
/**
 * ceneo script
 */
class Ipson_Ipson_Block_Checkout_Opineo extends Ipson_Ipson_Block_Checkout_Abstract {

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('ipson/checkout/opineo.phtml');
    }


    /**
     * check agreement
     */

    public function getOpineoAgreement() {
        if ($this->_getOrder() && $this->getGuid()) { // no order no script
            return Mage::getSingleton('checkout/session')->getOpineoAgreement();
        }         
        return false;
    }
    
    /**
     * get guid 
     * @todo make configurable
     */

    public function getGuid() {
        return Mage::helper('ipson')->getOpineoAgreementGuid();
    }
    

    /**
     * określa liczbę dni po ilu należy wysłać zaproszenie. 
     * Dopuszczalny przedział to od 1 do 45 dni. W przypadku braku parametru przyjmuje się 5 dni. 
     */

     public function getOpineoQueue() {
         return '5';
     }
}