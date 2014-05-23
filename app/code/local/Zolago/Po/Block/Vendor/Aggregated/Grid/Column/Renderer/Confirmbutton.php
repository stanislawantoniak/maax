<?php

class Zolago_Po_Block_Vendor_Aggregated_Grid_Column_Renderer_Confirmbutton
    extends Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Confirmbutton
{
	public function render(Varien_Object $row){
		if($row->getStatus()!=Zolago_Po_Model_Aggregated_Status::STATUS_CONFIRMED){
			return parent::render($row);
		}
		return '';
	}
}