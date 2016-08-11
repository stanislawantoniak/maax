<?php
class Zolago_Rma_Block_Vendor_Rma_Edit extends Mage_Core_Block_Template {
	
	/**
	 * @param Varien_Object $tracking
	 * @param Zolago_Po_Model_Po $po
	 * @return null
	 */
	public function getPoLetterUrl(Varien_Object $tracking, Zolago_Po_Model_Po $po) {
		if($this->isLetterable($tracking)){
			return $this->getUrl('orbashipping/dhl/lp', array(
					'trackId'		=> $tracking->getId(), 
					'trackNumber'	=> $tracking->getNumber(), 
					'vId'			=> $po->getVendor()->getId(), 
					'posId'			=> $po->getDefaultPosId(),
					'udpoId'		=> $po->getId(), 
					'_secure'		=>true
				));
		}
		return null;
	}
	
	/**
	 * @param Varien_Object $tracking
	 * @return boolean
	 */
	public function isLetterable(Varien_Object $tracking) {
		switch ($tracking->getCarrierCode()) {
			case Orba_Shipping_Model_Carrier_Dhl::CODE:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Mage_Sales_Model_Order_Shipment $shipment
	 * @return Mage_Sales_Model_Order_Shipment_Track|null
	 */
	public function getPoTracking(Mage_Sales_Model_Order_Shipment $shipment = null) {
		if($shipment instanceof Mage_Sales_Model_Order_Shipment){
			return $shipment->getTracksCollection()->getFirstItem();
		}
		return null;
	}
	
	/**
	 * @return Mage_Sales_Model_Order_Shipment | null
	 */
	public function getPoShipment(Zolago_Po_Model_Po $po) {
		return $po->getLastNotCanceledShipment();
	}
	
	/**
	 * @return Zolago_Rma_Model_Rma_Status
	 */
	public function getStatusModel() {
		return $this->getRma()->getStatusModel();
	}
	
	/**
	 * @return array
	 */
	public function getAvailableStatuses($asHash=true){
		return $this->getStatusModel()->getAvailableStatuses($this->getRma(), true, $asHash);
	}
	
	/**
	 * @param ZolagoOs_Rma_Model_Rma_Track $tracking
	 * @return string
	 */
	public function getTrackingStatusName(ZolagoOs_Rma_Model_Rma_Track $tracking) {
		return Mage::helper("zolagodropship")->getTrackingStatusName($tracking);
	}
	
	/**
	 * @return ZolagoOs_Rma_Model_Mysql4_Rma_Track_Collection
	 */
	public function getVendorTrackingCollection() {
		return $this->getRma()->getVendorTracksCollection();
	}
	
	/**
	 * @return ZolagoOs_Rma_Model_Mysql4_Rma_Track_Collection
	 */
	public function getCustomerTrackingCollection() {
		return $this->getRma()->getCustomerTracksCollection();
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
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	public function getVendor() {
		return $this->getRma()->getVendor();
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

	/**
	 * @param string $action
	 * @param array|null $params
	 * @return string
	 */
	public function getPoUrl($action, $params=array()) {
		$params += array(
			"id"=> $this->getPo()->getId(),
			"form_key" => Mage::getSingleton('core/session')->getFormKey()
		);
		return $this->getUrl("udpo/vendor/$action", $params);
	}
	
	/**
	 * @param string $action
	 * @param array|null $params
	 * @return string
	 */
	public function getRmaUrl($action, $params=array()) {
		$params += array(
			"id"=> $this->getRma()->getId(),
			"form_key" => Mage::getSingleton('core/session')->getFormKey()
		);
		return $this->getUrl("*/*/$action", $params);
	}
	
	/**
	 * @return bool
	 */
	public function canUseCarrier() {
		return Mage::helper('orbashipping')->canVendorUseCarrier($this->getVendor());
	}
	
	
    /**
     * @return bool
     */
	public function canVendorUseDhl() {
	    return Mage::helper('orbashipping')->isDhlEnabledForVendor($this->getVendor()) ||
	            Mage::helper('orbashipping')->isDhlEnabledForRma($this->getVendor());
	}
	/**
	 * @return bool
	 */
	public function canPosUseDhl() {
		return Mage::helper('orbashipping')->isDhlEnabledForPos($this->getPo()->getDefaultPos());
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getCarriers()
    {
        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers(
            $this->getPo()->getStoreId()
        );
        $carriers[''] = Mage::helper('sales')->__('* Use PO carrier *');
        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return  array_intersect_key(
			$carriers, 
			array_flip(Mage::helper('zolagodropship')->getAllowedCarriersForVendor($this->getVendor(),true))
		);
    }
	
}

