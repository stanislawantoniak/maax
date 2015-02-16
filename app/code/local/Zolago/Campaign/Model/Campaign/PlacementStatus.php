<?php
class Zolago_Campaign_Model_Campaign_PlacementStatus{
	
	const TYPE_EXPIRED = "expired";
	const TYPE_ACTIVE = "active";
    const TYPE_EXPIRES_SOON = "expires_soon";
    const TYPE_FUTURE = "future";
	
	/**
	 * @return array
	 */
	public function toOptionHash() {
		return array(
			self::TYPE_EXPIRED => Mage::helper("zolagocampaign")->__("Expired"),
			self::TYPE_ACTIVE => Mage::helper("zolagocampaign")->__("Active"),
            self::TYPE_EXPIRES_SOON => Mage::helper("zolagocampaign")->__("Expires soon"),
            self::TYPE_FUTURE => Mage::helper("zolagocampaign")->__("Will start soon")
		);
	}


    public function toOptionArray() {
        $optionArray = array();
        $icons = array(
            self::TYPE_EXPIRED => 'icon-remove',
            self::TYPE_ACTIVE => 'icon-ok',
            self::TYPE_EXPIRES_SOON => 'icon-warning-sign',
            self::TYPE_FUTURE => 'icon-time'
        );
        $messages = array(
            self::TYPE_EXPIRED => Mage::helper("zolagocampaign")->__('Campaign has ended. Creative is not active.'),
            self::TYPE_ACTIVE => Mage::helper("zolagocampaign")->__('Creative is active.'),
            self::TYPE_EXPIRES_SOON => Mage::helper("zolagocampaign")->__('Creative will expire soon.'),
            self::TYPE_FUTURE => Mage::helper("zolagocampaign")->__('Creative will start soon.')
        );

        foreach ($this->toOptionHash() as $option => $label) {
            $optionArray[$option] = array(
                'status' => $option,
                'icon' => $icons[$option],
                'message' => $messages[$option]
            );
        }
        return $optionArray;
    }
    
}