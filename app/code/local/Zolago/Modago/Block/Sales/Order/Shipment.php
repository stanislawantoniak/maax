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

		return false;
	}
	public function getCurrentTracking(Mage_Sales_Model_Order_Shipment $shipment = null) {
		if($shipment instanceof  Mage_Sales_Model_Order_Shipment && $shipment->getId()){
			return $shipment->getTracksCollection()->setOrder("created_at", "DESC")->getFirstItem();
		}
		return null;
	}
}