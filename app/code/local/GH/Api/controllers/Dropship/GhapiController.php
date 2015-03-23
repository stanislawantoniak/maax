<?php

class GH_Api_Dropship_GhapiController extends Zolago_Dropship_Controller_Vendor_Abstract {

	public function indexAction() {

        // FIX for ACL - GH API Access
        $vendor = $this->_getSession()->getVendor();
        if ($vendor->getData('ghapi_vendor_access_allow')) {
            $this->_renderPage(null, 'ghapi');
        } else {
            return $this->_redirect('udropship/vendor/dashboard');
        }

    }


    public function saveAction()
    {
        // FIX for ACL - GH API Access
        $vendor = $this->_getSession()->getVendor();
        if (!$vendor->getData('ghapi_vendor_access_allow')) {
            return $this->_redirect('udropship/vendor/dashboard');
        }

        $helper = Mage::helper('ghapi');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }

        $vendorId = (int)$vendor->getVendorId();

        $ghapiVendorPassword = $this->getRequest()->getPost('ghapi_vendor_password');

        /* @var $ghApiUser GH_Api_Model_User */
        $ghApiUser = Mage::getModel('ghapi/user');
        $vendorApiUser = $ghApiUser->loadByVendorId($vendorId);

        // If Edit
        if (is_null($vendorApiUser->getUserId()) || !($vendor->getVendorId() == $vendorApiUser->getVendorId())) {
            throw new Mage_Core_Exception($helper->__("It is not your settings"));
        }

        if (!empty($ghapiVendorPassword)) {
            //update password
            $password = $ghApiUser->updateUserPassword($ghapiVendorPassword, $vendorId);
        }

        $postData = $this->getRequest()->getPost();

        $vendor->setData('ghapi_message_new_order', isset($postData['ghapi_message_new_order']) ? 1 : 0);
        $vendor->setData('ghapi_message_order_canceled', isset($postData['ghapi_message_order_canceled']) ? 1 : 0);

        $vendor->setData('ghapi_message_order_payment_changes', isset($postData['ghapi_message_order_payment_changes']) ? 1 : 0);
        $vendor->setData('ghapi_message_order_product_changes', isset($postData['ghapi_message_order_product_changes']) ? 1 : 0);

        $vendor->setData('ghapi_message_order_shipping_changes', isset($postData['ghapi_message_order_shipping_changes']) ? 1 : 0);
        $vendor->setData('ghapi_message_order_invoice_changes', isset($postData['ghapi_message_order_invoice_changes']) ? 1 : 0);

        $vendor->setData('ghapi_message_order_status_changes', isset($postData['ghapi_message_order_status_changes']) ? 1 : 0);

        $vendor->save();

        $this->_getSession()->addSuccess($helper->__('GH API Settings has been saved'));

        return $this->_redirectReferer();
    }
}


