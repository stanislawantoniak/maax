<?php

/**
 * Class GH_Marketing_Model_Source_Marketing_Cost_Type
 */
class GH_Marketing_Model_Source_Marketing_Cost_Type_Option {

	const OPTION_CPC = 'cpc';
	const OPTION_CPS  = 'cps';
	const OPTION_FIXED = 'fixed';

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray($isMultiselect = false) {
		/** @var GH_Marketing_Helper_Data $hlp */
		$hlp = Mage::helper('ghmarketing');

		$options = array(
			array('value' => self::OPTION_CPC, 'label' => $hlp->__('CPC')),
			array('value' => self::OPTION_CPS,  'label' => $hlp->__('CPS')),
			array('value' => self::OPTION_FIXED, 'label' => $hlp->__('Fixed')),
		);

		if (!$isMultiselect) {
			array_unshift($options, array('value' => '', 'label' => ''));
		}
		return $options;
	}

	/**
	 * Get options in "key-value" format
	 *
	 * @return array
	 */
	public function toArray() {
		/** @var GH_Marketing_Helper_Data $hlp */
		$hlp = Mage::helper('ghmarketing');
		return array(
			self::OPTION_CPC => $hlp->__('CPC'),
			self::OPTION_CPS  => $hlp->__('CPS'),
			self::OPTION_FIXED => $hlp->__('Fixed'),
		);
	}

}
