<?php


class ZolagoOs_OmniChannelTierShipping_Model_SystemConfig_Backend_Empty extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $this->setValue('');
        return parent::_beforeSave();
    }
}
