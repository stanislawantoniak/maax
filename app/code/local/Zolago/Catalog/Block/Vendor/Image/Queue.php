<?php
class Zolago_Catalog_Block_Vendor_Image_Queue extends Zolago_Catalog_Block_Vendor_Image_Abstract {

    protected function _prepareLayout() {   
        $this->_addItem('plugins/plupload/i18n/%s.js');
        parent::_prepareLayout();
    }    
    
}