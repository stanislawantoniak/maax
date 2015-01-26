<?php
class Zolago_Po_Model_Observer extends Zolago_Common_Model_Log_Abstract{
	// Force UDPO shippping addres, no mage order shipping address
	public function poShipmentSaveBefore($observer) {
		$shipments = $observer->getEvent()->getShipments();
		$po = $observer->getEvent()->getUdpo();
		/* @var $po Zolago_Po_Model_Po */
		foreach($shipments as $shipment){
			/* @var $shipment Mage_Sales_Model_Order_Shipment */
			if($shipment->getShippingAddressId()!=$po->getShippingAddressId()){
				$shipment->setShippingAddressId($po->getShippingAddressId());
			}
		}
	}
	
	/**
	 * Clear biling data from shipping address
	 * @param type $observer
	 */
	public function quoteAddressSaveBefore($observer) {
		$address = $observer->getEvent()->getDataObject();
		/* @var $address Mage_Sales_Model_Quote_Address */
		if($address instanceof Mage_Sales_Model_Quote_Address){
			if($address->getAddressType()==Mage_Sales_Model_Quote_Address::TYPE_SHIPPING){
				$address->setNeedInvoice(0);
				$address->setVatId(null);
			}
		}
	}	
	/**
	 * Delete aggregated based shipment-related po
	 * Clear current carrier from PO
	 * @param type $observer
	 */
	public function shipmentCancelAfter($observer) {
		$shipment = $observer->getEvent()->getData('shipment');
		if($shipment instanceof Mage_Sales_Model_Order_Shipment){
			$po = Mage::getModel("zolagopo/po")->load($shipment->getUdpoId());
			
			/* @var $po Zolago_Po_Model_Po */
			$aggregated = $po->getAggregated();
			if($aggregated->getId()){
				$aggregated->delete();
			}
			
			// Clear current carrier
			$po->setCurrentCarrier(null);
			$po->getResource()->saveAttribute($po, "current_carrier");
		}
	}
	
	/**
	 * Split PO
	 * @param type $observer
	 */
	public function poSplit($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$newPo = $observer->getEvent()->getData('new_po');
		/* @var $oldPos Zolago_Po_Model_Po */
		$itemIds = $observer->getEvent()->getData('itme_ids');
		/* @var $newPos array */
		
		$header = Mage::helper('zolagopo')->__("PO Split (#%s&rarr;#%s)", $po->getIncrementId(), $newPo->getIncrementId());
		$poInfo = Mage::helper('zolagopo')->__("Items of #%s:", $po->getIncrementId()) . 
				"\n" . $this->_getPoItemsText($po);
		$newPoInfo = Mage::helper('zolagopo')->__("Items of #%s:", $newPo->getIncrementId()) . 
				"\n" . $this->_getPoItemsText($newPo);
		
		$text = trim($header . "\n" . $poInfo . "\n" . $newPoInfo);
		
		$this->_logEvent($po, $text);
		$this->_logEvent($newPo, $text);
	}
	
	/**
	 * 
	 * @param Zolago_Po_Model_Po $po
	 * @return string
	 */
	protected function _getPoItemsText(Zolago_Po_Model_Po $po) {
		$items = array();
		foreach($po->getAllItems() as $item){
			/* @var $item Zolago_Po_Model_Po_Item */
			if(!$item->getParentItemId()){
				$items[] = $item->getOneLineDesc();
			}
		}
		return implode("\n", $items);
	}


	/**
	 * PO created directly from order (no split)
	 * @param type $observer
	 */
	public function poCreatedFromOrder($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		// Send email
		Mage::helper('udpo')->sendNewPoNotificationEmail($po);
		Mage::helper('udropship')->processQueue();
	}
	
	/**
	 * Change pos
	 * @param type $observer
	 */
	public function poChangePos($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$oldPos = $observer->getEvent()->getData('old_pos');
		/* @var $oldPos Zolago_Pos_Model_Pos */
		$newPos = $observer->getEvent()->getData('new_pos');
		/* @var $newPos Zolago_Pos_Model_Pos */
		
		$text = Mage::helper('zolagopo')->__(
				"Changed POS (%s&rarr;%s)", $oldPos->getExternalId(), $newPos->getExternalId());
		
		$this->_logEvent($po, $text);
		
		// Send email
		Mage::helper('udpo')->sendNewPoNotificationEmail($po);
		Mage::helper('udropship')->processQueue();
	}
	
