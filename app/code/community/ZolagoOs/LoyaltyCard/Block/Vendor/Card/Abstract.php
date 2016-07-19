<?php

class ZolagoOs_LoyaltyCard_Block_Vendor_Card_Abstract extends Mage_Core_Block_Template {

	/**
	 * Trick: take file from store package/skin dir
	 * 
	 * @return string
	 */
	public function getTemplateFile() {
		$params = array('_relative'=>true);
		$area = $this->getArea();
		if ($area) {
			$params['_area'] = $area;
		}
		$params['_package'] = Mage::app()->getStore()->getConfig("design/package/name");
		$params['_theme'] = Mage::app()->getStore()->getConfig("design/theme/skin");
		$templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
		return $templateName;
	}

	/**
	 * @return Zolago_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->getSession()->getVendor();
	}
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Session
	 */
	public function getSession() {
		return Mage::getSingleton('udropship/session');
	}
}