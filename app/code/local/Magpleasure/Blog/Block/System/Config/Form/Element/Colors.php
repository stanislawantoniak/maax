<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_System_Config_Form_Element_Colors
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Change field renderer
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $render = $this->getLayout()->createBlock('mpblog/system_config_form_element_colors_render');
        if ($render){
            return $render->toHtml();
        }
        return false;
    }
}

