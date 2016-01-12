<?php

class GH_Api_Block_Adminhtml_Renderer_Salesmanago extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {
        return Mage::app()
            ->getLayout()
            ->createBlock('core/template')
            ->setTemplate('salesmanago/login.phtml')
            ->toHtml();
    }
}