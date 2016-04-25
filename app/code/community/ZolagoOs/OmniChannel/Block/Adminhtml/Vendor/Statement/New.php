<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Statement_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'udropship';
        $this->_mode = 'new';
        $this->_controller = 'adminhtml_vendor_statement';

        $this->_updateButton('save', 'label', Mage::helper('udropship')->__('Generate Statements'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return Mage::helper('udropship')->__('Generate Vendor Statements');
    }

}
