<?php

class ZolagoOS_IAIShop_SettingsController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagoosiaishop');
    }


    public function saveAction()
    {
        $this->getRequest()->getParam("login");
        $this->getRequest()->getParam("password");

        

        $this->_getSession()->addSuccess(Mage::helper("zosiaishop")->__("Settings saved!"));
        $this->_redirect('iaishop/settings');
    }
}
