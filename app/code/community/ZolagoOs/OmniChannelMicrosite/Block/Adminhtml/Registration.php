<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_Block_Adminhtml_Registration extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'umicrosite';
        $this->_controller = 'adminhtml_registration';
        $this->_headerText = Mage::helper('udropship')->__('Manage Registrations');
        parent::__construct();
        $this->_removeButton('add');
    }
}