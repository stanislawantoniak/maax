<?php

class ZolagoOS_IAIShop_SettingsController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
        $vendor = Mage::getModel("udropship/vendor")->load(83);
        Zend_Debug::dump(json_decode($vendor->getData("custom_vars_combined"))->iaishop_login);
        Zend_Debug::dump(json_decode($vendor->getData("custom_vars_combined"))->iaishop_pass);
        die("test");
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagoosiaishop');
    }


    public function saveAction()
    {


        // ACL no access
        $vendor = $this->_getSession()->getVendor();
        if (!$vendor->getData('ghapi_vendor_access_allow')) {
            return $this->_redirect('udropship/vendor/dashboard');
        }

        $helper = Mage::helper('zosiaishop');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }


        $vendor->setData('iaishop_login', $this->getRequest()->getPost('login'));
        $vendor->setData('iaishop_pass', $this->getRequest()->getPost('password'));

        $vendor->save();

        $this->_getSession()->addSuccess($helper->__('Settings saved!'));

        return $this->_redirectReferer();
    }
}
