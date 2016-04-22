<?php

class ZolagoOs_OmniChannelVendorProduct_Block_Adminhtml_SystemConfigField_FieldsetsColumnConfig extends ZolagoOs_OmniChannel_Block_Adminhtml_SystemConfigFormField_FieldContainer
{
    public function getEditFieldsConfig()
    {
        return Mage::helper('udprod')->getEditFieldsConfig();
    }
}