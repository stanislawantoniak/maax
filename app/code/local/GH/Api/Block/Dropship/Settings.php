<?php
class GH_Api_Block_Dropship_Settings extends Mage_Core_Block_Template {

    protected function getGhApiVendorUser()
    {
        /* @var $ghUserModel GH_Api_Model_Vendor_User */
        $ghUserModel = Mage::getModel('ghapi/vendor_user');
        $vendorApiUser = $ghUserModel->generateGhApiVendorUser();

        return $vendorApiUser;
    }

}