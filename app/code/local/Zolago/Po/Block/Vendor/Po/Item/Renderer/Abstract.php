<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract extends Mage_Core_Block_Template
{
	static $_rulesActions;
	
	public function __construct(array $args = array()){
		parent::__construct($args);
		$this->setTemplate("zolagopo/vendor/po/item/renderer/simple.phtml");
	}
	
	/**
	 * @param Zolago_SalesRule_Model_Relation $relation
	 * @return string
	 */
	public function getAlgorithmName(Zolago_SalesRule_Model_Relation $relation) {
		$allActions = $this->getAllRuleActions();
		if(isset($allActions[$relation->getSimpleAction()])){
			return $allActions[$relation->getSimpleAction()];
		}
		return $this->__("N/A");
	}
	
	
	/**
	 * @param Zolago_SalesRule_Model_Relation $relation
	 * @return string
	 */
	public function getPayerName(Zolago_SalesRule_Model_Relation $relation) {
		switch ($relation->getPayer()) {
			case Zolago_SalesRule_Model_Rule_Payer::PAYER_GALLERY:
				return $this->__("Gallery");
			break;
			case Zolago_SalesRule_Model_Rule_Payer::PAYER_VENDOR:
				return $this->__("Vendor");
			break;
		}
		return '';
	}
	
	/**
	 * @return array
	 */
	public function getAllRuleActions() {
		if(!self::$_rulesActions){
			$allActions = array(
				Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => 
					Mage::helper('salesrule')->__('Percent of product price discount'),
                Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => 
					Mage::helper('salesrule')->__('Fixed amount discount'),
                Mage_SalesRule_Model_Rule::CART_FIXED_ACTION =>
					Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),
                Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION => 
					Mage::helper('salesrule')->__('Buy X get Y free (discount amount is Y)'),
			);
			
			// Add Amasty rules if enabled
			if(Mage::helper('core')->isModuleEnabled("Amasty_Rules")){
				$allActions = array_merge($allActions, Mage::helper("amrules")->getDiscountTypes(true));
			}
			self::$_rulesActions = $allActions;
		}
		return self::$_rulesActions;
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
		if($pos && $pos->getId() && $item->getFinalSku() && $vendor->getExternalId()){
			$qty = Mage::helper("zolagoconverter")->getQty($vendor, $pos, $item->getFinalSku());
			if(!is_null($qty)){
				return $qty;
			}
		}
		
		return $this->__("N/A");
	}
	

}
