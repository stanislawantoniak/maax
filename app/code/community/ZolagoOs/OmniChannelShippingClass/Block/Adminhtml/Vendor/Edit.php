<?php

class ZolagoOs_OmniChannelShippingClass_Block_Adminhtml_Vendor_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_blockGroup  = 'udshipclass';
        $this->_controller  = 'adminhtml_vendor';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('tax')->__('Save Vendor Ship Class'));
        $this->_updateButton('delete', 'label', Mage::helper('tax')->__('Delete Vendor Ship Class'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('udshipclass_vendor')->getId()) {
            return Mage::helper('udshipclass')->__("Edit Vendor Ship Class '%s'", $this->htmlEscape(Mage::registry('udshipclass_vendor')->getClassName()));
        }
        else {
            return Mage::helper('udshipclass')->__('New Vendor Ship Class');
        }
    }

}
