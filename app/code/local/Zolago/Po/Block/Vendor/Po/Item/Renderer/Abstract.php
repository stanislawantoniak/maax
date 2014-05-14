<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract extends Mage_Core_Block_Template
{
    public function __construct(array $args = array()){
		parent::__construct($args);
		$this->setTemplate("zolagopo/vendor/po/item/renderer/simple.phtml");
	}
	
	public function getPoUrl($action, $params=array()) {
		return $this->getParentBlock()->getPoUrl($action, $params);
	}
	
	
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
		return "[dev]";
	}

}
