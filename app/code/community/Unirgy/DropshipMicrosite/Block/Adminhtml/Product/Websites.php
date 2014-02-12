<?php

class Unirgy_DropshipMicrosite_Block_Adminhtml_Product_Websites
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Websites
{
    public function getWebsiteIds()
    {
        $staging = Mage::getStoreConfig('udropship/microsite/staging_website');
        if (!($v = Mage::helper('umicrosite')->getCurrentVendor()) || !$staging) {
            if ($v && ($lw = $v->getLimitWebsites())) {
                return is_array($lw) ? $lw : explode(',', $lw);
            } else {
                return $this->getData('website_ids');
            }
        }
        return (array)$staging;
    }
}