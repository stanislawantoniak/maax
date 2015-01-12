<?php

class Zolago_Campaign_Block_Helper_Form_Infocampaign extends Varien_Data_Form_Element_Multiselect
{
    public function getHtml()
    {
        // Set disabled
        $this->setReadonly(true, true);
        return parent::getHtml();
    }
}