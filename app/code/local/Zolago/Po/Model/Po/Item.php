<?php
class Zolago_Po_Model_Po_Item extends Unirgy_DropshipPo_Model_Po_Item
{
	/**
	 * @return float
	 */
	public function getDiscount() {
		return round($this->getDiscountAmount()/$this->getQty(), 4);
	}
	
	/**
	 * Overrride if we have no order item 
	 * @return int
	 */
	public function getQtyToShip() {
		if(!$this->hasOrderItem()){
			return max(0, $this->getQty()-$this->getQtyShipped()-$this->getQtyCanceled());
		}
		return parent::getQtyToShip();
	}
	
	public function getOrderItem() {
		 if (is_null($this->_orderItem)) {
            if ($this->getPo()
            	&& ($orderItem = Mage::helper('udropship')->getOrderItemById($this->getPo()->getOrder(), $this->getOrderItemId()))
            ) {
                $this->_orderItem = $orderItem;
            }
            else {
                $this->_orderItem = Mage::getModel('sales/order_item')
                    ->load($this->getOrderItemId());
            }
			// Process abstract order item
			if(!$this->_orderItem->getId()){
				Mage::helper("zolagopo")->prepareOrderItemByPoItem($this->_orderItem, $this);
				$order = $this->_orderItem->getOrder();
				/* @var $order Mage_Sales_Model_Order */
				$this->_orderItem->save();
				$this->setOrderItemId($this->_orderItem->getId());
				if($this->getId()){
					$this->getResource()->saveAttribute($this, "order_item_id");
				}
			}
        }
        return $this->_orderItem;
	}
	
	public function hasOrderItem() {
		return (bool)(int)$this->getOrderItem()->getId();
	}

	
   public function _beforeSave() {
	   
	   // Process order item if needed
		if(!$this->getOrderItemId()){
			$this->getOrderItem();
		}
		
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
