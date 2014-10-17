<?php
/**
 * shipment info
 */
class Zolago_Modago_Block_Sales_Order_Shipment extends Mage_Core_Block_Template {
    protected function _construct() {
        $this->setTemplate('sales/order/shipment.phtml');
        parent::_construct();
    }
	public function getCarrierTitle($code) {
		if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
			return $carrier->getConfigData('title');
		}
		return null;
	}
	public function getCurrentTracking() {
		$shipment = $this->getItem()->getLastNotCanceledShipment();
		$collection = $shipment->getTracksCollection()->setOrder("created_at", "DESC");
		return $collection->getFirstItem()->getTrackNumber();
	}
}