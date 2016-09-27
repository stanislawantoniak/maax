<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Block_Adminhtml_Widget_Form_Views extends Varien_Data_Form_Element_Text
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getElementHtml()
    {
        $editorHtml = false;

        /** @var Magpleasure_Blog_Block_Adminhtml_Widget_Form_Views_Renderer $renderer */
        $renderer = Mage::app()->getLayout()->createBlock('mpblog/adminhtml_widget_form_views_renderer');
        if ($renderer) {
            $editorHtml =
                $this->getAfterElementHtml() .
                $renderer->toHtml() .
                $this->getAfterElementHtml();
        }

        return $editorHtml;
    }

}