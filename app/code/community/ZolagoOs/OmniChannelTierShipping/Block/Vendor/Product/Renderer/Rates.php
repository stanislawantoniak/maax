<?php


class ZolagoOs_OmniChannelTierShipping_Block_Vendor_Product_Renderer_Rates extends ZolagoOs_OmniChannelTierShipping_Block_ProductAttribute_Renderer_Rates
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/tiership/vendor/v2/product/rates.phtml');
    }
}