<?php

class Zolago_DropshipMicrositePro_Block_Vendor_Logo extends Zolago_Catalog_Block_Product_Vendor_Info  {

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {

        return $_vendor = Mage::helper('umicrosite')->getCurrentVendor();

    }

    public function getLogo($width = 130, $height = 74)
    {
        $vendor = $this->getVendor();
        /* @var $zolagodropship Zolago_Dropship_Helper_Data */
        $zolagodropship = Mage::helper("zolagodropship");

        return $zolagodropship->getVendorLogoResizedUrl($vendor, $width, $height);
    }
} 