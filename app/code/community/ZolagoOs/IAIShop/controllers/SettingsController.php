<?php

class ZolagoOS_IAIShop_SettingsController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
		if (!$this->_getSession()->getVendor()->getData('ghapi_vendor_access_allow')) {
			return $this->_redirect('udropship/vendor');
		}

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


        $data = $this->getRequest()->getParams();
        unset($data["form_key"]);

		if ($vendor->getData("iaishop_pass") != '' && $data["pass"] == '') unset($data["pass"]);


        foreach ($data as $k => $v) $vendor->setData("iaishop_" . $k, $v);

        
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
