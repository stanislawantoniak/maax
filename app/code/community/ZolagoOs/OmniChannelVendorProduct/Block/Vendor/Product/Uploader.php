<?php

class ZolagoOs_OmniChannelVendorProduct_Block_Vendor_Product_Uploader extends Mage_Adminhtml_Block_Media_Uploader
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/udprod/vendor/product/uploader.phtml');
    }
}