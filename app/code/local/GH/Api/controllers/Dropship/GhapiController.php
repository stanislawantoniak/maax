<?php

class GH_Api_Dropship_GhapiController extends Zolago_Dropship_Controller_Vendor_Abstract {

	public function indexAction() {
		$this->_renderPage(null, 'ghapi');
	}


    public function saveAction()
    {
        $helper = Mage::helper('ghapi');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }

        $vendor = $this->_getSession()->getVendor();
        $vendorId = (int)$vendor->getVendorId();

        $ghapiVendorPassword = $this->getRequest()->getPost('ghapi_vendor_password');

        if (!empty($ghapiVendorPassword)) {
            //update password
            /* @var $ghApiUser GH_Api_Model_User */
            $ghApiUser = Mage::getModel('ghapi/user');
            $vendorApiUser = $ghApiUser->loadByVendorId($vendorId);

            // If Edit
            if (is_null($vendorApiUser->getUserId()) || !($vendor->getExternalId() == $vendorApiUser->getVendorId())) {
                throw new Mage_Core_Exception($helper->__("It is not your settings"));
            }
            $password = $ghApiUser->updateUserPassword($ghapiVendorPassword, $vendorId);

            $this->_getSession()->addSuccess($helper->__('API Password saved'));
        }
        return $this->_redirectReferer();
    }
}


