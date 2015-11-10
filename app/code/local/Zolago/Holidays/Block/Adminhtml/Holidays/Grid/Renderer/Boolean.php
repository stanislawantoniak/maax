<?php
class Zolago_Holidays_Block_Adminhtml_Holidays_Grid_Renderer_Boolean extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	
	/**
	 * @param Varien_Object
	 * 
	 * @return string
	 */
	public function render(Varien_Object $row){
	    $helper = Mage::helper('zolagocommon');
		$value =  $row->getData($this->getColumn()->getIndex());
		return ($value == '1') ? $helper->__('Yes') : $helper->__('No');
	}
}