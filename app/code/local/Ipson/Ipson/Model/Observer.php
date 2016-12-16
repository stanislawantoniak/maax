<?php
/**
 * ipson observers
 */
class Ipson_Ipson_Model_Observer {
    
    /**
     * save ceneo agreement to checkout session
     */
     public function setCeneoAgreements($observer) {
         $ceneoAgreements = Mage::app()->getRequest()->getParam('ceneo_agreement',0);
         $session = Mage::getSingleton('checkout/session');
         $session->setCeneoAgreement($ceneoAgreements);

     }

}
