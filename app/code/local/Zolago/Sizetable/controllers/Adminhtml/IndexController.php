<?php

class Zolago_Sizetable_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

    public function settingsAction()
    {
        $block =  $this->getLayout()->createBlock('zolagosizetable/adminhtml_dropship_edit_tab_settings', 'admin.sizetable.settings');
        $block->setVendorId($this->getRequest()->getParam('id'));
        $block->setUseAjax(true)
                ->toHtml();
        $this->getResponse()->setBody($block);
    }

}
