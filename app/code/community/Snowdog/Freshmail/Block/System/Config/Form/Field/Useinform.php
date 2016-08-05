<?php

class Snowdog_Freshmail_Block_System_Config_Form_Field_Useinform extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        $options = Mage::getSingleton('adminhtml/system_config_source_yesno')
            ->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }
        $this->setExtraParams('style="width: 70px"');
        $html = parent::_toHtml();

        return $this->jsQuoteEscape($html);
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
