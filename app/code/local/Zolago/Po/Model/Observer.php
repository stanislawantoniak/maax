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
		}
	}
}

?>
