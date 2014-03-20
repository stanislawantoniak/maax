<?php

class Unirgy_DropshipVendorAskQuestion_Block_Vendor_Question_Renderer_FieldsetElement extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('udqa/vendor/question/renderer/fieldset_element.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        $element->addClass('udvalidate-'.$element->getId());
        return $this->toHtml();
    }

    public function getElementHtml()
    {
        $html = $this->_element->getElementHtml();
        return $html;
    }
}