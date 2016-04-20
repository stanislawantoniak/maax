<?php

class ZolagoOs_OmniChannelTierShipping_Block_Adminhtml_VendorEditTab_ShippingRates_Renderer_SimpleRateSingle extends ZolagoOs_OmniChannelTierShipping_Block_Vendor_SimpleRateSingle
{
    public function __construct()
    {
        Mage_Core_Block_Template::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udtiership/vendor/helper/simple_rate_single.phtml');
        }
    }
}