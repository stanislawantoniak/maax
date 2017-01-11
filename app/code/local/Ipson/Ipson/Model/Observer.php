<?php
/**
 * ipson observers
 */
class Ipson_Ipson_Model_Observer {
     protected $_domainList = array (
               'ceneo' => 'ceneo.pl',
               'opineo' => 'opineo.pl',
          );
     
    /**
     * save agreement to checkout session
     */
     protected function _setAgreement($name) {
         $agreements = Mage::app()->getRequest()->getParam($name,0);
         $session = Mage::getSingleton('checkout/session');
         $session->setData($name,$agreements);     
     }
    
    /**
     * save ceneo agreement to checkout session
     */
     public function setAgreements($observer) {
         foreach ($this->_domainList as $name=>$domain) {
              $this->_setAgreement(sprintf('%s_agreement',$name));
         }

     }
}
