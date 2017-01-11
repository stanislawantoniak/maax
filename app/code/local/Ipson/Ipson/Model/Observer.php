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
     
    /**
     * sprawdzamy czy było wejście z ceneo lub opineo
     */
     public function checkAgreementType() {
          $cookie = Mage::getSingleton('core/cookie');
          $referer = Mage::helper('core/http')->getHttpReferer();
          $newKey = '';
          foreach ($this->_domainList as $name => $domain) {
              if (strstr($referer,$domain)) {
                   $newKey = $name;
                   break;
              }
          }           
          $key = $cookie->get('opinion_domain');
          if (!$newKey && !$key) {
              $newKey = 'opineo'; // default
          }
          if ($newKey && ($key != $newKey)) {                          
                $cookie->set('opinion_domain',$newKey,3600*24*30);
          };
     }

}
