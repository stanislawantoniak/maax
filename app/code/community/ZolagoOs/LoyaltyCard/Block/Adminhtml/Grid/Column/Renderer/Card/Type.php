<?php

class ZolagoOs_LoyaltyCard_Block_Adminhtml_Grid_Column_Renderer_Card_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
	
	/**
	 * @param Varien_Object $row
	 * @return string
	 */
	public function render(Varien_Object $row) {
		$data = $row->getData($this->getColumn()->getIndex());
		$text = Mage::getSingleton("zolagocustomer/source_loyalty_card_types")->getOptionText($data);
		$string = $this->escapeHtml($text);
		return $string;
	}
}