<?php
class Zolago_Catalog_Block_Vendor_Image_Queue extends Zolago_Catalog_Block_Vendor_Image_Abstract {

    protected function _prepareLayout() {   
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $tmp = explode('_',$locale);
        $this->localeCode = $tmp[0];
        $this->_addItem('plugins/plupload/i18n/%s.js');
        $this->_addItem('plugins/elfinder/js/i18n/elfinder.%s.js');
        parent::_prepareLayout();
    }    
    
}
