<?php

/**
 * Class Zolago_Po_Model_Po
 * @method string getMaxShippingDate()
 * @method string getGrandTotalInclTax()
 * @method string getIncrementId()
 */
class Zolago_Po_Model_Po extends Unirgy_DropshipPo_Model_Po
{
	const TYPE_POSHIPPING = "poshipping";
	const TYPE_POBILLING = "pobilling";
	
	/**
	 * Email template for new status
	 */
	const XML_PATH_UDROPSHIP_PURCHASE_ORDER_STATUS_CHANGED_SHIPPED = 
			"udropship/purchase_order/status_changed_shipped";
	
	/**
	 * Email sender
	 */
    const XML_PATH_EMAIL_IDENTITY = 'sales_email/order/identity';
	
	/**
	 * @return array
	 */
	public function getAllowedOperators() {
		if(!$this->hasData("allowed_operators")){
			$opreators = array();
			// Must have post
			if($this->getPos() instanceof Zolago_Pos_Model_Pos){
				$collection = Mage::getResourceModel("zolagooperator/operator_collection");
				/* @var $collection Zolago_Operator_Model_Resource_Operator_Collection */
				$collection->addVendorFilter($this->getVendor());
				$collection->addActiveFilter();
				$collection->walk("afterLoad");
				foreach($collection as $operator){
					if($this->isAllowed(null, $operator)){
						$opreators[] = $operator;
					}
				}
			}
			$this->setData("allowed_operators", $opreators);
		}
		return $this->getData("allowed_operators");
	}
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor $venndor
	 * @param Zolago_Operator_Model_Operator $operator
	 * @return boolean
	 */
	public function isAllowed(Unirgy_Dropship_Model_Vendor $vendor = null, 
		Zolago_Operator_Model_Operator $operator = null) {
		
		if($operator instanceof Zolago_Operator_Model_Operator){
			return in_array($this->getDefaultPosId(), $operator->getAllowedPos()) && 
				$operator->hasRole(Zolago_Operator_Model_Acl::ROLE_ORDER_OPERATOR);
		}elseif($vendor instanceof Zolago_Dropship_Model_Vendor){
			return in_array($this->getDefaultPosId(), $vendor->getAllowedPos());
		}
		return false;
	}
	
	/**
	 * @param Mage_Sales_Model_Order_Shipment $shipment | null
	 * @return Mage_Sales_Model_Order_Shipment_Track | null
	 */
	public function getTracking(Mage_Sales_Model_Order_Shipment $shipment = null) {
		if(!$shipment instanceof  Mage_Sales_Model_Order_Shipment){
			$shipment = $this->getLastNotCanceledShipment();
		}
		if($shipment instanceof  Mage_Sales_Model_Order_Shipment && $shipment->getId()){
			$collection = $shipment->getTracksCollection()->setOrder("created_at", "DESC");
			return $collection->getFirstItem();
		}
		return null;
	}
	
	/**
	 * @param Mage_Sales_Model_Order_Shipment_Track $tracking
	 * @return string
	 */
	public function getTrackingUrl(Mage_Sales_Model_Order_Shipment_Track $tracking=null) {
		$carrier = $this->getCarrier();
		if(!$tracking instanceof Mage_Sales_Model_Order_Shipment_Track){
			$tracking = $this->getTracking();
		}
		
		if($carrier && $tracking){
			$out = $carrier->getConfigData('tracking_url');
			return sprintf($out, $tracking->getTrackNumber());
		}
		return null;
	}
	
