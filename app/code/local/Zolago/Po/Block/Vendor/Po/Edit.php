<?php

class Zolago_Po_Block_Vendor_Po_Edit extends Unirgy_DropshipPo_Block_Vendor_Po_Info
{
	/**
	 * @return Zolago_Po_Model_Po
	 */
    public function getPo(){
		return parent::getPo();
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @return string
	 */
	public function getCurrentStatus(Unirgy_DropshipPo_Model_Po $po) {
		$statuses = $this->getAllowedStatuses();
		$statusId = $this->getPo()->getUdropshipStatus();
		if(isset($statuses[$statusId])){
			return $this->__($statuses[$statusId]);;
		}
		return '';
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getPos(Unirgy_DropshipPo_Model_Po $po) {
		return $po->getPos();
	}
	
	/**
	 * @return array
	 */
	public function getAllowedStatuses() {
		return Mage::helper('udpo')->getVendorUdpoStatuses();
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @return array
	 */
	public function getAllowedStatusesForPo(Unirgy_DropshipPo_Model_Po $po) {
		return $this->getAllowedStatuses();
	}
	
	/**
	 * 
	 * @return decimal
	 */
	public function getTotal() {
		$totals = $this->getTotals();
		var_export($totals);
		if(isset($totals['total'])){
			return $totals['total'];
		}
		return 0;
	}
	
	public function getTotals() {
		return $this->getPo()->getUdropshipTotals();
	}
	
	
	public function	getItemRedener(Unirgy_DropshipPo_Model_Po_Item $item) {
		$orderItem = $item->getOrderItem();
		$type=$orderItem->getProductType();
		return $this->_getRendererByType($type)->setItem($item);
		
	}
	
	/**
	 * @param type $type
	 * @return Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
	 */
	protected function _getRendererByType($type) {
		$renderPath = "zolagopo/vendor_po_item_renderer_";
		switch ($type) {
			case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE: 
				/*@todo add other types*/
				$renderPath.=$type;
			break;
			default:
				$renderPath.=Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
			break;
		}
		return $this->getLayout()->createBlock($renderPath);
	}
  
}
