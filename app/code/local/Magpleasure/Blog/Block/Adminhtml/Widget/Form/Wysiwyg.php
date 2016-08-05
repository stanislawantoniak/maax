<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Widget_Form_Wysiwyg extends Varien_Data_Form_Element_Editor
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getShowCutter()
    {
        return true;
    }

    public function getElementHtml()
    {
        $editorHtml = parent::getElementHtml();

        /** @var Magpleasure_Blog_Block_Adminhtml_Widget_Form_Wysiwyg_Renderer $renderer */
        $renderer = Mage::app()->getLayout()->createBlock('mpblog/adminhtml_widget_form_wysiwyg_renderer');
        if ($renderer){
            $editorHtml = $this->getAfterElementHtml().$renderer->addData(array(
                'field_name' => $this->getName(),
                'html_id' => $this->getId(),
                'style' => $this->getStyle(),
                'class' => $this->getClass(),
                'value' => $this->getEscapedValue(),
                'min_height' => $this->getMinHeight(),
                'show_cutter' => $this->getShowCutter(),
            ))->toHtml().$this->getAfterElementHtml();
        }
        return $editorHtml;
    }
}