<?php
/**
 * @method Unirgy_DropshipPo_Model_Mysql4_Po getResource() 
 */
class Zolago_Rma_Model_Rma extends Unirgy_Rma_Model_Rma
{
	
	const TYPE_RMASHIPPING = "rmashipping";
	const TYPE_RMABILLING = "rmabilling";
	
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
		if(!$this->hasData("po")){
			$po = Mage::getModel("zolagopo/po");
			$po->load($this->getUdpoId());
			$this->setData("po", $po);
		}
		return $this->getData("po");
	}
	/**
	 * @return Mage_Sales_Model_Order_Shipment
	 */
	public function getShipment() {
		if(!$this->hasData("shipment")){
			$shipment = Mage::getModel("sales/order_shipment");
			$shipment->load($this->getShipmentId());
			$this->setData("shipment", $shipment);
		}
		return $this->getData("shipment");
	}
	

	/**
	 * @return bool
	 */
	public function isShippingSameAsPo() {
	   return $this->getShippingAddress()->getId() == $this->getPo()->getShippingAddress()->getId();
    }
   
   /**
    * @return bool
    */
   public function isBillingSameAsPo() {
	   return $this->getBillingAddress()->getId() == $this->getPo()->getBillingAddress()->getId();
   }
   
   
   /**
    * @return Mage_Sales_Model_Order_Address
    */
   public function getShippingAddress() {
	   if($this->getShippingAddressId()){
		   $address = $this->getOrder()->getAddressById($this->getShippingAddressId());
		   if($address->getId()){
			   return $address;
		   }
	   }
	   return parent::getShippingAddress();
   }
   
   /**
    * @return Mage_Sales_Model_Order_Address
    */
   public function getBillingAddress() {
	   if($this->getBillingAddressId()){
		   $address = $this->getOrder()->getAddressById($this->getBillingAddressId());
		   if($address->getId()){
			   return $address;
		   }
	   }
	   return parent::getBillingAddress();
   }
   
   
   /**
    * @param Mage_Sales_Model_Order_Address $address
    * @param bool $append
    * @return Zolago_Rma_Model_Rma
    */
   public function setOwnShippingAddress(Mage_Sales_Model_Order_Address $address, $append=false){
	    return $this->_setOwnAddress(self::TYPE_RMASHIPPING, $address, $append);
   }
   
   /**
    * @param Mage_Sales_Model_Order_Address $address
    * @param bool $append
    * @return Zolago_Rma_Model_Rma
    */
   public function setOwnBillingAddress(Mage_Sales_Model_Order_Address $address, $append=false){
	   return $this->_setOwnAddress(self::TYPE_RMABILLING, $address, $append);
   }
   
   /**
    * @param type $type
    * @param Mage_Sales_Model_Order_Address $address
    * @param type $append
    * @return Zolago_Rma_Model_Rma
    */
   protected function _setOwnAddress($type, Mage_Sales_Model_Order_Address $address, $append=false){
	   $address->setId(null);
	   $address->setParentId($this->getOrder()->getId());
	   $address->setAddressType($type);
	   $address->save();
	   if($type==self::TYPE_RMASHIPPING){
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
   
   /**
    * @return Zolago_Rma_Model_Rma
    */
   public function clearOwnShippingAddress(){
	   if($this->isShippingSameAsPo()){
		   return $this;
	   }
	   $this->setShippingAddressId(
			$this->getPo()->getShippingAddress()->getId()
	   );
	   $this->getResource()->saveAttribute($this, "shipping_address_id");
	   $this->_cleanAddresses(self::TYPE_RMASHIPPING);
	   return $this;
   }
   
   /**
    * @return Zolago_Rma_Model_Rma
    */
   public function clearOwnBillingAddress(){
	   if($this->isBillingSameAsPo()){
		   return $this;
	   }
	   $this->setBillingAddressId(
			$this->getPo()->getBillingAddress()->getId()
	   );
	   $this->getResource()->saveAttribute($this, "billing_address_id");
	   $this->_cleanAddresses(self::TYPE_RMABILLING);
	   return $this;
   }
   
   /**
    * @param string $type
    * @param array $exclude
    */
   
   protected function _cleanAddresses($type, $exclude=array()) {
	   Mage::helper('zolagopo')->clearAddresses($this->getPo(), $type, $exclude);
   }
   public function sendDhlRequest() {
       $request = Mage::getModel('zolagorma/rma_request');
       $request->prepareRequest($this);
   }
}
