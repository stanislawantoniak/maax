<?php
class Zolago_Converter_Helper_Data extends Mage_Core_Helper_Abstract{
	

	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor|int $vendor
	 * @param Zolago_Pos_Model_Pos | int $pos
	 * @param string $vsku
	 * @return type
	 */
    public function getQty($vendor, $pos, $vsku) {
		
		if(!($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor)){
			$vendor = Mage::getModel("udropship/vendor")->load($vendor);
		}
		if(!($pos instanceof Zolago_Pos_Model_Pos)){
			$pos = Mage::getModel("zolagopos/pos")->load($pos);
		}
		
		return Mage::getSingleton('zolagoconverter/client')
			->getQty($vendor->getExternalId(), $pos->getExternalId(), $vsku);
	}
} 