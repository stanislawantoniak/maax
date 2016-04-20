<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Batch extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_batch';
        $this->_headerText = Mage::helper('udropship')->__('Label Batches');

        $this->_removeButton('add');
    }

}
