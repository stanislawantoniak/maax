<?php

class Unirgy_DropshipVendorAskQuestion_Block_Vendor_Question_Renderer_Fieldset extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('udqa/vendor/question/renderer/fieldset.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
    public function getChildElementHtml($elem)
    {
        return $this->getElement()->getForm()->getElement($elem)->toHtml();
    }
    public function getChildElement($elem)
    {
        return $this->getElement()->getForm()->getElement($elem);
    }
    public function isHidden($elem)
    {
        return $this->getElement()->getForm()->getElement($elem)->getIsHidden();
    }
}