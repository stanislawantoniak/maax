<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Config_Form_Element_Ajax_Tags extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Change field renderer
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $newElement = new Magpleasure_Common_Block_System_Entity_Form_Element_Ajax_Tags($element->getData());
        return $newElement->getElementHtml();
    }
}