<?php

/**
 * Class Zolago_Po_Model_Po_Item
 * @method string getVendorSimpleSku()
 * @method string getName()
 * @method float getQty()
 */
class Zolago_Po_Model_Po_Item extends Unirgy_DropshipPo_Model_Po_Item
{
	/**
	 * @return array
	 */
	public function getDiscountInfo() {
		if(!$this->hasData("discount_info")){
			$collection = Mage::getResourceModel('zolagosalesrule/relation_collection');
			/* @var $collection Zolago_SalesRule_Model_Resource_Relation_Collection */
			$collection->addFieldToFilter("po_item_id", $this->getId());
			$this->setData('discount_info', $collection->getItems());
		}
		return $this->getData("discount_info");
	}
	
	/**
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct() {
		if (!$this->getData('product')) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            $this->setData('product', $product);
        }
		return $this->getData('product');
	}
	
	/**
	 * @return Mage_Catalog_Helper_Image
	 */
	public function getProductThumbHelper() {
		$_storeId = $this->getPo() ? $this->getPo()->getStoreId() : Mage::app()->getStore()->getStoreId();
		$thumb = Mage::getResourceModel("catalog/product")->getAttributeRawValue(
			$this->getProductId(),
			'thumbnail',
			$_storeId
		);

		$product = Mage::getModel("catalog/product")->
			setId($this->getProductId())->
			setThumbnail($thumb);
		return Mage::helper("catalog/image")->init($product, 'thumbnail');
	}
	
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
	
	/**
	 * @return Mage_Sales_Model_Order_Item
	 */
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
   
   
	public function getFinalProductPrice() {
		return $this->getPriceInclTax() + (-1 * $this->getDiscountAmount()/$this->getQty());
	}
	
	public function getProductDiscountPrice() {
		return $this->getFinalProductPrice();
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
   
   /**
    * @return string
    */
   public function getFinalSku() {

//	   $child = $this->getChildItem();
//	   if($child && $child->getId() && $child->getData('vendor_sku')){
//		   return $child->getData('vendor_sku');
//	   }
//	   
	   if($this->getData('vendor_simple_sku')){
		   return $this->getData('vendor_simple_sku');
	   }
	   
	   
	   return $this->getData('vendor_sku');
   }
   
   
   /**
    * @todo not tested
    * @return Zolago_Po_Model_Po_Item
    */
   public function getParentOrderItem() {
	   if(!$this->hasData("parent_order_item")){
			$parent = Mage::getResourceModel('sales/order_item_collection')->
				 addFieldToFilter("item_id", $this->getParentItemId())->
					getFirstItem();
			$this->setData("parent_order_item", $parent);
	   }
	   return $this->getData("parent_order_item");
   }
   
   /**
    * @todo not tested
    * @return Zolago_Po_Model_Po_Item
    */
   public function getParentItem() {
	   if(!$this->hasData("parent_item")){
			$parent = Mage::getResourceModel('zolagopo/po_item_collection')->
				 addFieldToFilter("order_item_id", $this->getParentItemId())->
					getFirstItem();
			$this->setData("parent_item", $parent);
	   }
	   return $this->getData("parent_item");
   }
   
   /**
    * @return Zolago_Po_Model_Po_Item
    */
   public function getChildItem() {
	   if(!$this->hasData("child_item")){
			$parent = Mage::getResourceModel('zolagopo/po_item_collection')->
				 addFieldToFilter("parent_item_id", $this->getOrderItem()->getId())-> // Order item id ? @todo
					getFirstItem();
			$this->setData("child_item", $parent);
	   }
	   return $this->getData("child_item");
   }
   
   /**
    * @return string
    */
   public function getOneLineDesc() {
		$configurable = $this->getConfigurableText();
		return $this->getName() . " " .
			"(".
		    	 ($configurable ? $configurable . ", " : "") .
				Mage::helper("zolagopo")->__("Qty") .   ": " . (int)$this->getQty() . ", " . 
				Mage::helper("zolagopo")->__("Price") . ": " . Mage::helper("core")->currency($this->getPriceInclTax(), true, false) . ", " . 
			    Mage::helper("zolagopo")->__("Discount").": " . Mage::helper("core")->currency($this->getProductDiscountPrice(), true, false) . ", " . 
				Mage::helper("zolagopo")->__("SKU") .   ": " . $this->getFinalSku() .
			")";
   }
   
}
