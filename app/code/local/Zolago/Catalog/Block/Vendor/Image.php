<?php
class Zolago_Catalog_Block_Vendor_Image extends Mage_Core_Block_Template {

    public function maxUploadInByte() {
        /* @var $ghCommonHelper GH_Common_Helper_Data */
        $ghCommonHelper = Mage::helper('ghcommon');
        $minByte = $ghCommonHelper->getMaxUploadFileSize();
        return $minByte;
    }
}