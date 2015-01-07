<?php
class Zolago_SalesRule_Model_Resource_Relation extends Mage_Core_Model_Resource_Db_Abstract {
 /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('zolagosalesrule/relation', 'relation_id');
    }
	
	/**
	 * @param Mage_Sales_Model_Order_Item $item
	 * @return Zolago_SalesRule_Model_Resource_Relation
	 */
	public function saveForOrderItem(Mage_Sales_Model_Order_Item $item) {
		$relationData = $item->getDiscountInfo();
		$itemId = $item->getId();
		
		if(is_null($relationData)){
			return;
		}
		
		foreach($relationData as &$relation){
			$relation['order_item_id'] = $itemId;
		}
		
		$this->_getWriteAdapter()->delete(
			$this->getMainTable(), 
			array("order_item_id"=>$itemId)
		);
		
		$this->_getWriteAdapter()->insertMultiple(
			$this->getMainTable(), 
			$relationData
		);
		
		// Unset discount info to prevent newt steps saving
		$item->setDiscountInfo(null);
		
		return $this;
	}
}