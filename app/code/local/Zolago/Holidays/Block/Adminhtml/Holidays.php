<?php
class Zolago_Holidays_Block_Adminhtml_Holidays extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
     	$this->_controller = 'adminhtml_holidays';
     	$this->_blockGroup = 'zolagoholidays';
     	$this->_headerText = $this->__('Holidays management');
     	$this->_addButtonLabel = Mage::helper('zolagoholidays')->__('Add a holiday');
		
     	parent::__construct();
	}
}
