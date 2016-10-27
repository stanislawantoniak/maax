<?php

/**
 * override wysiwyg form
 */
class Zolago_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg extends Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg {
    
    
    /**
     * Check whether wysiwyg enabled or not
     *
     * @return boolean
     */
    public function getIsWysiwygEnabled()
    {
        
        if (Mage::helper('catalog')->isModuleEnabled('Mage_Cms')) {
            return (bool)(Mage::getSingleton('zolagocatalog/wysiwyg_config')->isEnabled()
                && $this->getEntityAttribute()->getIsWysiwygEnabled());
        }

        return false;
    }

}