	/**
	 * Status change
	 * @param Mage_Core_Model_Observer $observer
	 */
	public function poChangeStatus($observer) {
		/* @var $po Zolago_Po_Model_Po */
		$po = $observer->getEvent()->getData('po');

//        Mage::log("poChangeStatus; po_id: " . $po->getId(), null, "a.log");
		if($po instanceof Zolago_Po_Model_Po && $po->getId()){
			$oldStatus = $observer->getEvent()->getOldStatus();
			$newStatus = $observer->getEvent()->getNewStatus();

//            Mage::log("$oldStatus -> $newStatus " , null, "a.log");

			// Status changed to shipped
			if($oldStatus!=$newStatus && $newStatus==Zolago_Po_Model_Po_Status::STATUS_SHIPPED){
				// Register for use by email template block
				Mage::register('current_po', $po); 
				// Do send email
				$tracking = $po->getTracking();
				$params = array(
					"tracking" => $tracking,
					"track_url"=> $po->getTrackingUrl($tracking),
					"contact_url"=> Mage::getUrl("help/contact/vendor", array(
						"vendor"=>$po->getVendor()->getId(),
						"po"=>$po->getId()
					)),
					"_ATTACHMENTS" => Mage::helper("zolagopo")->getPoImagesAsAttachments($po)
				);
				$po->sendEmailTemplate(
					Zolago_Po_Model_Po::XML_PATH_UDROPSHIP_PURCHASE_ORDER_STATUS_CHANGED_SHIPPED,
					$params
				);
			}
		}
	}

    public function updatePoStatusByAllocation($observer){
        /* @var $po Zolago_Po_Model_Po */
        $po = $observer->getEvent()->getData('po');
        if(!$po->getId()) {
            $po = $observer->getPo();
        }

	    if($po->getId()) {
		    /** @var Zolago_Po_Model_Po_Status $statusModel */
		    $statusModel = Mage::getSingleton("zolagopo/po_status");
		    $statusModel->updateStatusByAllocation($po);

	    }
    }

    public function addAllocationComment($observer) {
        /** @var Zolago_Po_Helper_Data $hlp */
        $hlp = Mage::helper("zolagopo");

        /* @var $oldPo Zolago_Po_Model_Po */
        $oldPo = $observer->getEvent()->getData('oldPo');
        if(!$oldPo->getId()) {
            $oldPo = $observer->getPo();
        }
        /* @var $oldPo Zolago_Po_Model_Po */
        $newPo = $observer->getEvent()->getData('newPo');
        if(!$newPo->getId()) {
            $newPo = $observer->getPo();
        }

        if($newPo->getId()) {
            $hlp->addAllocationComment(
                $oldPo,
                $newPo,
                false,
                true,
                $observer->getEvent()->getData('operator_id'),
                $observer->getEvent()->getData('amount'));
        }
    }

    public function addOverpayComment($observer) {
        /** @var Zolago_Po_Helper_Data $hlp */
        $hlp = Mage::helper("zolagopo");

        /* @var $po Zolago_Po_Model_Po */
        $po = $observer->getEvent()->getData('po');
        if(!$po->getId()) {
            $po = $observer->getPo();
        }

        if($po->getId()) {
            $hlp->addOverpayComment($po, false, true, $observer->getEvent()->getData('operator_id'), $observer->getEvent()->getData('amount'));
        }
    }

	/**
	 * PO Compose	
	 * @param type $observer
	 */
	public function poCompose($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$message = $observer->getEvent()->getData('message');
		$recipient = $observer->getEvent()->getData('recipient');
		
		$text = Mage::helper('zolagopo')->__("Message send to %s: %s", $recipient, $message);
		
		$this->_logEvent($po, $text);
	}
	
	/**
	 * PO Item edit
	 * @param type $observer
	 */
	public function poItemEdit($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$oldItem = $observer->getEvent()->getData('old_item');
		/* @var $oldItem Zolago_Po_Model_Po_Item */
		$newItem = $observer->getEvent()->getData('new_item');
		/* @var $newItem Zolago_Po_Model_Po_Item */
		
		$changeLog = array();
		
		if($oldItem->getPriceInclTax()!=$newItem->getPriceInclTax()){
			$changeLog[] = Mage::helper('zolagopo')->__("Price") . ": " . 
				$po->getStore()->formatPrice($oldItem->getPriceInclTax(), false) .
				"&rarr;" . 
				$po->getStore()->formatPrice($newItem->getPriceInclTax(), false);
		}
		
		if($oldItem->getProductDiscountPrice()!=$newItem->getProductDiscountPrice()){
			$changeLog[] = Mage::helper('zolagopo')->__("Discount") . ": " . 
				$po->getStore()->formatPrice($oldItem->getProductDiscountPrice(), false) .
				"&rarr;" . 
				$po->getStore()->formatPrice($newItem->getProductDiscountPrice(), false);
		}
		
		if($oldItem->getQty()!=$newItem->getQty()){
			$changeLog[] = Mage::helper('zolagopo')->__("Qty") . ": " . 
				(int)$oldItem->getQty() . "&rarr;" . (int)$newItem->getQty();
		}
		
		if($changeLog){
			$text = Mage::helper('zolagopo')->__("Item changed %s (%s)", $newItem->getName(), implode(", ", $changeLog));
			$this->_logEvent($po, $text);
		}
	}
	
	/**
	 * PO Item Add
	 * @param type $observer
	 */
	public function poItemAdd($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$item = $observer->getEvent()->getData('item');
		/* @var $item Zolago_Po_Model_Po_Item */
		
		$text = Mage::helper('zolagopo')->__("Item added %s", $this->_getItemText($item));
				
		$this->_logEvent($po, $text);
	}
	
