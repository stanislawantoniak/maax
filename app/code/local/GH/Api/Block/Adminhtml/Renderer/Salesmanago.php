<?php

class GH_Api_Block_Adminhtml_Renderer_Salesmanago extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {
        $ajaxUrl = Mage::helper("adminhtml")->getUrl("ghapi/adminhtml_salesmanago/save");

        return Mage::app()
            ->getLayout()
            ->createBlock('core/template')
            ->setData("save_url", $ajaxUrl)
            ->setTemplate('salesmanago/login.phtml')
            ->toHtml();
    }
}