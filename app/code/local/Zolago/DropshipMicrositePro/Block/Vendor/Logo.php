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
        $logo = Mage::getBaseDir('media') . DS . "vendor_logo/resized" . DS . $vendorLogo;
        if (empty($vendorLogo)) {
            return false;
        }
        $image = new Varien_Image(Mage::getBaseDir('media') . DS . $vendorLogo);
        $image->constrainOnly(false);
        $image->keepFrame(true);
        $image->backgroundColor(array(255, 255, 255));
        $image->keepAspectRatio(true);
        $image->resize(130, 70);
        $image->save($logo);
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "/vendor_logo/resized" . DS . $vendorLogo;

    }
} 