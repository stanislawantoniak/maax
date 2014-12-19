<?php

class Zolago_DropshipMicrositePro_Block_Vendor_Logo extends Zolago_Catalog_Block_Product_Vendor_Info  {

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {

        return $_vendor = Mage::helper('umicrosite')->getCurrentVendor();

    }

    public function getLogo()
    {
        $vendorLogo = $this->getVendor()->getLogo();
        if (empty($vendorLogo)) {
            return false;
        }

        $logo = Mage::getBaseDir('media') . DS . "vendor_logo" . DS . "resized" . DS . $vendorLogo;
        $logoUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . "vendor_logo" . DS . "resized" . DS . $vendorLogo;

        if (file_exists($logo)) {
            return $logoUrl;
        } else {
            $image = new Varien_Image(Mage::getBaseDir('media') . DS . $vendorLogo);
            $image->constrainOnly(false);
            $image->keepFrame(true);
            $image->backgroundColor(array(255, 255, 255));
            $image->keepAspectRatio(true);
            $image->resize(130, 70);
            $image->save($logo);
            return $logoUrl;
        }

    }
} 