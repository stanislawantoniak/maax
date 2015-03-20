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

        /* @var $ghApiUser GH_Api_Model_User */
        $ghApiUser = Mage::getModel('ghapi/user');
        $vendorApiUser = $ghApiUser->loadByVendorId($vendorId);

        // If Edit
        if (is_null($vendorApiUser->getUserId()) || !($vendor->getExternalId() == $vendorApiUser->getVendorId())) {
            throw new Mage_Core_Exception($helper->__("It is not your settings"));
        }

        if (!empty($ghapiVendorPassword)) {
            //update password
            $password = $ghApiUser->updateUserPassword($ghapiVendorPassword, $vendorId);
            $this->_getSession()->addSuccess($helper->__('API Password saved'));
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

        return $this->_redirectReferer();
    }
}


