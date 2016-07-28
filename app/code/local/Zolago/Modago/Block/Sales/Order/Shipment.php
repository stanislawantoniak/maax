<?php
/**
 * shipment info
 */
class Zolago_Modago_Block_Sales_Order_Shipment extends Mage_Core_Block_Template {
	protected $shipment;

    protected function _construct() {
        $this->setTemplate('sales/order/shipment.phtml');
        parent::_construct();
    }

	/**
	 * @return mixed
	 */
	public function getShippingMethodInfo() {
		/* @var $po ZolagoOs_OmniChannelPo_Model_Po */
		$po = $this->getItem();
		return $po->getShippingMethodInfo();
	}

	public function getCarrierTitle() {
		$code = $this->getItem()->getData('current_carrier');
		if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
			return $carrier->getConfigData('title');
		}
		return false;
	}
	public function getTrackingUrl() {
		$code = $this->getItem()->getData('current_carrier');
		if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
			$out = $carrier->getConfigData('tracking_url');
			return $out;
		}
		return false;
	}
	public function getCurrentTracking() {
		$shipment = !$this->shipment ? $this->getItem()->getLastNotCanceledShipment() : $this->shipment;
		if($shipment) {
			return $shipment->getTracksCollection()->setOrder("created_at", "DESC")->getFirstItem()->getTrackNumber();
		}
		return false;
	}
	public function getShipmentDate() {
		$shipment = !$this->shipment ? $this->getItem()->getLastNotCanceledShipment() : $this->shipment;
		if($shipment) {
			// todo: returns tracking letter creation date instead of actual sending date
			return Mage::helper('core')->formatDate($shipment->getTracksCollection()->setOrder("created_at", "DESC")->getFirstItem()->getCreatedAt(), 'medium', false);
		}
		return false;

	}
	public function getDeliveryDate() {
		$startDay = $this->getShipmentDate() ? $this->getShipmentDate() : $this->getItem()->getMaxShippingDate();
		$deliveryDate = date('Y-m-d',Mage::helper('zolagoholidays/datecalculator')->getNextWorkingDay(strtotime($startDay)));
		return Mage::helper('core')->formatDate($deliveryDate, 'medium', false);
	}

	public function getSendDate() {
		return Mage::helper('core')->formatDate($this->getItem()->getMaxShippingDate(), 'medium', false);
	}
}