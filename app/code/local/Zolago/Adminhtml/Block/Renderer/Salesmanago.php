<?php

class Zolago_Adminhtml_Block_Renderer_Salesmanago extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {
        $ajaxUrl = Mage::helper("adminhtml")->getUrl("adminhtml/salesmanago/save");

        return Mage::app()
            ->getLayout()
            ->createBlock('core/template')
            ->setData("vendor_id", Mage::app()->getRequest()->getParam("id", null))
            ->setData("save_url", $ajaxUrl)
            ->setTemplate('salesmanago/login.phtml')
            ->toHtml();
    }
}