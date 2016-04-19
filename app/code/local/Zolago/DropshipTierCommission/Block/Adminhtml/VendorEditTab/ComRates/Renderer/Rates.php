<?php

class Zolago_DropshipTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Renderer_Rates extends ZolagoOs_OmniChannelTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Renderer_Rates
{
    public function __construct()
    {
        $this->setTemplate('zolagoudtiercom/vendor/helper/category_rates_config.phtml');
    }
}