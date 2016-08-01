<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Form_Element_Wysiwyg extends Varien_Data_Form_Element_Textarea
{
    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _getCommonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Retrives element html
     * @return string
     */
    public function getElementHtml()
    {
        /** @var Magpleasure_Common_Block_Adminhtml_Widget_Form_Wysiwyg_Renderer $renderer */
        $renderer = Mage::app()->getLayout()->createBlock('magpleasure/adminhtml_widget_form_wysiwyg_renderer');
        if ($renderer){
            return $renderer->setData($this->getData())->toHtml();
        } else {
            return parent::getElementHtml();
        }
    }
}