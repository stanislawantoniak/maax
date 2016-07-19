<?php

/**
 * Class ZolagoOs_LoyaltyCard_Block_Vendor_Card
 */
class ZolagoOs_LoyaltyCard_Block_Vendor_Card extends ZolagoOs_LoyaltyCard_Block_Vendor_Card_Abstract {
	
	
	protected function _beforeToHtml() {
		$this->getGrid();
		return parent::_beforeToHtml();
	}

	public function getGridJsObjectName() {
		return $this->getGrid()->getJsObjectName();
	}

	/**
	 * @return Zolago_Po_Block_Vendor_Po_Grid
	 */
	public function getGrid() {
		if (!$this->getData("grid")) {
			$design = Mage::getDesign();
			$design->setArea("adminhtml");
			$block = $this->getLayout()->
			createBlock("zosloyaltycard/vendor_card_grid");
			$block->setParentBlock($this);
			$this->setGridHtml($block->toHtml());
			$this->setData("grid", $block);
			$design->setArea("frontend");
		}
		return $this->getData("grid");
	}
}