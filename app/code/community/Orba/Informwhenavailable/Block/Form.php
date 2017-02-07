<?php
class Orba_Informwhenavailable_Block_Form extends Mage_Catalog_Block_Product_Abstract {
    
    protected function getConfig() {
        return Mage::getModel('informwhenavailable/config');
    }
    
    public function isActive() {
        return $this->getConfig()->isActive();
    }
    
    public function isAvailable($product) {
        return Mage::getModel('informwhenavailable/entry')->isAvailable($product);
    }
    
    public function isLoggedIn() {
        return Mage::helper('customer')->isLoggedIn();
    }
    
    public function isRequestAlreadySent() {
        $product = $this->getProduct();
        return Mage::getModel('informwhenavailable/entry')->isRequestAlreadySent($product);
    }
    
}