<?php
class Zolago_Catalog_Block_Vendor_Image extends Zolago_Catalog_Block_Vendor_Image_Abstract {

    protected $localeJsPath = 'plugins/plupload/i18n/%s.js';
    
    protected function _perpareLayout() {
        $this->_addItem($this->localeJsPath);
        parent::_prepareLayout();
    }
    
}