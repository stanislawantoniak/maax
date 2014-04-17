<?php

class Zolago_Po_Block_Vendor_Po_Grid_Column_Renderer_Products 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$items = $row->getData($this->getColumn()->getIndex());
		if(is_array($items)){
			return implode("<br/>", $items);
		}
		return '';
	}
}
