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
   
}
