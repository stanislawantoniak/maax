<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'udpo';

    public function __construct()
    {
        $this->_controller = 'adminhtml_po';
        $this->_headerText = Mage::helper('udpo')->__('Purchase Orders');
        parent::__construct();
        $this->_removeButton('add');
    }
}