	/**
	 * PO Item Remove
	 * @param type $observer
	 */
	public function poItemRemove($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$item = $observer->getEvent()->getData('item');
		/* @var $item Zolago_Po_Model_Po_Item */

		$text = Mage::helper('zolagopo')->__("Item removed %s", $this->_getItemText($item, $po));
				
		$this->_logEvent($po, $text);
	}
	
	/**
	 * PO Shipping Cost
	 * @param type $observer
	 */
	public function poShippingCost($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$newPrice = $observer->getEvent()->getData('new_price');
		$oldPrice = $observer->getEvent()->getData('old_price');
		
		if((float)$newPrice!=(float)$oldPrice){
			$text = Mage::helper('zolagopo')->__(
					"Shipping cost changed (%s&rarr;%s)", 
					$po->getStore()->formatPrice($oldPrice,false),
					$po->getStore()->formatPrice($newPrice,false)
			);
			$this->_logEvent($po, $text);
		}
	}
	/**
	 * PO Address Changed
	 * @param type $observer
	 */
	public function poAddressRestore($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		$type = $observer->getEvent()->getData('type');
		if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
			$type = Mage::helper('zolagopo')->__("shipping");
		}else{
			$type = Mage::helper('zolagopo')->__("billing");
		}

		$text = Mage::helper('zolagopo')->__("Origin %s address restored", $type);
		$this->_logEvent($po, $text);
	}
	
	/**
	 * PO Address Changed
	 * @param type $observer
	 */
	public function poAddressChange($observer) {
		$po = $observer->getEvent()->getData('po');
		/* @var $po Zolago_Po_Model_Po */
		
		$newAddress = $observer->getEvent()->getData('new_address');
		/* @var $newAddress Mage_Sales_Model_Order_Address */
		$oldAddress = $observer->getEvent()->getData('old_address');
		/* @var $oldAddress Mage_Sales_Model_Order_Address */
		
		$type =  $observer->getEvent()->getData('type');
		
		$hlp = Mage::helper("zolagopo");
		
		$keysToCheck = array(
			"postcode"		=> $hlp->__("Postcode"),
			"lastname"		=> $hlp->__("Lastname"),
			"firstname"		=> $hlp->__("Firstname"),
			"street"		=> $hlp->__("Street"),
			"city"			=> $hlp->__("City"),
			"email"			=> $hlp->__("Email"),
			"telephone"		=> $hlp->__("Phone"),
			"country_id"	=> $hlp->__("Country"),
			"vat_id"		=> $hlp->__("NIP"),
			"company"		=> $hlp->__("Company"),
			"need_invoice"	=> $hlp->__("Invoice")	
		);
		
		$changeLog = $this->_prepareChangeLog($keysToCheck, $oldAddress, $newAddress);
		
		if($changeLog){
			if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
				$type = $hlp->__("Shipping");
			}else{
				$type = $hlp->__("Billing");
			}
			$text = Mage::helper('zolagopo')->__("%s address changed (%s)", $type, implode(", " , $changeLog));
			$this->_logEvent($po, $text);
		}
	}
	
	/**
	 * @param Zolago_Po_Model_Po_Item $item
	 * @return string
	 */
	protected function _getItemText(Zolago_Po_Model_Po_Item $item) {
		return $item->getOneLineDesc();
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @param string $comment
	 */
	protected function _logEvent($po, $comment) {
		$session = Mage::getSingleton('udropship/session');
		/* @var $session Zolago_Dropship_Model_Session */
		$vendor = $session->getVendor();
		$operator = $session->getOperator();
		
		if($session->isOperatorMode()){
			$fullname = $vendor->getVendorName()  . " / " . $operator->getEmail();
		}else{
			$fullname = $vendor->getVendorName();
		}
		
		$po->addComment("[" . $fullname . "] " . $comment, false, true);
		$po->saveComments();
	}
	
    //{{{ 
    /**
     * cancel orders with all po canceled
     */
    public function cronCancelOrders() {
        $order_collection = Mage::getModel('sales/order')->getCollection(); 
        $order_collection->addFieldToFilter('state',	
            array('in'=>array (
                Mage_Sales_Model_Order::STATE_NEW,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage_Sales_Model_Order::STATE_HOLDED,
                Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
                )
            )
        );
        $order_collection->addFieldToFilter("updated_at",array("lt"=>date('Y-m-d H:i:s',time()-24*3600)));
        $cancel = array();
        foreach ($order_collection as $order) {            
			$collection = Mage::getResourceModel("udpo/po_collection");
			/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
			$collection->addFieldToFilter("order_id", $order->getId());
    		$collection->addFieldToFilter("udropship_status", 
					array("nin"=>Zolago_Po_Model_Po_Status::STATUS_CANCELED)
            );
            if (!count($collection)) {
                // check date of canceled
    			$collection = Mage::getResourceModel("udpo/po_collection");
		    	/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
	    		$collection->addFieldToFilter("order_id", $order->getId());
                $collection->addFieldToFilter("updated_at",array('lt'=>date('Y-m-d H:i:s',time()-24*3600)));
                if (!count($collection)) {
                    $cancel[] = $order;
                }
            }
        }       

        foreach ($cancel as $order) {
            $order->cancel()
                ->save();
        }
    }
    //}}}
}

?>
