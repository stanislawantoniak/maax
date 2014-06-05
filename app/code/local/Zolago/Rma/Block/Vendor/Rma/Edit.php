<?php
class Zolago_Rma_Block_Vendor_Rma_Edit extends Mage_Core_Block_Template {
	
	
	
	public function getAvailableStatuses(){
		
	}
	
	/**
	 * @param Mage_Sales_Model_Order_Shipment $shipment
	 * @return Mage_Sales_Model_Order_Shipment_Track|null
	 */
	public function getTracking($shipment = null) {
		if($shipment instanceof Mage_Sales_Model_Order_Shipment){
			$collection = $shipment->getTracksCollection();
			/* @var $collection Mage_Sales_Model_Resource_Order_Shipment_Track_Collection */
			$collection->setOrder("created_at", "DESC");
			$item=$collection->getFirstItem();
			if($item->getId()){
				return $item;
			}
		}
		return null;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return text
	 */
	public function getPaymentText(Mage_Sales_Model_Order $order) {
		$payment =  $order->getPayment();
		$text = $this->__("N/A");
		if($payment instanceof Mage_Sales_Model_Order_Payment){
			$text = $payment->getMethodInstance()->getConfigData('title');
		}
		return $this->escapeHtml($text);
	}
	
	/**
	 * @return Zolago_Rma_Model_Rma
	 */
	public function getModel() {
		if(!Mage::registry("current_rma")){
			 Mage::register("current_rma", Mage::getModel("zolagorma/rma"));
			 
		}
		return Mage::registry("current_rma");
	}
	
	/**
	 * alias
	 * @return Zolago_Rma_Model_Rma
	 */
	public function getRma() {
		return $this->getModel();
	}
	
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
		return $this->getRma()->getPo();
	}


	public function getPoUrl($action, $params=array()) {
		$params += array(
			"id"=> $this->getPo()->getId(),
			"form_key" => Mage::getSingleton('core/session')->getFormKey()
		);
		return $this->getUrl("udpo/vendor/$action", $params);
	}
	
	public function getRmaUrl($action, $params=array()) {
		$params += array(
			"id"=> $this->getRma()->getId(),
			"form_key" => Mage::getSingleton('core/session')->getFormKey()
		);
		return $this->getUrl("*/*/$action", $params);
	}
	
}

