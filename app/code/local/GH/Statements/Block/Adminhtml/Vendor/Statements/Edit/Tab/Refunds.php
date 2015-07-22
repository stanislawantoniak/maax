<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Refunds
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function canShowTab()
    {
        return 1;
    }

    public function getTabLabel()
    {
        return Mage::helper('ghstatements')->__("Refunds");
    }

    public function getTabTitle()
    {
        return Mage::helper('ghstatements')->__("Refunds");
    }

    public function isHidden()
    {
        return false;
    }

}
