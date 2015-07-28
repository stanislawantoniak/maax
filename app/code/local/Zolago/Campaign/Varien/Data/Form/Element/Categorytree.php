<?php

class Zolago_Campaign_Varien_Data_Form_Element_Categorytree
    extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {
        return Mage::app()->getLayout()
            ->createBlock('core/template')
            ->setTemplate('zolagocampaign/dropship/campaign/edit/category_tree.phtml')
            ->setData("field_name", $this->getName())
            ->setData("field_value", $this->getValue())
            ->toHtml();
    }
}