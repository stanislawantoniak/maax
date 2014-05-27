<?php

class Zolago_Po_Block_Vendor_Po_Grid_Column_Renderer_Products 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$items = $row->getData($this->getColumn()->getIndex());
		$return = "";
		if(is_array($items)){
			foreach($items as $item){
				/* @var $item Zolago_Po_Model_Po_Item */
				$return .= 
					round($item->getQty()) . " &times; " . 
					$this->escapeHtml($item->getName()) . "<br/>" . 
					"<small class=\"text-muted\">" . 
						$this->__("SKU") . ": " . ($item->getFinalSku() ? $item->getFinalSku() : $this->__('N/A')) . ", " . 
						$this->__("Price") . ": " . Mage::helper('core')->currency($item->getPriceInclTax()*(1-$item->getDiscountPercent()/100), true, false) .
					"</small>" . "<br/>";
			}
			$return .= "</ul>";
		}
		return $return;
	}
}
