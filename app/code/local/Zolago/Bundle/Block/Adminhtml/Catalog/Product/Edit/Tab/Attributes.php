<?php
/**
 * use different wysiwyg configuration
 */
class Zolago_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes extends 
    Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes {
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::helper('catalog')->isModuleEnabled('Mage_Cms')
            && Mage::getSingleton('zolagocatalog/wysiwyg_config')->isEnabled()
        ) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        } else {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(false);
        }
    }


    
}
