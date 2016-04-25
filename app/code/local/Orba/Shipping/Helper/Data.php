<?php

/**
 * helper for dhl module
 */
class Orba_Shipping_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function canPosUseCarrier(Zolago_Pos_Model_Pos $pos) {
        return (bool)(int)($pos->getUseDhl()) | (bool)(int)($pos->getUseOrbaups());
    }
    public function canVendorUseCarrier(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
		return (bool)(int)($vendor->getUseDhl()) | (bool)(int)($vendor->getUseOrbaups())
		    | (bool)(int)($vendor->getDhlRma());
	}

    public function isDhlEnabledForVendor(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
		return (bool)(int)$vendor->getUseDhl();
	}
    public function isDhlEnabledForRma(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
		return (bool)(int)$vendor->getDhlRma();
	}
    public function isDhlEnabledForPos(Zolago_Pos_Model_Pos $pos) {
		return (bool)(int)$pos->getUseDhl();
	}
	
	/**
	 * Special Log Message Function
	 * 
	 * @param string $message	Message to Log
	 * @param string $logFile	Log file name. Default: dhl_tracking.log
	 */
	public function _log($message, $logFile = false) {
		if (!$logFile) {
			$logFile = $this->_dhlLogFile;
		}
		
		Mage::log($message, null, $logFile, true);
	}
	
	/**
	 * Get Dhl Next Check Date
	 * 
	 * @param integer $storeId
	 * 
	 * @return date	Date Object of Next Check
	 */
	public function getNextDhlCheck($storeId)
	{
		$repeatIn = Mage::getStoreConfig('carriers/orbadhl/repeat_tracking', $storeId);
		if ($repeatIn <= 0) {
			$repeatIn = 1;
		}
		$repeatIn = $repeatIn*60*60;
		return date('Y-m-d H:i:s', time()+$repeatIn);		
	}
	
	
	/**
	 * Check if DHL Waybill cna be shown
	 * 
	 * @param type $track
	 * @param type $shipment
	 * 
	 * @return boolean $canShow Boolean Value
	 */
	public function canShowWaybill($track, $shipment)
	{
		$canShow = false;
		if ($track->getCarrierCode() == Zolago_Dhl_Helper_Data::DHL_CARRIER_CODE
			&& $track->getNumber()
			&& $shipment->getUdropshipStatus() != ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED) {
			$canShow = true;
		}
		
		return $canShow;
	}

	/**
	 * @param string $carrier
	 * @return Orba_Shipping_Model_Carrier_Dhl|Orba_Shipping_Model_Carrier_Ups|Orba_Shipping_Model_Carrier_Default|false|Mage_Core_Model_Abstract
	 */
     public function getShippingManager($carrier) {
         switch ($carrier) {
             case Orba_Shipping_Model_Carrier_Dhl::CODE:
                 $model = Mage::getModel('orbashipping/carrier_dhl');
                 break;
             case Orba_Shipping_Model_Carrier_Ups::CODE:
                 $model = Mage::getModel('orbashipping/carrier_ups');
                 break;
             default:
                 $model = Mage::getModel('orbashipping/carrier_default');
         }
         return $model;
     }
}