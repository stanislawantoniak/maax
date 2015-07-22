<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function canShowTab()
    {
        return 1;
    }

    public function getTabLabel()
    {
        return Mage::helper('ghstatements')->__("General Information");
    }

    public function getTabTitle()
    {
        return Mage::helper('ghstatements')->__("General Information");
    }

    public function isHidden()
    {
        return false;
    }

}
