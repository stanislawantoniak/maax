<?php

class Zolago_DropshipTierCommission_Block_Adminhtml_SystemConfigField_Rates extends ZolagoOs_OmniChannelTierCommission_Block_Adminhtml_SystemConfigField_Rates
{
    public function __construct()
    {
        if (!$this->getTemplate()) $this->setTemplate('zolagoudtiercom/system/form_field/category_rates_config.phtml');
        parent::__construct();
    }
}