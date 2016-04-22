<?php
class Zolago_Po_Block_Vendor_Po_Edit_Pos_Stock
	extends Mage_Core_Block_Template
{
	const CALSS_OUT_OF_STOCK = "danger";
	const CALSS_IN_STOCK = "success";
	const CALSS_NOT_AVAILABLE = "default";
	
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
	 * @return ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Item_Collection
	 */
	public function getItemsCollection() {
		return $this->getPo()->getAllItems();
	}
	
	/**
	 * @param numeric $qty
	 * @return string
	 */
	public function getLabelClass($qty) {
		if(is_null($qty)){
			return self::CALSS_NOT_AVAILABLE;
		}elseif($qty>$this->getMinimalStock()){
			return self::CALSS_IN_STOCK;
		}
		return self::CALSS_OUT_OF_STOCK;
	}
	
	/**
	 * @param int|null $qty
	 * @return string
	 */
	public function getQtyText($qty) {
		if(is_null($qty)){
			return $this->__("N/A");
		}
		return $qty;
	}
	
	/**
	 * @return int
	 */
	public function getMinimalStock() {
		return is_numeric($this->getPos()->getMinimalStock()) ? 
			$this->getPos()->getMinimalStock() : self::DEFUALT_MINIMAL_QTY;
	}
	
	/**
	 * @param ZolagoOs_OmniChannelPo_Model_Po_Item $item
	 * @return int | null
	 */
	public function getPosQty(Zolago_Po_Model_Po_Item $item){
		$pos = $this->getPos();
		$vendor = $this->getPo()->getVendor();
		if($pos && $pos->getId() && $item->getFinalSku() && $vendor->getExternalId()){
			return Mage::helper("zolagoconverter")->getQty($vendor, $pos, $item->getFinalSku());
		}
		
		return null;
	}
	

	
}
