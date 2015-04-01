<?php

class Zolago_Adminhtml_Block_Cms_Edit_Form_Renderer_Fieldset_Helplabel
    extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {
        return $this->getData('content');
    }

}