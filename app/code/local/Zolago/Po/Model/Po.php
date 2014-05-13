<?php

class Zolago_Po_Model_Po extends Unirgy_DropshipPo_Model_Po
{
	const TYPE_POSHIPPING = "poshipping";
	const TYPE_POBILLING = "pobilling";
	
	/**
	 * @param array $itemIds
	 * @return Zolago_Po_Model_Po
	 * @throws Exception
	 */
	public function split(array $itemIds) {
		
		$transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
		/* @var $transaction Varien_Db_Adapter_Interface */
		try{
			$transaction->beginTransaction();
			$collection = $this->getItemsCollection() ;
			/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Item_Collection */

			$newModel = Mage::getModel("zolagopo/po");
			/* @var $newModel Zolago_Po_Model_Po */


			////////////////////////////////////////////////////////////////////
			// Process items
			////////////////////////////////////////////////////////////////////
			foreach($collection as $itemId=>$item){
				
				if(in_array($itemId, $itemIds)){
					$collection->removeItemByKey($itemId);
					$item->setId(null); // force add item to colelciton in po model
					$newModel->addItem($item);
					$item->setId($itemId); 
					
					// Proces child items
					$childCollection = Mage::getResourceModel('zolagopo/po_item_collection');
					/* @var $childCollection Zolago_Po_Model_Resource_Po_Item_Collection */

					$childCollection->addParentFilter($item);

					foreach($childCollection as $childItemId=>$childItem){
						$collection->removeItemByKey($childItemId);
						$childItem->setId(null);
						$newModel->addItem($childItem);
						$childItem->setId($childItemId); 
					}
				}
			}
			
			////////////////////////////////////////////////////////////////////
			// Process addresses - clone custom addresses or referene origin
			////////////////////////////////////////////////////////////////////
			if(!$this->isBillingSameAsOrder()){
				$newBilling = clone $this->getBillingAddress();
				$newBilling->setCreatedAt(null);
				$newBilling->setUpdatedAt(null);
				$newBilling->setId(null);
				$newBilling->save();
			}else{
				$newBilling = $this->getBillingAddress();
			}
			
			if(!$this->isShippingSameAsOrder()){
				$newShipping = clone $this->getShippingAddress();
				$newShipping->setCreatedAt(null);
				$newShipping->setUpdatedAt(null);
				$newShipping->setId(null);
				$newShipping->save();
			}else{
				$newShipping = $this->getShippingAddress();
			}
			
			
			////////////////////////////////////////////////////////////////////
			// Process misc data
			////////////////////////////////////////////////////////////////////
			$newModel->addData($this->getData());
			$newModel->setId(null);
			$newModel->setShippingAddressId($newShipping->getId());
			$newModel->setBillingAddressId($newBilling->getId());
			$newModel->setCreatedAt(null);
			$newModel->setUpdatedAt(null);
			$newModel->setIncrementId($this->_getNextIncementId());
				
			////////////////////////////////////////////////////////////////////
			// Process comments
			////////////////////////////////////////////////////////////////////
			$comments = $newModel->getCommentsCollection();
			/* @var $comments Unirgy_DropshipPo_Model_Mysql4_Po_Comment_Collection */
			
			foreach($this->getCommentsCollection() as $comment){
				/* @var $comment Unirgy_DropshipPo_Model_Po_Comment */
				$tmpComment = clone $comment;
				$tmpComment->setId(null);
				$tmpComment->setParentId(null);
				$tmpComment->setPo($newModel);
				$comments->addItem($tmpComment);
			}
			
			$newModel->setCommentsChanged(true);
			
			////////////////////////////////////////////////////////////////////
			// Save objects
			////////////////////////////////////////////////////////////////////
			$newModel->updateTotals(true);
			$this->updateTotals(true);
			
			$transaction->commit();
		} catch (Exception $ex) {
			$transaction->rollBack();
			throw $ex;
		}
		return $newModel;
	}
	
	public function setCommentsChanged($value) {
		$this->_commentsChanged = $value;
		return $this;
	}
	
	protected function _getNextIncementId() {
		$collection = Mage::getResourceModel('zolagopo/po_collection');
		/* @var $collection Zolago_Po_Model_Resource_Po_Collection */
		$collection->setOrderFilter($this->getOrder());
		
		$currentIncrement = explode("-", $this->getIncrementId());
		$base = $currentIncrement[0];
		$maxIncrement = $currentIncrement[1];
		foreach($collection as $po){
			$arr = explode("-", $po->getIncrementId());
			// Same base
			if($arr[0]==$base){
				$maxIncrement = max($maxIncrement, (int)$arr[1]);
			}
		}
		
		return $base . "-" . ($maxIncrement+1);
	}
	
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
   
   
   public function isShippingSameAsOrder() {
	   return $this->getShippingAddress()->getId() == $this->getOrder()->getShippingAddress()->getId();
   }
   
   public function isBillingSameAsOrder() {
	   return $this->getBillingAddress()->getId() == $this->getOrder()->getBillingAddress()->getId();
   }
   
   public function setOwnShippingAddress(Mage_Sales_Model_Order_Address $address, $append=false){
	    return $this->_setOwnAddress(self::TYPE_POSHIPPING, $address, $append);
   }
   
