<?php

/**
 * Class ZolagoOs_Pwr_Model_Observer
 */
class ZolagoOs_Pwr_Model_Observer {
	
	public function updateAllPoints($observer) {
		/** @var ZolagoOs_Pwr_Helper_Data $helper */
		$helper = Mage::helper('zospwr');
		$isActive = $helper->isActive();
		if ($isActive) {
			/** @var ZolagoOs_Pwr_Model_Resource_Point $res */
			$res = Mage::getResourceModel("zospwr/point");
			$res->updateAllPoints();
		}
	}
}