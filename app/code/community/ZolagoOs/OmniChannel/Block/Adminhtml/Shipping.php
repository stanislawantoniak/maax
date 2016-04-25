<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_shipping';
        $this->_headerText = Mage::helper('udropship')->__('Manage Shipping Methods');
        $this->_addButtonLabel = Mage::helper('udropship')->__('Add New Shipping Method');
        parent::__construct();
    }
}