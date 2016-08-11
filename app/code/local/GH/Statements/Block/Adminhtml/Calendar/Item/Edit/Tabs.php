<?php

class GH_Statements_Block_Adminhtml_Calendar_Item_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ghstatements_calendar_item_info_tab');
        $this->setDestElementId('template_edit_form');
        $this->setTitle(Mage::helper('ghstatements')->__('Statement event'));
    }
}
