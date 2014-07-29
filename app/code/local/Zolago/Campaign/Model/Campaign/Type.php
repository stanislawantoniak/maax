<?php
class Zolago_Campaign_Model_Campaign_Type{
	
	const TYPE_SALE = "sale";
	const TYPE_PROMOTION = "promotion";
	
	/**
	 * @return array
	 */
	public function toOptionHash() {
		return array(
			self::TYPE_SALE => Mage::helper("zolagocampaign")->__("Sale"),
			self::TYPE_PROMOTION => Mage::helper("zolagocampaign")->__("Promotion")
		);
	}
    
}