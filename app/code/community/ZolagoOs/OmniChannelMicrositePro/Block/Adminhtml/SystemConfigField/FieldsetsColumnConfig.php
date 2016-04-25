<?php

class ZolagoOs_OmniChannelMicrositePro_Block_Adminhtml_SystemConfigField_FieldsetsColumnConfig extends ZolagoOs_OmniChannel_Block_Adminhtml_SystemConfigFormField_FieldContainer
{
    public function getEditFieldsConfig()
    {
        return Mage::helper('udmspro')->getRegistrationFieldsConfig();
    }
}
