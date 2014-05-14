<?php
class Zolago_Po_Model_Po_Item extends Unirgy_DropshipPo_Model_Po_Item
{
	/**
	 * @return float
	 */
	public function getDiscount() {
		return round($this->getDiscountAmount()/$this->getQty(), 4);
	}

   public function _beforeSave() {
	   // Transfer fields
	   if((!$this->getId() || $this->isObjectNew()) && !$this->getSkipTransferOrderItemsData()){
		   $transferFields = array(
			   "price_incl_tax", 
			   "base_price_incl_tax", 
			   "discount_amount", 
			   "discount_percent", 
			   "row_total", 
			   "row_total_incl_tax", 
			   "base_row_total_incl_tax",
			   "parent_item_id"
			);
			$orderItem = $this->getOrderItem();
			if($orderItem && $orderItem->getId()){
				foreach($transferFields as $field){
					$this->setData($field, $orderItem->getData($field));
				}
			}
	   }
	   return parent::_beforeSave();
   }
   
   
	public function getFinalItemPrice() {
		return $this->getPriceInclTax() - $this->getDiscount();
	}
   
   public function getConfigurableText() {
	   	$request = $this->getOrderItem()->getProductOptionByCode("attributes_info");
		$out = array();
		if(is_array($request)){
			foreach($request as $item){
				$out[] = Mage::helper("zolagopo")->__($item['label']) . ": " . Mage::helper("zolagopo")->__($item['value']);
			}
		}
		if($out){
			return implode(", ", $out);
		}
		return "";
   }
   
   public function getFinalSku() {
	   return $this->getData('vendor_simple_sku') ? $this->getData('vendor_simple_sku') : $this->getData('sku');
   }
   
   public function getOneLineDesc() {
		$configurable = $this->getConfigurableText();
		return $this->getName() . " " .
			"(".
				 ($configurable ? $configurable . ", " : "") .
				 Mage::helper("zolagopo")->__("SKU") .   ": " . $this->getFinalSku() . ", " .
				 Mage::helper("zolagopo")->__("Qty") .   ": " . round($this->getQty(),2) . ", " .
				 Mage::helper("zolagopo")->__("Price") . ": " . Mage::helper("core")->currency($this->getFinalItemPrice(), true, false) .
			")";
   }
   
}
