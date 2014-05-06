<?php
class Zolago_Po_Block_Vendor_Po_Edit_Pos_Stock
	extends Mage_Core_Block_Template
{
	const CALSS_DANGER = "danger";
	const CALSS_SUCCESS = "success";
	
	const DEFUALT_MINIMAL_QTY = 1;
	
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
		return Mage::registry("current_po");
	}
	
	
	/**
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getPos() {
		return Mage::registry("current_pos");
	}
	
	/**
	 * @return Unirgy_DropshipPo_Model_Mysql4_Po_Item_Collection
	 */
	public function getItemsCollection() {
		return $this->getPo()->getAllItems();
	}
	
	/**
	 * @param numeric $qty
	 * @return string
	 */
	public function getLabelClass($qty) {
		if($qty>$this->getMinimalStock()){
			return self::CALSS_SUCCESS;
		}
		return self::CALSS_DANGER;
	}
	
	/**
	 * @return int
	 */
	public function getMinimalStock() {
		return is_numeric($this->getPos()->getMinimalStock()) ? 
			$this->getPos()->getMinimalStock() : self::DEFUALT_MINIMAL_QTY;
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po_Item $item
	 * @return int
	 */
	public function getPosQty(Unirgy_DropshipPo_Model_Po_Item $item) {
		return $this->_getPosQty($item, $this->getPos());
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po_Item $item
	 * @param Zolago_Pos_Model_Pos $pos
	 * @return int
	 */
	protected function _getPosQty(Unirgy_DropshipPo_Model_Po_Item $item, 
		Zolago_Pos_Model_Pos $pos) {
		/**
		 * @todo implement
		 */
		return $this->_getRandomStock();
	}
	
	/**
	 * @return int
	 */
	protected function _getRandomStock() {
		return rand(0,10);
	}

}
