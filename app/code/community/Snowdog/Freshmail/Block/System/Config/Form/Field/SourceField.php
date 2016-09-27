<?php

class Snowdog_Freshmail_Block_System_Config_Form_Field_SourceField extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        /** @var Mage_Customer_Model_Entity_Form_Attribute_Collection $collection */
        $collection = Mage::getModel('customer/entity_form_attribute_collection')
            ->addFormCodeFilter('customer_account_edit');
        foreach ($collection as $attribute) {
            if ($attribute->isStatic() || !$attribute->getIsVisible()) {
                continue;
            }
            $this->addOption($attribute->getAttributeCode(), $attribute->getFrontendLabel());
        }
        $this->setExtraParams('style="width: 150px"');
        $html = parent::_toHtml();

        return $this->jsQuoteEscape($html);
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
