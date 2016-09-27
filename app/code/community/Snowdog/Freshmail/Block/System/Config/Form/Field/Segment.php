<?php

class Snowdog_Freshmail_Block_System_Config_Form_Field_Segment
    extends Mage_Core_Block_Html_Select
{
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml()
    {
        $collection = Mage::getResourceModel(
            'enterprise_customersegment/segment_collection'
        );
        foreach ($collection->getData() as $item) {
            $this->addOption($item['segment_id'], $item['name']);
        }
        $this->setExtraParams('style="width: 150px"');
        $html = parent::_toHtml();

        return $this->jsQuoteEscape($html);
    }
}
