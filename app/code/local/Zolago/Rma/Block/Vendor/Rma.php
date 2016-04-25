<?php

class Zolago_Rma_Block_Vendor_Rma extends Mage_Core_Block_Template
{
	
	protected function _beforeToHtml() {
		$this->getGrid();
		return parent::_beforeToHtml();
	}

	public function getGridJsObjectName() {
		return $this->getGrid()->getJsObjectName();
	}

    protected function _prepareLayout()
    {
        //fix for horizontal scroll for grid
        $this->getLayout()
            ->getBlock('root')
            ->addBodyClass('grid-hscroll-fix')
            ->addBodyClass('grid-hscroll-1150w');
        return parent::_prepareLayout();
    }

	/**
	 * @return Zolago_Rma_Block_Vendor_Rma_Grid
	 */
	public function getGrid() {
		if(!$this->getData("grid")){
			$design = Mage::getDesign();
			$design->setArea("adminhtml");
			$block = $this->getLayout()->
					createBlock("zolagorma/vendor_rma_grid");
			$block->setParentBlock($this);
			$this->setGridHtml($block->toHtml());
			$this->setData("grid", $block);
			$design->setArea("frontend");
		}
		return $this->getData("grid");
	}
	
	public function getFilterValue($index) {
		return $this->getGrid()->getFilterValueByIndex($index);
	}

	public function getCreatedAt($idx) {
		if(($v=$this->getFilterValue('created_at')) && is_array($v) && isset($v[$idx])){
			return $v[$idx];
		}
		return null;
	}
	
	public function getMaxDateExceed($val) {
		if(($v=$this->getFilterValue('max_date_exceed')) && is_array($v) && in_array($val, $v)){
			return 1;
		}
		return null;
	}

	public function getConditionOptions() {
		
		if(!is_null($this->getFilterValue('rma_item_condition'))){
			$values = $this->getFilterValue('rma_item_condition');
		}else{
			$values = $this->getDefaultItemCondition();
		}
		
		$allFilters =  Mage::getSingleton('urma/source')->setPath('rma_item_condition')->toOptionHash();
		$out = array();
		
		foreach($allFilters as $key=>$label){
			$item = array(
				"value" => $key,
				"label" => $label
			);
			if(is_array($values) && in_array($key, $values)){
				$item['checked'] = true;
			}
			$out[] = $item;
		}
		
		return $out;
	}

	public function getDefaultItemCondition() {
		return array();
	}
	
	public function getStatusOptions() {
		
		if(!is_null($this->getFilterValue('rma_status'))){
			$values = $this->getFilterValue('rma_status');
		}else{
			$values = $this->getDefaultStatuses();
		}
		$allFilters = Mage::helper('urma')->getVendorRmaStatuses();
		$out = array();
		
		foreach($allFilters as $key=>$label){
			$item = array(
				"value" => $key,
				"label" => $this->__($label)
			);
			if(is_array($values) && in_array($key, $values)){
				$item['checked'] = true;
			}
			$out[] = $item;
		}
		
		return $out;
	}
	
	
	public function getDefaultStatuses() {
		/*$statuses = $this->getVendor()->getData('vendor_po_grid_status_filter');
		return is_array($statuses) ? $statuses : array();*/
		return array();
	}
//	
//	public function getPosCollection() {
//		$collection = Mage::getResourceModel('zolagopos/pos_collection');
//		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
//		$collection->addVendorFilter($this->getVendor());
//		return $collection;
//	}
//	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	public function getVendor() {
		return Mage::getSingleton('udropship/session')->getVendor();
	}
	
}
