<?php
class Zolago_Catalog_Block_Vendor_Image_Queue extends Mage_Core_Block_Template {

    protected function _prepareLayout() {   
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $this->getLayout()->getBlock('head')->addItem('skin_js','plugins/plupload/i18n/'.$locale.'.js');
        parent::_prepareLayout();
    }    
    
}