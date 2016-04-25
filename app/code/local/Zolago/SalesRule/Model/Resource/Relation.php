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
		
		if(empty($relationData)){
			return;
		}
		
		foreach($relationData as &$relation){
			$relation['order_item_id'] = $itemId;
		}
		
		$this->_getWriteAdapter()->delete(
			$this->getMainTable(), 
			$this->_getWriteAdapter()->quoteInto("order_item_id=?", $itemId)
		);
		
		$this->_getWriteAdapter()->insertMultiple(
			$this->getMainTable(), 
			$relationData
		);
		
		
		return $this;
	}
	
	/**
	 * @param ZolagoOs_OmniChannelPo_Model_Po_Item $item
	 * @return Zolago_SalesRule_Model_Resource_Relation
	 */
	public function updateForPoItem(ZolagoOs_OmniChannelPo_Model_Po_Item $item) {
		
		$orderItemId = $item->getOrderItemId();
		$poItemId = $item->getId();
		// If obejct just created
		$this->_getWriteAdapter()->update(
			$this->getMainTable(), 
			array("po_item_id"=>$poItemId),
			$this->_getWriteAdapter()->quoteInto("order_item_id=?", $orderItemId)
		);
		return $this;
	}
	
	/**
	 * @param ZolagoOs_OmniChannelPo_Model_Po_Item $item
	 * @return Zolago_SalesRule_Model_Resource_Relation
	 */
	public function resetDiscountInfo(ZolagoOs_OmniChannelPo_Model_Po_Item $item) {
		$poItemId = $item->getId();
		// Remove all old entries
		$this->_getWriteAdapter()->delete(
			$this->getMainTable(), 
			$this->_getWriteAdapter()->quoteInto("po_item_id=?", $poItemId)
		);
        if (floatval($item->getDiscountAmount())) { // case: discount like "0.0000"
            // Set one new entry
            $this->_getWriteAdapter()->insert(
                $this->getMainTable(),
                array(
                    "rule_id"			=> null,
                    "order_item_id"		=> $item->getOrderItemId(),
                    "po_item_id"		=> $item->getId(),
                    "payer"				=> Zolago_SalesRule_Model_Rule_Payer::PAYER_VENDOR,
                    "discount_amount"	=> $item->getDiscountAmount(),
                    "name"				=> Mage::helper("zolagosalesrule")->__("Manual discount"),
                    "simple_action"		=> Mage_SalesRule_Model_Rule::BY_FIXED_ACTION
                )
            );
        }
		return $this;
	}
}