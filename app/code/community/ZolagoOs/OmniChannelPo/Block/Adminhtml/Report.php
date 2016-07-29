<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udpo';
        $this->_controller = 'adminhtml_report';
        $this->_headerText = Mage::helper('udropship')->__('PO Details Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}
