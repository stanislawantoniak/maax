<?php

class Zolago_Sizetable_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

    public function brandAction()
    {
        $block =  $this->getLayout()->createBlock('zolagosizetable/adminhtml_dropship_settings_brand_grid', 'admin.sizetable.settings.brand');
        $block->setVendorId($this->getRequest()->getParam('id'));
        $block->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }
    public function attributesetAction()
    {
        $block =  $this->getLayout()->createBlock('zolagosizetable/adminhtml_dropship_settings_attributeset_grid', 'admin.sizetable.settings.attributeset');
        $block->setVendorId($this->getRequest()->getParam('id'));
        $block->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }

}
