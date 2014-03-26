<?php
class Zolago_Catalog_Block_Vendor_Mass_Editor extends Mage_Core_Block_Template {
    public function getGrid() {
		if($this->getParentBlock() && $this->getParentBlock()->getGrid()){
			return $this->getParentBlock()->getGrid();
		}
		return null;
	}
	public function getSubmitButtonHtml() {
		$btn = $this->getLayout()->
				createBlock("adminhtml/widget_button")->
				setLabel("Confirm changes");
		return $btn->toHtml();
	}
}