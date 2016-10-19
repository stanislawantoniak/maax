<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_report';
        $this->_headerText = Mage::helper('udropship')->__((Mage::helper('udropship')->isUdpoActive() ? 'Shipment' : 'General').' Details Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}