<?php

class GH_Dhl_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getGalleryDHLAccountData($account)
    {
        $password = "";
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        if($vendor){
            $vid = $vendor->getId();

            $dhlAccounts = Mage::getModel("ghdhl/dhl")->getCollection();
            $dhlAccounts->addFieldToFilter("dhl_account", $account);
            $galleryDHLAccount = $dhlAccounts->getFirstItem();
            $galleryDHLAccountId = $galleryDHLAccount->getId();
            if(!empty($galleryDHLAccountId)){
                //Check if vendor has access to this account
                $dhlVendorAccounts = Mage::getModel("ghdhl/dhl_vendor")->getCollection();
                $dhlVendorAccounts->addFieldToFilter("vendor_id", $vid);
                $dhlVendorAccounts->addFieldToFilter("dhl_id", $galleryDHLAccountId);
                $access = $dhlVendorAccounts->getFirstItem()->getId();

                if(!empty($access)){
                    $password = $galleryDHLAccount->getDhlPassword();
                } else {
                    throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("You don't have access to this DHL Account"));
                }
            }
        }

        return $password;
    }
}