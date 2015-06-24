<?php

class GH_Dhl_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getGalleryDHLAccountData($account)
    {
        $dhlAccounts = Mage::getModel("ghdhl/dhl")->getCollection();
        $dhlAccounts->addFieldToFilter("dhl_account", $account);
        $galleryDHLAccount = $dhlAccounts->getFirstItem();
        return $galleryDHLAccount;
    }
}