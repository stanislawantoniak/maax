<?php
class Zolago_Catalog_Block_Vendor_Image_Abstract extends Mage_Core_Block_Template {
    
    protected $catalogId = '0';

    
    protected function _addItem($jsPath) {   
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $path = sprintf($jsPath,$locale);
        if ($path) {
            $this->getLayout()->getBlock('head')->addItem('skin_js',$path);
        }
    }    
    protected function _prepareLayout() {
        $this->_assignCatalog();
        parent::_prepareLayout();
    }
    protected function _assignCatalog() {
        $session = Mage::getSingleton('udropship/session');
        $vendor = $session->getVendor();
        $this->catalogId = ($vendor->getId())? $vendor->getId():0;
    }
}