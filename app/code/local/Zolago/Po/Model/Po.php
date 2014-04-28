<?php

class Zolago_Po_Model_Po extends Unirgy_DropshipPo_Model_Po
{
	const TYPE_POSHIPPING = "poshipping";
	
	public function getPos() {
		if($this->getDefaultPosId()){
			return Mage::getModel("zolagopos/pos")->load($this->getDefaultPosId());
		}
		return null;
	}
	
	public function getSubtotalInclTax() {
		$total = 0;
		foreach($this->getAllItems() as $item){
			$total += $this->calcuateItemPrice($item) * $item->getQty() - $item->getDiscountAmount();
		}
		return $total;
	}
	
	
	public function getShippingDiscountIncl() {
		return $this->getBaseShippingAmountIncl()-$this->getShippingAmountIncl();
	}
	
	/**
	 * 
	 * @param Unirgy_DropshipPo_Model_Po_Item $item
	 * @return type
	 */
	public function calcuateItemPrice(Unirgy_DropshipPo_Model_Po_Item $item, $inclTax=true) {
		return $inclTax ? $item->getPriceInclTax() : $item->getPriceExclTax();
	}
	
	
   // Override address
   public function getShippingAddress() {
	   if($this->getShippingAddressId()){
		   $address = $this->getOrder()->getAddressById($this->getShippingAddressId());
		   if($address->getId()){
			   return $address;
		   }
	   }
	   return parent::getShippingAddress();
   }
   
   // Override address
   public function getBillingAddress() {
	   if($this->getBillingAddressId()){
		   $address = $this->getOrder()->getAddressById($this->getBillingAddressId());
		   if($address->getId()){
			   return $address;
		   }
	   }
	   return parent::getBillingAddress();
   }
   
   // Address wasn't overrriden?
   public function isShippingSameAsOrder() {
	   return $this->getShippingAddress()->getId() == $this->getOrder()->getShippingAddress()->getId();
   }
   
   public function setOwnShippingAddress(Mage_Sales_Model_Order_Address $address, $append=false){
	   $address->setId(null);
	   $address->setParentId($this->getOrder()->getId());
	   $address->setAddressType(self::TYPE_POSHIPPING);
	   $address->save();
	   $this->setShippingAddressId($address->getId());
	   
	   // Remove not used addresses
	   if(!$append){
		  $this->cleanAddresses(array($address->getId()));
	   }
	   
	   return $this;
   }
   
   public function clearOwnShippingAddress(){
	   if($this->isShippingSameAsOrder()){
		   return $this;
	   }
	   $this->setShippingAddressId(
			$this->getOrder()->getShippingAddress()->getId()
	   );
	   $this->cleanAddresses();
	   return $this;
   }
   
   public function cleanAddresses($exclude=array()) {
	    // Add this shippign id
		$exclude[] = $this->getShippingAddressId();
	    $addressCollection = Mage::getResourceModel("sales/order_address_collection");
		/* @var $addressCollection Mage_Sales_Model_Resource_Order_Address_Collection */
		$addressCollection->addFieldToFilter("parent_id", $this->getOrder()->getId());
		$addressCollection->addFieldToFilter("entity_id", array("nin"=>$exclude));
		$addressCollection->addFieldToFilter("address_type", self::TYPE_POSHIPPING);
		$select = $addressCollection->getSelect();
		$subSelect = $select->getAdapter()->select();
		$subSelect->from( 
				array("shipment"=>$this->getResource()->getTable("sales/shipment")),
				array(new Zend_Db_Expr("COUNT(shipment.entity_id)"))
		);
		$subSelect->where("shipment.shipping_address_id=main_table.entity_id");

		$select->where("? < 1", $subSelect);

		foreach($addressCollection as $toDelete){
			$toDelete->delete();
		}
   }
   
   public function needInvoice() {
	   return $this->getBillingAddress()->getNeedInvoice();
   }
   
   /**
    * @return Zolago_Pos_Model_Pos
    */
   public function getDefaultPos() {
	   $pos = Mage::getModel("zolagopos/pos");
	   if($this->getDefaultPosId()){
			$pos->load($this->getDefaultPosId());
	   }
	   return $pos;
   }
   
   public function updateTotals($force=false) {
	    if($force || !$this->getGrandTotalInclTax()){
			$this->setGrandTotalInclTax($this->getSubtotalInclTax()+$this->getShippingAmountIncl());
			$this->save();
		}
		return $this;
   }
   
	protected function _afterSave(){
		$ret = parent::_afterSave();
		$this->updateTotals();
		return $ret;
	} 
   
}
