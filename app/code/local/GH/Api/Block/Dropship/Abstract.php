<?php 
/**
 * abstract class for gh api dropship panel
 */
class GH_Api_Block_Dropship_Abstract extends Mage_Core_Block_Template {
    public function getVendor() {
        $_session = Mage::getSingleton('udropship/session');
        $_vendor = $_session->getVendor();
        return $_vendor;
    }
    protected function getGhApiVendorUser()
    {
        /* @var $ghUserModel GH_Api_Model_Vendor_User */
        $ghUserModel = Mage::getModel('ghapi/vendor_user');
        $vendorApiUser = $ghUserModel->generateGhApiVendorUser();

        return $vendorApiUser;
    }


}