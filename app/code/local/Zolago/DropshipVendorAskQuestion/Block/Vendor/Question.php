<?php
class Zolago_DropshipVendorAskQuestion_Block_Vendor_Question extends Mage_Core_Block_Template {
	
	
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
					createBlock("zolagoudqa/vendor_question_grid");
			$block->setParentBlock($this);
			$this->setGridHtml($block->toHtml());
			$this->setData("grid", $block);
			$design->setArea("frontend");
		}
		return $this->getData("grid");
	}
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('udropship/session');
	}

    protected function _prepareLayout()
    {
        //fix for horizontal scroll for grid
        $this->getLayout()
            ->getBlock('root')
            ->addBodyClass('grid-hscroll-fix')
            ->addBodyClass('grid-hscroll-950w');
        return parent::_prepareLayout();
    }
}
