<?php
/**
 * @method Unirgy_DropshipPo_Model_Mysql4_Po getResource() 
 */
class Zolago_Rma_Model_Rma extends Unirgy_Rma_Model_Rma
{
	
    const RMA_TYPE_STANDARD = '1';
    const RMA_TYPE_RETURN = '2';

	const TYPE_RMASHIPPING = "rmashipping";
	const TYPE_RMABILLING = "rmabilling";
	
	const FLOW_INSTANT = 1;
	const FLOW_ACKNOWLEDGED = 2;
	
	/**
	 * @return boolean
	 * @todo implement
	 */
	public function getIsClaim(){
		return false;
	}
	
	/**
	 * @return boolean
	 * @todo implement
	 */
	public function getIsReturn(){
		return true;
	}
	
	/**
	 * @return string
	 */
	public function getRmaStatusName() {
		return $this->getStatusObject()->getTitle();
	}

	/**
	 * @return string
	 */
	public function getRmaStatusCode() {
		return $this->getStatusObject()->getCode();
	}

	/**
	 * @return Varien_Object
	 */
	public function getStatusObject() {
		return $this->getStatusModel()->getStatusObject($this);
	}
	
	/**
	 * @return bool
	 */
	public function hasCustomerTracking() {
		foreach($this->getTracksCollection() as $track){
			if($track->getTrackCreator()==Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_CUSTOMER){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @return Zolago_Rma_Model_Resource_Rma_Track_Collection
	 */
	public function getVendorTracksCollection() {
		return Mage::getResourceModel('urma/rma_track_collection')
                ->setRmaFilter($this->getId())
                ->addVendorFilter();
	}
	
	/**
	 * @return Zolago_Rma_Model_Resource_Rma_Track_Collection
	 */
	public function getCustomerTracksCollection() {
		return Mage::getResourceModel('urma/rma_track_collection')
                ->setRmaFilter($this->getId())
                ->addCustomerFilter();
	}
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
	 * @return Zolago_Rma_Model_Rma_Status
	 */
	public function getStatusModel() {
		return Mage::getSingleton('zolagorma/rma_status');
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
   public function getFormattedAddressForVendor() {
       $data = $this->getData();
       $addressId = $data['shipping_address_id'];
       $address = Mage::getModel('sales/order_address')->load($addressId)->getData();
       $out = array (
           'name' 		=> (empty($address['company']))? ($address['firstname'].' '.$address['lastname']):$address['company'],
           'city' 		=> $address['city'],
           'postcode' 	=> $address['postcode'],           
           'street' 	=> $address['street'],
           'personName' => $address['firstname'].' '.$address['lastname'],
           'phone' 		=>$address['telephone'],
           'email' 		=> $this->getOrder()->getCustomerEmail(),
           'country'	=> $address['country_id'],
       );
       return $out;
   }
   public function getFormattedAddressForCustomer() {
       $data = $this->getData();
       $addressId = $data['customer_address_id'];
       $address = Mage::getModel('customer/address')->load($addressId)->getData();
       $out = array (
           'name' 		=> (empty($address['company']))? ($address['firstname'].' '.$address['lastname']):$address['company'],
           'city' 		=> $address['city'],
           'postcode' 	=> $address['postcode'],           
           'street' 	=> $address['street'],
           'personName' => $address['firstname'].' '.$address['lastname'],
           'phone' 		=>$address['telephone'],
           'email' 		=> $this->getOrder()->getCustomerEmail(),
           'country'	=> $address['country_id'],
       );
       return $out;
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
   
   /**
    * @param array $dhlParams
    * @return type
    */
   public function sendDhlRequest($dhlParams = array()) {
       $request = Mage::getModel('zolagorma/rma_request');
       foreach ($dhlParams as $key=>$val) {
           $request->setParam($key,$val);
       }
       $return = $request->prepareRequest($this);
       return $return;
   }
   
   /**
    * @return flaot
    */
   public function getTotalValue() {
       $collection = $this->getItemsCollection();
       $price = 0;
       foreach ($collection as $item) {
         $price += $item->getPrice();
       }
       return $price;
   }
  
   /**
    * @return Zolago_Rma_Model_Rma
    */
   protected function _beforeSave() {
	   if(!$this->getId()){
			$this->getStatusModel()->processNewRmaStatus($this);
			$this->setIsNewFlag(true);
	   }
	   return parent::_beforeSave();
   }
    /**
     * generated pdf for customer
     * @return string
     */
     public function getRmaPdf() {
         $pdf = Mage::getModel('zolagorma/pdf');
         return $pdf->getPdfFile($this->getId());
     }

    /**
     * static pdf for customer
     * @return string
     */
     public function getCustomerPdf() {
         $helper = Mage::helper('zolagorma');
         return $helper->getStaticCustomerPdf();
     }
     
    /**
     * return rma type name
     * @param int $id
     * @return string
     */
    public function getRmaTypeName() {
        $id = $this->getRmaType();
        $model = Mage::getModel('zolagorma/system_source_type');
        $val = $model->getTypeById($id);
        if ($val) {
            $helper = Mage::helper('zolagorma');
            return $helper->__($val);
        }
        return '';
    }

	/**
	 * @return float
	 */
	public function getRmaRefundAmount() {
		if(!isset($this->_refundAmount)) {
			$_items = $this->getAllItems();
			$amount = 0;
			foreach ($_items as $item) {
				$amount += $item->getReturnedValue();
			}
			$this->_refundAmount = $amount;
		}
		return $this->_refundAmount;
	}


	public function getRmaRefundAmountMax() {
		if(!isset($this->_refundAmountMax)) {
			$_items = $this->getAllItems();
			$amount = 0;
			foreach($_items as $item) {
				$amount += $item->getPoItem()->getFinalItemPrice();
			}
			$this->_refundAmountMax = $amount;
		}
		return $this->_refundAmountMax;
	}

	/**
	 * @param int $poId
	 * @return Zolago_Rma_Model_Resource_Rma_Collection
	 */
	public function loadByPoId($poId) {
		$ids = $this->getCollection()
			->addAttributeToFilter('udpo_id', $poId);

		return $ids;
	}
}