   public function setOwnBillingAddress(Mage_Sales_Model_Order_Address $address, $append=false){
	   return $this->_setOwnAddress(self::TYPE_POBILLING, $address, $append);
   }
   
   protected function _setOwnAddress($type, Mage_Sales_Model_Order_Address $address, $append=false){
	   $address->setId(null);
	   $address->setParentId($this->getOrder()->getId());
	   $address->setAddressType($type);
	   $address->save();
	   if($type==self::TYPE_POSHIPPING){
			$this->setShippingAddressId($address->getId());
			$this->getResource()->saveAttribute($this, "shipping_address_id");
	   }else{
		    $this->setBillingAddressId($address->getId());
			$this->getResource()->saveAttribute($this, "billing_address_id");
	   }
	   // Remove not used addresses
	   if(!$append){
		  $this->_cleanAddresses($type, array($address->getId()));
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
	   $this->getResource()->saveAttribute($this, "shipping_address_id");
	   $this->_cleanAddresses(self::TYPE_POSHIPPING);
	   return $this;
   }
   
   public function clearOwnBillingAddress(){
	   if($this->isBillingSameAsOrder()){
		   return $this;
	   }
	   $this->setBillingAddressId(
			$this->getOrder()->getBillingAddress()->getId()
	   );
	   $this->getResource()->saveAttribute($this, "billing_address_id");
	   $this->_cleanAddresses(self::TYPE_POBILLING);
	   return $this;
   }
   
   /**
    * @todo move to reseource
    * @param string $type
    * @param array $exclude
    */
   
   protected function _cleanAddresses($type, $exclude=array()) {
	    // Add this shippign id
		$exclude[] = $this->getShippingAddressId();
	    $addressCollection = Mage::getResourceModel("sales/order_address_collection");
		/* @var $addressCollection Mage_Sales_Model_Resource_Order_Address_Collection */
		$addressCollection->addFieldToFilter("parent_id", $this->getOrder()->getId());
		$addressCollection->addFieldToFilter("entity_id", array("nin"=>$exclude));
		$addressCollection->addFieldToFilter("address_type", $type);
		
		if($type==self::TYPE_POSHIPPING){
			$select = $addressCollection->getSelect();

			$subSelect = $select->getAdapter()->select();
			$subSelect->from( 
					array("shipment"=>$this->getResource()->getTable("sales/shipment")),
					array(new Zend_Db_Expr("COUNT(shipment.entity_id)"))
			);
			$subSelect->where("shipment.shipping_address_id=main_table.entity_id");

			$select->where("? < 1", $subSelect);
		}
		
		// Skip used addresses
		$select = $addressCollection->getSelect();

		$subSelect = $select->getAdapter()->select();
		$subSelect->from( 
				array("self"=>$this->getResource()->getMainTable()),
				array(new Zend_Db_Expr("COUNT(self.entity_id)"))
		);
		$subSelect->where("self.shipping_address_id=main_table.entity_id OR self.billing_address_id=main_table.entity_id");

		$select->where("? < 1", $subSelect);
		
		foreach($addressCollection as $toDelete){
			$toDelete->delete();
		}
   }
   
   public function needInvoice() {
	   return (int)$this->getBillingAddress()->getNeedInvoice();
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
			$this->_processTotalWeight();
			$this->_processTotalQty();
			$this->setGrandTotalInclTax($this->getSubtotalInclTax()+$this->getShippingAmountIncl());
			$this->save();
		}
		return $this;
   }
   
   protected function _processTotalWeight() {
	   $weight = 0;
	   foreach($this->getItemsCollection() as $item){
		   if(!$item->getParentItemId()){
			$weight += $item->getWeight() * $item->getQty();
		   }
	   }
	   $this->setTotalWeight($weight);
	   return $this;
   }
   
   protected function _processTotalQty() {
	   $qty = 0;
	   foreach($this->getItemsCollection() as $item){
		   if(!$item->getParentItemId()){
				$qty += $item->getQty();
		   }
	   }
	   $this->setTotalQty($qty);
	   return $this;
   }
   
   /**
    * @todo implement
    * @return bool
    */
   public function isGatewayPayment() {
	   return $this->getOrder()->getPayment()->getMethod() == Zolago_Payment_Model_Method::PAYMENT_METHOD_CODE;
   }
   
   /**
    * @return boolean
    */
   public function isPaid() {
	   if($this->isGatewayPayment()){
		   /**
		    * @todo implement logic based on transaction
		    */
		   return false;
	   }
	   return true;
   }
   
   /**
    * @return Zolago_Po_Model_Po_Status
    */
   public function getStatusModel() {
	   return Mage::getSingleton('zolagopo/po_status');
   }
   
	protected function _afterSave(){
		$ret = parent::_afterSave();
		$this->updateTotals();
		return $ret;
	} 
	
	protected function _beforeSave() {
		$this->_processAlert();
		$this->_processStatus();
		return parent::_beforeSave();
	}
	
	protected function _processAlert() {
		if(!$this->getId()){
			/**
			 * @todo implement
			 */
		}
	}
	
	protected function _processStatus() {
		if(!$this->getId()){
			Mage::getSingleton('zolagopo/po_status')->processNewStatus($this);
		}
	}
   
}
