<?php
class Zolago_VendorGlobalSettings_Block_Adminhtml_VendorGlobalSettings_Container_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('zolagovendorglobalsettings_settings_tab');
        $this->setDestElementId('template_edit_form');
        $this->setTitle(Mage::helper('zolagovendorglobalsettings')->__('Vendor Global Settings'));
    }

}
