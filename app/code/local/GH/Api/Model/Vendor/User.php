<?php

/**
 * Class GH_Api_Model_Vendor_User
 */
class GH_Api_Model_Vendor_User extends Zolago_Dropship_Model_Vendor
{
    protected function getVendor(){
        return Mage::getSingleton('udropship/session')->getVendor();
    }


    public function generateGhApiVendorUser()
    {
        $vendor = $this->getVendor();
        $vendorId = (int)$vendor->getVendorId();

        /* @var $ghApiUser GH_Api_Model_User */
        $ghApiUser = Mage::getModel('ghapi/user');
        $vendorApiUser = $ghApiUser->loadByVendorId($vendorId);

        if (is_null($vendorApiUser->getUserId())) {
            //create user
            $newUserPassword = MD5(strrev(microtime()));
            $vendorApiUser = $ghApiUser->createUser($vendorId,$newUserPassword);
        }

        return $vendorApiUser;
    }
}