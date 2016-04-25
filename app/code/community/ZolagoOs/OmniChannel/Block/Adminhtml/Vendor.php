<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_vendor';
        $this->_headerText = Mage::helper('udropship')->__('Manage Vendors');
        $this->_addButtonLabel = Mage::helper('udropship')->__('Add New Vendor');
        parent::__construct();
    }
}