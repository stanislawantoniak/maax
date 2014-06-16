<?php
class Zolago_Holidays_Block_Adminhtml_Holidays_Grid_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	
	/**
	 * @param Varien_Object @row
	 * 
	 * @return string
	 */
	public function render(Varien_Object $row){
		
		$helper = Mage::helper('zolagoholidays');
		
		$value =  $row->getData($this->getColumn()->getIndex());
		return ($value == '1') ? $helper->__('Fixed') : $helper->__('Movable');
	}
}