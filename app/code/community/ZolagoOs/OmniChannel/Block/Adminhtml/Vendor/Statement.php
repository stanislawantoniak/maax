<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Statement extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_vendor_statement';
        $this->_headerText = Mage::helper('udropship')->__('Vendor Statements');

        $this->_updateButton('add', 'label', Mage::helper('udropship')->__('Generate Statements'));
    }

}
