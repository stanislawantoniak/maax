<?php

class Zolago_Rma_Block_Vendor_Rma_Grid_Column_Renderer_Products 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$items = $row->getData($this->getColumn()->getIndex());
		$return = "";
		if(is_array($items)){
			foreach($items as $item){
				if($item->getItemConditionName()) {
					/* @var $item Zolago_Rma_Model_Rma_Item */
					$return .= "[" . $item->getItemConditionName() . "] " .
						$this->escapeHtml($item->getName()) . "<br/>";
				}
			}
		}
		return $return;
	}
}
