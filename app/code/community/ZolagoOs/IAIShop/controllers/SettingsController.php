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
        $vendor = $this->_getSession()->getVendor();
        if (!$vendor->getData('ghapi_vendor_access_allow')) {
            return $this->_redirect('udropship/vendor');
        }

        $helper = Mage::helper('zosiaishop');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }


        $vendor->setIaishopLogin($this->getRequest()->getPost('login'));
        $vendor->setIaishopPass($this->getRequest()->getPost('password'));

        try {
            $vendor->save();
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setFormData($this->getRequest()->getParams());
            return $this->_redirectReferer();
        }

        $this->_getSession()->addSuccess($helper->__('Settings saved!'));

        return $this->_redirectReferer();
    }
}
