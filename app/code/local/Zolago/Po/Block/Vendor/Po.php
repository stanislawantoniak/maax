<?php

class Zolago_Po_Block_Vendor_Po extends Mage_Core_Block_Template
{
	
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
					createBlock("zolagopo/vendor_po_grid");
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
	public function getMaxShippingDate($idx) {
		if(($v=$this->getFilterValue('max_shipment_date')) && is_array($v) && isset($v[$idx])){
			return $v[$idx];
		}
		return null;
	}
	public function getShipmentDate($idx) {
		if(($v=$this->getFilterValue('shipment_date')) && is_array($v) && isset($v[$idx])){
			return $v[$idx];
		}
		return null;
	}
	
	public function getDefaultPosId() {
		return $this->getFilterValue('default_pos_id');
	}
	
	public function getStatusOptions() {
		
		if(!is_null($this->getFilterValue('udropship_status'))){
			$values = $this->getFilterValue('udropship_status');
		}else{
			$values = $this->getDefaultStatuses();
		}
		
		$allFilters = Mage::helper('udpo')->getVendorUdpoStatuses();
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
	
	
	public function getDefaultStatuses() {
		$statuses = $this->getVendor()->getData('vendor_po_grid_status_filter');
		return is_array($statuses) ? $statuses : array();
	}
	
	public function getPosCollection() {
		$collection = Mage::getResourceModel('zolagopos/pos_collection');
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		$collection->addVendorFilter($this->getVendor());
		return $collection;
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return Mage::getSingleton('udropship/session')->getVendor();
	}
	
}
