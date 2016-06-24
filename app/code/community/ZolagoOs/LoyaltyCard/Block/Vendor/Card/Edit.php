<?php

/**
 * Class ZolagoOs_LoyaltyCard_Block_Vendor_Card_Edit
 */
class ZolagoOs_LoyaltyCard_Block_Vendor_Card_Edit extends ZolagoOs_LoyaltyCard_Block_Vendor_Card_Abstract {

	public function _prepareLayout() {
		parent::_prepareLayout();
		/** @see template/zosloyaltycard/vendor/card/edit.phtml */
	}

	/**
	 * @return ZolagoOs_LoyaltyCard_Model_Card
	 */
	public function getModel() {
		if (!Mage::registry("current_loyalty_card")) {
			Mage::register("current_loyalty_card", Mage::getModel("zosloyaltycard/card"));
		}
		return Mage::registry("current_loyalty_card");
	}

	/**
	 * @return bool
	 */
	public function isModelNew() {
		return $this->getModel()->isObjectNew();
	}
	
	public function getSaveUrlAction() {
		return $this->getUrl("loyalty/card/save", array("_secure" => true));
	}

	public function getDeleteUrlAction() {
		return $this->getUrl("loyalty/card/delete", array("_secure" => true));
	}

}