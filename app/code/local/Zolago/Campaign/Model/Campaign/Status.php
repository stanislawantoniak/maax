<?php
class Zolago_Banner_Model_Banner_Status {
	
	const TYPE_ACTIVE   = 1;
	const TYPE_ARCHIVE  = -1;
	const TYPE_INACTIVE = 0;
 	
	
	/**
	 * @return array
	 */
	public function toOptionHash() {
		return array(
			self::TYPE_ACTIVE => Mage::helper("zolagocampaign")->__("Active"),
			self::TYPE_ARCHIVE => Mage::helper("zolagocampaign")->__("Archive"),
			self::TYPE_INACTIVE => Mage::helper("zolagocampaign")->__("Inactive")
		);
	}
    
}