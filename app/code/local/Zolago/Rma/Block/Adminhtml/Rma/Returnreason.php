<?php
class Zolago_Rma_Block_Adminhtml_Rma_Returnreason extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
         $this->_controller = 'adminhtml_rma_returnreason';
         $this->_blockGroup = 'zolagorma';
         $this->_headerText = $this->__('Return reasons management');
         $this->_addButtonLabel = 'Add a reason';
         parent::__construct();
    }
}