<?php
/**
 * show opinion script
 */
class Ipson_Ipson_Block_Checkout_Script extends Mage_Core_Block_Template {

    protected $_agreements = array (    
         'ceneo',
         'opineo'
    );
    protected function _toHtml() {
         Mage::log('script');
         $out = '';
         foreach ($this->_agreements as $name) {              
              if (Mage::getSingleton('checkout/session')->getData(sprintf('%s_agreement',$name))) {                   
                   Mage::log('name script '.$name);
                   $block = $this->getLayout()->createBlock(sprintf('ipson/checkout_%s',$name));
                   if ($out = $this->getLayout()->createBlock(sprintf('ipson/checkout_%s',$name))->toHtml()) {
                        Mage::log('out - '.$out);
                        break;
                   }
                   
              }
         }   
         return $out;         
    }

}