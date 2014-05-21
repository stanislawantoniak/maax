<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract extends Mage_Core_Block_Template
{
    public function __construct(array $args = array()){
		parent::__construct($args);
		$this->setTemplate("zolagopo/vendor/po/item/renderer/simple.phtml");
	}
	
	/**
	 * @param string $action
	 * @param array $params
	 * @return string
	 */
	public function getPoUrl($action, $params=array()) {
		return $this->getParentBlock()->getPoUrl($action, $params);
	}
	
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
		return $this->getParentBlock()->getPo();
	}
	
	/**
	 * @param Zolago_Po_Model_Po_Item $item
	 * @return string
	 */
	public function getOneLineDesc(Zolago_Po_Model_Po_Item $item) {
		return $this->escapeHtml($item->getName()) . " " .
			"(".
				 $this->__("SKU") .   ": " . $this->escapeHtml($this->getFinalSku($item)) . ", " .
				 $this->__("Qty") .   ": " . round($item->getQty(),2) . ", " .
				 $this->__("Price") . ": " . Mage::helper("core")->currency($this->getFinalItemPrice($item), true, false) .
			")";
	}
	
	/**
	 * @param int|null $qty
	 * @return string
	 */
	public function getQtyText($qty) {
		if(is_null($qty)){
			return $this->__("N/A");
		}
		return (string)$qty;
	}
	
	/**
	 * @param Zolago_Po_Model_Po_Item $item
	 * @return int
	 */
	public function getPosQty(Zolago_Po_Model_Po_Item $item){
		$pos = $this->getPo()->getPos();
		$vendor = $this->getPo()->getVendor();
		if($pos && $pos->getId() && $item->getVendorSku() && $vendor->getExternalId()){
			$qty = Mage::helper("zolagoconverter")->getQty($vendor, $pos, $item->getVendorSku());
			if(!is_null($qty)){
				return $qty;
			}
		}
		
		return $this->__("N/A");
	}
	

}
