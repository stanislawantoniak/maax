<?php

class Zolago_Customer_Model_Source_Loyalty_Card_Types extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	/**
	 * Types defined in config
	 * see customer/loyalty_card/config
	 * 
	 * @param bool $withEmpty
	 * @param bool $defaultValues
	 * @return array
	 */
	public function getAllOptions($withEmpty = true, $defaultValues = false) {
		if (!$this->_options) {
			/** @var ZolagoOs_LoyaltyCard_Helper_Data $helper */
			$helper = Mage::helper("zosloyaltycard");
			$config = $helper->getLoyaltyCardConfig();

			$options = array(array(
				'value' => '',
				'label' => ''
			));

			foreach ($config as $id => $type){
				$options[] = array (
					'value' => $id,
					'label' => $type['name']
				);
			}
			$this->_options = $options;
		}
		return $this->_options;
	}

	public function toOptionArray()
	{
		return $this->getAllOptions();
	}
	
}