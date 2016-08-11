<?php

class GH_Statements_Block_Adminhtml_Vendor_Balance extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ghstatements';
        $this->_controller = 'adminhtml_vendor_balance';
        $this->_headerText = Mage::helper('ghstatements')->__('Vendors balances');

        parent::__construct();
        $this->_removeButton('add');
    }
}