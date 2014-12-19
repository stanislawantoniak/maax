<?php

class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Header extends Mage_Core_Block_Template
{

    public function getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }

    public function getDesktopVendorHeaderPanel()
    {
        $vendor = $this->getVendor();
        return $this
            ->getLayout()
            ->createBlock('cms/block')
            ->setBlockId('top-bottom-header-desktop-v-' . $vendor['vendor_id'])
            ->toHtml();
    }

    public function getMobileVendorHeaderPanel()
    {
        $vendor = $this->getVendor();
        return $this
            ->getLayout()
            ->createBlock('cms/block')
            ->setBlockId('top-bottom-header-mobile-v-' . $vendor['vendor_id'])
            ->toHtml();
    }
} 