	/**
	 * @param string $template
	 * @param array $templateParams
	 * @return Zolago_Po_Model_Po
	 */
	public function sendEmailTemplate($template, $templateParams = array())
    {
		// Reciver data
		$storeId = $this->getOrder()->getStoreId();
		$email = $this->getOrder()->getCustomerEmail();
		$name = $this->getOrder()->getCustomerName();
		
		$templateParams['po'] = $this;
		$templateParams['order'] = $this->getOrder();
		$templateParams['vendor'] = $this->getVendor();
		$templateParams['carrier'] = $this->getCarrier();
	    $templateParams['use_attachments'] = true;

        $mailer = Mage::getModel('zolagocommon/core_email_template_mailer');
        /* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */
		$emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($email, $name);
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId(Mage::getStoreConfig($template, $storeId));
        $mailer->setTemplateParams($templateParams);
        $mailer->send();
        
		return $this;
    }
	
	/**
	 * @return Mage_Sales_Model_Order_Shipment | null
	 */
	public function getLastNotCanceledShipment() {
		
		$collection = $this->getShipmentsCollection();
		/* @var $collection Mage_Sales_Model_Resource_Order_Shipment_Collection */
		$collection->clear();
		
		$collection->addAttributeToFilter("udropship_status", 
			array("nin"=>array(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED))
		);
		$collection->setOrder("created_at", "DESC");
		
		$item = $collection->getFirstItem();
		
		if($item && $item->getId()){
			return $item;
		}
		
		return null;
	}
	
	/**
	 * It can be returned when order has been delivered not later than the bigges number for allowed_days
	 * 
	 * @return bloolean
	 */
	public function canBeReturned(){
		
		$vendor = $this->getVendor();
		$reason_vendor = Mage::getModel('zolagorma/rma_reason_vendor')->getCollection()
																	  ->addFieldToFilter('vendor_id', $vendor->getId())
																	  ->setOrder('allowed_days', 'desc')
																	  ->getFirstItem();
		
		if(!$reason_vendor){
			return false;
		}
		
		$max_allowed_days = (int) $reason_vendor->getAllowedDays();
		
		$days_elapsed = Mage::helper('zolagorma')->getDaysElapsed($reason_vendor->getReturnReasonId(), $this);
		
		return ($days_elapsed < $max_allowed_days) ? true : false;
	}
	
	/**
	 * @return bool
	 */
	public function isFinished() {
		$status = $this->getStatusModel();
		return in_array($this->getUdropshipStatus(), $status::getFinishStatuses());
	}
	
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
						$childItem->setId(null); // force add item to colelciton in po model
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
			$newModel->setBaseShippingTax(0);
			$newModel->setShippingTax(0);
			$newModel->setBaseShippingAmountIncl(0);
			$newModel->setShippingAmountIncl(0);
				
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
			
			$newModel->setSkipCheckSameEmail(true);
			$this->setSkipCheckSameEmail(true);
			
			$newModel->updateTotals(true);
			$this->updateTotals(true);
			
