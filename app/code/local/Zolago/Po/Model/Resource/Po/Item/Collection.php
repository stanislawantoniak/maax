<?php
class Zolago_Po_Model_Resource_Po_Item_Collection 
	extends Unirgy_DropshipPo_Model_Mysql4_Po_Item_Collection
{
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po_Item|Mage_Sales_Model_Order_Item|int $parent
	 * @return \Zolago_Po_Model_Resource_Po_Item_Collection
	 */
   public function addParentFilter($parent) {
	   $orderItemId = null;
	   if($parent instanceof Unirgy_DropshipPo_Model_Po_Item){
		   $orderItemId = $parent->getOrderItemId();
	   }elseif($parent instanceof Mage_Sales_Model_Order_Item){
		   $orderItemId = $parent->getId();
	   }elseif(is_numeric ($parent)){
		   $orderItemId = $parent;
	   }
	   
	   // No join 
	   if(!is_null($orderItemId)){
			$select = $this->_conn->select();
			$select->from($this->getTable('sales/order_item'), "item_id");
			$select->where("parent_item_id=?", $orderItemId);
			$this->addFieldToFilter("order_item_id", array("in"=>$this->_conn->fetchCol($select)));
	   }else{
		   $this->addFieldToFilter("entity_id", -1); // no results
	   }
	   
	   return $this;
   }
}
