<?php

class Gh_Regulation_Adminhtml_VendorController extends Mage_Adminhtml_Controller_Action
{

    public function brandAction()
    {
        $block =  $this->getLayout()->createBlock('ghregulation/adminhtml_dropship_settings_kind_grid', 'admin.regulation.settings.kind');
        $block->setVendorId($this->getRequest()->getParam('id'));
        $block->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }
}
