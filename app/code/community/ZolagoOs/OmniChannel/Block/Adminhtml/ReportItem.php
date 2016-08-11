<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_ReportItem extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_reportItem';
        $this->_headerText = Mage::helper('udropship')->__('Item Details Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}