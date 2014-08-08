<?php

class Zolago_Banner_Block_Adminhtml_SystemConfigField_TypesColumnConfig extends Zolago_Banner_Block_Adminhtml_SystemConfigFormField_FieldContainer
{
    public function getEditFieldsConfig()
    {
        return Mage::helper('zolagobanner')->getEditFieldsConfig();
    }
}