			$transaction->commit();
		} catch (Exception $ex) {
			$transaction->rollBack();
			throw $ex;
		}
		
		Mage::dispatchEvent("zolagopo_po_split", array(
			"po"		=> $this, 
			"new_po"	=> $newModel, 
			"item_ids"	=> $itemIds
		));
					
		return $newModel;
	}
	
	/**
	 * @return Zolago_Po_Model_Aggregated
	 */
	public function getAggregated() {
		if(!$this->hasData("aggregated")){
			$aggregated = Mage::getModel("zolagopo/aggregated");
			$aggregated->load($this->getAggregatedId());
			$this->setData("aggregated", $aggregated);
		}
		return $this->getData("aggregated");
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
	
	/**
	 * @return Mage_Shipping_Model_Carrier_Abstract
	 */
	public function getCarrier() {
		if(!$this->hasData("carrier")){
			$config = Mage::getSingleton("shipping/config");
			/* @var $config Mage_Shipping_Model_Config */
			$this->setData("carrier", $config->getCarrierInstance(
				$this->getCurrentCarrier(), 
				$this->getStore()->getId()
			));
		}
		return $this->getData("carrier");
	}
	
	/**
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getPos() {
		if(!$this->hasData("pos")){
			$this->setData("pos", Mage::getModel("zolagopos/pos")->load($this->getDefaultPosId()));
		}
		return $this->getData("pos");
	}
	
	public function getSubtotalInclTax() {
		$total = 0;
		foreach($this->getAllItems() as $item){
			$total += $this->calcuateItemPrice($item) * $item->getQty() - $item->getDiscountAmount();
		}
		return $total;
	}
	
	public function getSubtotalDiscount() {
		$discount = 0;
		foreach($this->getAllItems() as $item){
			$discount += $item->getDiscountAmount();
		}
		return $discount;
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
    * @param string $type
    * @param array $exclude
    */
   
   protected function _cleanAddresses($type, $exclude=array()) {
	   Mage::helper('zolagopo')->clearAddresses($this, $type, $exclude);
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
   
   /**
    * @return string
    */
   public function getFormattedGrandTotalInclTax() {
	   return Mage::app()->getLocale()->currency(
			$this->getStore()->getCurrentCurrencyCode()
		)->toCurrency($this->getGrandTotalInclTax());
   }
   
   /**
    * @param bool $force
    * @return Zolago_Po_Model_Po
    */
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
	   return $this->getOrder()->isGatewayPayment();
   }

   /**
    * @return bool
    */
   public function isPaymentCheckOnDelivery() {
       return $this->getOrder()->isPaymentCheckOnDelivery();
   }

    /**
     * return true if payment method is Banktransfer
     * if not return false
     *
     * @return bool
     */
    public function isPaymentBanktransfer() {
       return $this->getOrder()->isPaymentBanktransfer();
    }

    /**
     * return true if payment method is dotpay
     * if nor return false
     *
     * @return bool
     */
    public function isPaymentDotpay() {
       return $this->getOrder()->isPaymentDotpay();
    }

    public function isCC() {
        return $this->getOrder()->isCC();
    }

    public function isGateway() {
        return $this->getOrder()->isGateway();
    }
   
   /**
    * @see isPaymentCheckOnDelivery()
    * @return bool
    */
   public function isCod() {
	   return $this->isPaymentCheckOnDelivery();
   }
   
   /**
    * @return boolean
    */
   public function isPaid() {
	   if(!$this->isCod()){
		   return $this->getPaymentAmount() >= $this->getGrandTotalInclTax() ? true : false;
	   }
	   return true;
   }

	public function getPaymentAmount() {
		/** @var Zolago_Payment_Model_Allocation $allocationModel */
		$allocationModel = Mage::getModel("zolagopayment/allocation");
		return $allocationModel->getSumOfAllocations($this->getId()); //sum of allocations amount
	}

	public function getDebtAmount() {
		return -($this->getGrandTotalInclTax() - $this->getPaymentAmount());
	}

	public function getCurrencyFormattedAmount($amount) {
		return Mage::helper('core')->currency(
			$amount,
			true,
			false
		);
	}
   
   /**
    * @return Zolago_Po_Model_Po_Status
    */
   public function getStatusModel() {
	   return Mage::getSingleton('zolagopo/po_status');
   }
   
   /**
    * @return Zolago_Po_Model_Resource_Po_Collection
    */
   public function getSameEmailPoCollection() {
	   $collection = $this->getCollection();
	   
	   $statModel = Mage::getSingleton("zolagopo/po_status");
	   $finishedStatuses = $statModel::getFinishStatuses();
	   
	   if(in_array($this->getUdropshipStatus(), $finishedStatuses)){
		   $collection->addFieldToFilter("entity_id", -1); // Emtpy
		   return $collection;
	   }
	   
	   /* @var $collection Zolago_Po_Model_Resource_Po_Collection */
	   if($this->getId()){
			$collection->addFieldToFilter("entity_id", array("neq"=>$this->getId()));
	   }
	   $collection->addFieldToFilter("udropship_vendor", $this->getUdropshipVendor());
	   $collection->addFieldToFilter("udropship_status", array("nin"=>$finishedStatuses));
	   $collection->addFieldToFilter("customer_email", $this->getCustomerEmail());

	   return $collection;
   }
   
	protected function _afterSave(){
		$ret = parent::_afterSave();
		$this->updateTotals();
		return $ret;
	} 
	
	protected function _beforeSave() {
		// Transfer fields
		if((!$this->getId() || $this->isObjectNew()) && !$this->getSkipTransferOrderItemsData()){
			$this->setCustomerEmail($this->getOrder()->getCustomerEmail());
		}

		//unset customer_id if order is made by guest
		if((!$this->getId() || $this->isObjectNew()) && $this->getOrder()->getCustomerIsGuest()) {
			$this->setCustomerId(null);
		}
		
		$this->_processAlert();
		$this->_processStatus();

		$this->_processMaxShippingDate();
		
		return parent::_beforeSave();
	}
	
	protected function _processAlert() {
		if(!$this->getId()){
			$alertBit = 0;
			
			if(!$this->getSkipCheckSameEmail()){
				$sameEmail = $this->getSameEmailPoCollection();
				if($sameEmail->count()){
					$alertBit += Zolago_Po_Model_Po_Alert::ALERT_SAME_EMAIL_PO;
					foreach($sameEmail as $po){
						$oldAlert = (int)$po->getAlert();
						$oldAlert|=Zolago_Po_Model_Po_Alert::ALERT_SAME_EMAIL_PO;
						$po->setAlert($oldAlert);
						$po->getResource()->saveAttribute($po, "alert");
					}
				}
				$this->setAlert($alertBit);
			}
		}
	}


	
	protected function _processStatus() {
		if(!$this->getId()){
			Mage::getSingleton('zolagopo/po_status')->processNewStatus($this);
		}
	}
	
	protected function _processMaxShippingDate() {
		if(!$this->getId()){
			if ($max_shipping_date = Mage::helper('zolagoholidays/datecalculator')->calculateMaxPoShippingDate($this, true)) {
				$this->setMaxShippingDate($max_shipping_date->toString('YYYY-MM-dd'));
			}
		}
		
		
	}

	public function save() {
		$new = $this->isObjectNew();
		$return = parent::save();
		if ($new) {
    		Mage::dispatchEvent("zolagopo_po_save_new",array('po'=>$this));
		}
		Mage::dispatchEvent("zolagopo_po_save_after",array('po'=>$this));
		return $return;
	}

    public function getVendorCommentsCollection($reload=false)
    {
        if (is_null($this->_vendorComments) || $reload) {
            $this->_vendorComments = Mage::getResourceModel('udpo/po_comment_collection')
                ->setPoFilter($this->getId())
                ->addFieldToFilter('is_visible_to_vendor', 1)
                ->setCreatedAtOrder()
                ->setOrder('entity_id', 'desc');

            /**
             * When shipment created with adding comment, comments collection must be loaded before we added this comment.
             */
            $this->_vendorComments->load();

            if ($this->getId()) {
                foreach ($this->_vendorComments as $comment) {
                    $comment->setPo($this);
                }
            }
        }
        return $this->_vendorComments;
    }
    
    /**
     * set shipped data into tracking
     * @return 
     */
     public function setShipped() {
         $track = $this->getTracking();
         if (!$track->getShippedDate()) {
             $track->setShippedDate(Varien_Date::now());
             $track->save();
         }
     }

    /**
     * Replace customer email with new email
     *
     * @param $newEmail
     * @param $customerId
     * @param $storeId
     */
    public function replaceEmailInPOs($newEmail, $customerId, $storeId)
    {
        if (empty($customerId)) {
            return;
        }
        $sameEmailCollection = $this->getCollection();

        $sameEmailCollection->addFieldToFilter("customer_id", $customerId);
        $sameEmailCollection->addFieldToFilter("store_id", $storeId);
        if ($sameEmailCollection->count()) {
            foreach ($sameEmailCollection as $po) {
                $po->setCustomerEmail($newEmail);
                $po->getResource()->saveAttribute($po, "customer_email");
            }
        }
    }

    public function addItemWithTierCommission(Unirgy_DropshipPo_Model_Po_Item $item)
    {
        $this->addItem($item);
        if(Mage::helper("core")->isModuleEnabled('Unirgy_DropshipTierCommission')) {
            Mage::helper("udtiercom")->processPo($this);
        }
        return $this;
    }

    /**
     * @param $ids int|array
     * @param $vendor Zolago_Dropship_Model_Vendor
     * @return array
     */
    public function ghapiGetOrdersByIncrementIds($ids, $vendor) {

        if (is_numeric($ids)) $ids = array($ids);
        if (!is_array($ids)) return array();
        if (!$vendor->getId()) return array();

        /** @var Zolago_Po_Model_Resource_Po_Collection $coll */
        $coll = Mage::getResourceModel('zolagopo/po_collection');
        $coll->addFieldToFilter('udropship_vendor', $vendor->getId());
        $coll->addFieldToFilter('increment_id', $ids);
        $coll->addPosData("external_id");

        $list = array();
        $i = 0;
        foreach ($coll as $po) {
            /** @var Zolago_Po_Model_Po $po */
            $list[$i]['vendor_id']                = $vendor->getId();
            $list[$i]['vendor_name']              = $vendor->getVendorName();
            $list[$i]['order_id']                 = $po->getIncrementId();
            $list[$i]['order_date']               = $po->getCreatedAt();
            $list[$i]['order_max_shipping_date']  = $po->getMaxShippingDate();
            $list[$i]['order_status']             = $this->getStatusModel()->ghapiOrderStatus($po->getUdropshipStatus());
            $list[$i]['order_total']              = $po->getGrandTotalInclTax();
            $list[$i]['payment_method']           = $po->ghapiPaymentMethod();
            $list[$i]['order_due_amount']         = abs($po->getDebtAmount());
            $list[$i]['delivery_method']          = 'standard_courier'; // todo when inpost added
            $list[$i]['shipment_tracking_number'] = $po->getShipmentTrackingNumber();
            $list[$i]['pos_id']                   = $po->getExternalId();
            $list[$i]['order_currency']           = $po->getStore()->getCurrentCurrencyCode();

            $list[$i]['invoice_data']['invoice_required'] = $po->needInvoice();
            if ($list[$i]['invoice_data']['invoice_required']) {
                /** @var Zolago_Sales_Model_Order_Address $ba */
                $ba = $po->getBillingAddress();
                $list[$i]['invoice_data']['invoice_address']['invoice_first_name']   = $ba->getFirstname();
                $list[$i]['invoice_data']['invoice_address']['invoice_last_name']    = $ba->getLastname();
                $list[$i]['invoice_data']['invoice_address']['invoice_company_name'] = $ba->getCompany();
                $list[$i]['invoice_data']['invoice_address']['invoice_street']       = $ba->getStreet()[0];
                $list[$i]['invoice_data']['invoice_address']['invoice_city']         = $ba->getCity();
                $list[$i]['invoice_data']['invoice_address']['invoice_zip_code']     = $ba->getPostcode();
                $list[$i]['invoice_data']['invoice_address']['invoice_country']      = $ba->getCountryId();
                $list[$i]['invoice_data']['invoice_address']['invoice_tax_id']       = $ba->getVatId();
//                $list[$i]['invoice_data']['invoice_address']['phone']                = $ba->getTelephone(); // No telephone?
            }

            $list[$i]['delivery_data']['inpost_locker_id'] = ''; // todo when inpost added
            $sa = $po->getShippingAddress();
            $list[$i]['delivery_data']['delivery_address']['delivery_first_name']   = $sa->getFirstname();
            $list[$i]['delivery_data']['delivery_address']['delivery_last_name']    = $sa->getLastname();
            $list[$i]['delivery_data']['delivery_address']['delivery_company_name'] = $sa->getCompany();
            $list[$i]['delivery_data']['delivery_address']['delivery_street']       = $sa->getStreet()[0];
            $list[$i]['delivery_data']['delivery_address']['delivery_city']         = $sa->getCity();
            $list[$i]['delivery_data']['delivery_address']['delivery_zip_code']     = $sa->getPostcode();
            $list[$i]['delivery_data']['delivery_address']['delivery_country']      = $sa->getCountryId();
            $list[$i]['delivery_data']['delivery_address']['phone']                 = $sa->getTelephone();

            $j = 0;
            foreach ($po->getItemsCollection() as $item) {
                /** @var Zolago_Po_Model_Po_Item $item */
                if (!$item->isDeleted() && !$item->getParentItemId()) {
                    $list[$i]['order_items'][$j]['is_delivery_item']           = 0;
                    $list[$i]['order_items'][$j]['item_sku']                   = $item->getFinalSku();
                    $list[$i]['order_items'][$j]['item_name']                  = $item->getName();
                    $list[$i]['order_items'][$j]['item_qty']                   = $item->getQty();
                    $list[$i]['order_items'][$j]['item_value_before_discount'] = $item->getPriceInclTax() * $item->getQty();
                    $list[$i]['order_items'][$j]['item_discount']              = $item->getDiscount() * $item->getQty();
                    $list[$i]['order_items'][$j]['item_value_after_discount']  = ($item->getPriceInclTax() - $item->getDiscount()) * $item->getQty();
                    $j++;
                }
            }
            // Shipping cost
            $list[$i]['order_items'][$j]['is_delivery_item']           = 1;
            $list[$i]['order_items'][$j]['item_sku']                   = '';
            $list[$i]['order_items'][$j]['item_name']                  = Mage::helper('ghapi')->__('Delivery and package');
            $list[$i]['order_items'][$j]['item_qty']                   = 1;
            $list[$i]['order_items'][$j]['item_value_before_discount'] = $po->getBaseShippingAmountIncl();
            $list[$i]['order_items'][$j]['item_discount']              = $po->getShippingDiscountIncl();
            $list[$i]['order_items'][$j]['item_value_after_discount']  = $po->getShippingAmountIncl();
            $i++;
        }

        return $list;
    }

    /**
     * Get payment_method for GH API
     *
     * @return string
     */
    public function ghapiPaymentMethod() {
        if ($this->isCC()) {
            return 'credit_card';
        }
        if ($this->isGateway()) {
            return 'online_bank_transfer';
        }
        if ($this->isPaymentBanktransfer()) {
            return 'bank_transfer';
        }
        if ($this->isCod()) {
            return 'cash_on_delivery';
        }
        return '';
    }

    /**
     * Return collection of PO for given Vendor
     * and array of ids (increment_id)
     *
     * @param $ids
     * @param $vendor
     * @return Zolago_Po_Model_Resource_Po_Collection
     */
    public function getVendorPoCollectionByIncrementId($ids, $vendor) {
        /** @var Zolago_Po_Model_Resource_Po_Collection $coll */
        $coll = Mage::getResourceModel('zolagopo/po_collection');
        if ($vendor->getId()) {
            $coll->addFieldToFilter('udropship_vendor', $vendor->getId());
        }
        $coll->addFieldToFilter('increment_id', array("in" => $ids));
        return $coll;
    }

    /**
     * Get track number
     *
     * @return mixed
     */
    public function getShipmentTrackingNumber() {
        $shipment = $this->getLastNotCanceledShipment();
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            /** @var Mage_Sales_Model_Order_Shipment_Track $item */
            $item = $shipment->getTracksCollection()->setOrder("created_at", "DESC")->getFirstItem();
            return $item->getTrackNumber();
        } else {
            return '';
        }
    }



	const GH_API_RESERVATION_STATUS_OK = 'ok';
	const GH_API_RESERVATION_STATUS_PROBLEM = 'problem';

	/**
	 * GH Api method to set reservation flag on PO object
	 * @param string $reservationStatus
	 * @param string|bool $reservationMessage
	 * @return $this
	 */
	public function ghApiSetOrderReservation($reservationStatus,$reservationMessage=false) {
		$reservationStatus = trim(strtolower($reservationStatus));
		$save = false;
		$alert = $this->getAlert();
		switch($reservationStatus) {

			case self::GH_API_RESERVATION_STATUS_OK:
				$this->setReservation(0);
				$alert &= ~ Zolago_Po_Model_Po_Alert::ALERT_GH_API_RESERVATION_PROBLEM;
				$save = true;
				break;

			case self::GH_API_RESERVATION_STATUS_PROBLEM:
				$statusModel = $this->getStatusModel();

				if(!$reservationMessage) {
					$this->throwWrongReservationMessageError();
				}

				if(!$statusModel->isManulaStatusAvailable($this) ||
					!in_array($statusModel::STATUS_ONHOLD, array_keys($statusModel->getAvailableStatuses($this)))) {
					$this->throwCannotChangePoStatusError();
				} else {
					$statusModel->changeStatus($this, $statusModel::STATUS_ONHOLD);
					$alert |= Zolago_Po_Model_Po_Alert::ALERT_GH_API_RESERVATION_PROBLEM;
					$save = true;
				}
				break;

			default:
				$this->throwWrongReservationStatusError();
		}
		if($reservationMessage) {
			$this->addComment($reservationMessage,false,true);
		}
		if($save) {
			$this->setAlert($alert);
			$this->save();
		}
		return $this;
	}

	/**
	 * GH Api method to set reservation flag on multiple pos that has been confirmed as read
	 * @param array $posIds
	 * @return $this
	 */
	public function ghApiSetOrdersReservationAfterRead($posIds) {
		foreach($posIds as $poId) {
			$this
				->loadByIncrementId($poId)
				->setReservation(0)
				->save();
		}
		$this->unsetData();
		return $this;
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwWrongReservationStatusError() {
		Mage::throwException('error_reservation_status_invalid');
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwCannotChangePoStatusError() {
		Mage::throwException('error_order_status_change');
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwWrongReservationMessageError() {
		Mage::throwException('error_reservation_message_invalid');
	}


    /**
     * @param Zolago_Po_Model_Po $po
     * @return bool
     */
    public function setOrderState(Zolago_Po_Model_Po $po)
    {
        $order = $po->getOrder();
        $orderId = $order->getId();
        Mage::log('$orderId ' . $orderId, null, 'rma.log');

        $orderPos = Mage::getModel('udpo/po')
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId);

        $orderStatusChange = array();

        $completePos = array(
            Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED,
            Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED,
            Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED
        );
        $cancelPos = array(
            Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED
        );
        $poStatuses = array();
        if ($orderPos->getSize() > 0) {
            foreach ($orderPos as $orderPo) {
                $poStatuses[] = (int)$orderPo->getUdropshipStatus();
            }
        }

        $diffCompleteStatuses = array_diff($poStatuses, $completePos);

        $diffCancelStatuses = array_diff($poStatuses, $cancelPos);


        if (empty($diffCompleteStatuses)) {
            $orderStatusChange['state'] = Mage_Sales_Model_Order::STATE_COMPLETE;
            $orderStatusChange['udropship_status'] = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;
        }
        if (empty($diffCancelStatuses)) {
            $orderStatusChange['state'] = Mage_Sales_Model_Order::STATE_CANCELED;
            $orderStatusChange['udropship_status'] = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
        }

        //Mage::log($orderStatusChange, null, 'order.log');
        if (!empty($orderStatusChange)) {
            $order->setData('state', $orderStatusChange['state']);
            $order->setStatus($orderStatusChange['state'])
                ->setUdropshipStatus($orderStatusChange['udropship_status']);
            try {
                $order->save();
            } catch (Exception $e) {
                Mage::logException($e);
                return false;
            }
        }

    }
}
