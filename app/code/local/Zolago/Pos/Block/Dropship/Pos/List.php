<?php
class Zolago_Pos_Block_Dropship_Pos_List extends Mage_Core_Block_Template {
	
	
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
		if(!$this->getData("grid")){
			$design = Mage::getDesign();
			$design->setArea("adminhtml");
			$block = $this->getLayout()->
					createBlock("zolagopos/dropship_pos_grid");
			$block->setParentBlock($this);
			$this->setGridHtml($block->toHtml());
			$this->setData("grid", $block);
			$design->setArea("frontend");
		}
		return $this->getData("grid");
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('udropship/session');
	}
}
