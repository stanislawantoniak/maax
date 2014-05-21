<?php
class Zolago_Converter_Helper_Data extends Mage_Core_Helper_Abstract{
	
	/**
	 * @param Zolago_Pos_Model_Pos $pos
	 * @param string $vsku
	 * @return int|null
	 */
    public function getQtyForPos(Zolago_Pos_Model_Pos $pos,$vsku) {
		return Mage::getSingleton('zolagoconverter/client')
			->getQtyForPos($pos->getExternalId(), $vsku);
	}
} 