<?php

class GH_Dhl_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getGalleryDHLAccountData($account, $vendorId)
    {
        //Mage::log($account, null, "dhl.log");
        $account = trim($account);
        if (empty($account)) {
            return;
        }

        if (empty($vendorId)) {
            return;
        }

        $dhlAccounts = Mage::getModel("ghdhl/dhl")->getCollection();
        $dhlAccounts->addFieldToFilter("dhl_account", $account);
        $galleryDHLAccount = $dhlAccounts->getFirstItem();
        $galleryDHLAccountId = $galleryDHLAccount->getId();
        //Mage::log("DHL Account Id: ".$galleryDHLAccountId, null, "dhl.log");
        if (empty($galleryDHLAccountId)) {
            return;
        }
        //Check if vendor has access to this account
        $dhlVendorAccounts = Mage::getModel("ghdhl/dhl_vendor")->getCollection();
        $dhlVendorAccounts->addFieldToFilter("vendor_id", $vendorId);
        $dhlVendorAccounts->addFieldToFilter("dhl_id", $galleryDHLAccountId);
        $dhlVendorAccount = $dhlVendorAccounts->getFirstItem();
        //Mage::log($dhlVendorAccount->getData(), null, "dhl.log");
        $access = $dhlVendorAccount->getId();

        if (!empty($access)) {
            return $galleryDHLAccount;
        } else {
            throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("You don't have access to %s DHL Account", $galleryDHLAccount->getDhlAccount()));
            return;
        }

    }
}