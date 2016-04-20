<?php
class Zolago_Po_Model_Resource_Po_Item_Collection 
	extends ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Item_Collection
{
	
	/**
	 * @param ZolagoOs_OmniChannelPo_Model_Po_Item|Mage_Sales_Model_Order_Item|int $parent
	 * @return \Zolago_Po_Model_Resource_Po_Item_Collection
	 */
   public function addParentFilter($parent) {
	   $orderItemId = null;
	   if($parent instanceof ZolagoOs_OmniChannelPo_Model_Po_Item){
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
   
   /**
    * @return Zolago_Po_Model_Resource_Po_Item_Collection
    */
   public function addDiscountInfo() {
	   $this->setFlag("add_discount_info", 1);
	   return $this;
   }
   
   public function _afterLoad() {
	   $ret = parent::_afterLoad();
	   if($this->getFlag('add_discount_info')){
		   $collection = Mage::getResourceModel('zolagosalesrule/relation_collection');
		   /* @var $collection Zolago_SalesRule_Model_Resource_Relation_Collection */
		   $collection->addFieldToFilter("order_po_id", $this->getAllIds());
		   $grouped = array();
		   foreach($collection as $relation){
			   $grouped[$relation->getPoImteId()][] = $relation;
		   }
		   foreach($this as $item){
			   $item->setDiscountInfo(isset($grouped[$item->getId()]) ? $grouped[$item->getId()] : array());
		   }
	   }
	   return $ret;
   }
   
   
}
