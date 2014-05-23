<?php

class Zolago_Po_Block_Vendor_Po_Grid_Column_Renderer_Products 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$items = $row->getData($this->getColumn()->getIndex());
		$return = "";
		if(is_array($items)){
			foreach($items as $item){
				$return .= 
					round($item['qty']) . " &times; " . 
					$this->escapeHtml($item['name']) . "<br/>" . 
					"<small class=\"text-muted\">" . 
						$this->__("SKU") . ": " . ($item['vendor_sku'] ? $item['vendor_sku'] : $item['sku']) . ", " . 
						$this->__("Price") . ": " . Mage::helper('core')->currency($item['price_incl_tax']*(1-$item['discount_percent']/100), true, false) .
					"</small>" . "<br/>";
			}
			$return .= "</ul>";
		}
		return $return;
	}
}
