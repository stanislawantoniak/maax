<?php

class GH_Dhl_Block_Adminhtml_Dhl_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ghdhl_info_tab');
        $this->setDestElementId('template_edit_form');
        $this->setTitle(Mage::helper('ghdhl')->__('DHL Account Information'));
    }
}
