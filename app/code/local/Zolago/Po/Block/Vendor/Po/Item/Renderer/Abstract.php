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
	 * @param Zolago_Po_Model_Po_Item $item
	 * @return int
	 */
	public function getPosQty(Zolago_Po_Model_Po_Item $item){
		$pos = $this->getPo()->getPos();
		if($pos && $pos->getId() && $item->getVendorSku()){
			return $this->_getPosQty($pos, $item->getVendorSku());
		}
		
		return $this->__("N/A");
	}
	
	protected function _getPosQty(Zolago_Pos_Model_Pos $pos, $vsku) {
		return Mage::helper("zolagoconverter")->getQtyForPos($pos,$vsku);
	}

}
