<?php
class Zolago_Catalog_Block_Vendor_Image_Files extends Zolago_Catalog_Block_Vendor_Image_Abstract {
    
    protected $localeCode;
    
    protected function _prepareLayout() {   
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $tmp = explode('_',$locale);
        $this->localeCode = $tmp[0];
        $path = 'plugins/elfinder/js/i18n/elfinder.%s.js';
        $this->_addItem($path);        
        parent::_prepareLayout();
    }    

    
}