<?php
class Zolago_Po_Model_Observer {
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
	
	public function quoteAddressSaveBefore($observer) {
		$address = $observer->getEvent()->getDataObject();
		/* @var $address Mage_Sales_Model_Quote_Address */
		if($address instanceof Mage_Sales_Model_Quote_Address){
			if($address->getAddressType()==Mage_Sales_Model_Quote_Address::TYPE_SHIPPING){
				$address->setNeedInvoice(0);
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
		
		$text = Mage::helper('zolagopo')->__("PO Split");
		
		$this->_logPoEvent($po, $text);
		$this->_logPoEvent($newPo, $text);
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
		
		$this->_logPoEvent($po, $text);
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
		
		$this->_logPoEvent($po, $text);
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
			$this->_logPoEvent($po, $text);
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
				
		$this->_logPoEvent($po, $text);
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
				
		$this->_logPoEvent($po, $text);
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
			$this->_logPoEvent($po, $text);
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
		$this->_logPoEvent($po, $text);
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
			$this->_logPoEvent($po, $text);
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
	 * @param array $fileds
	 * @param Varien_Object $object1
	 * @param Varien_Object $object2
	 * @return array
	 */
	protected function _prepareChangeLog(array $fileds, Varien_Object $object1, Varien_Object $object2) {
		$out = array();
		foreach(array_keys($fileds) as $key){
			$old = (string)$object1->getData($key);
			$new = (string)$object2->getData($key);
			if(trim($new)!=trim($old)){
				$out[] = $fileds[$key] . ": " . $old . "&rarr;" . $new; 
			}
		}
		return $out;
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @param string $comment
	 */
	protected function _logPoEvent(Unirgy_DropshipPo_Model_Po $po, $comment) {
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
}

?>
