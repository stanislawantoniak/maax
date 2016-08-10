<?php

/**
 * Class ZolagoOs_Pwr_Helper_Data
 */
class ZolagoOs_Pwr_Helper_Data extends Mage_Core_Helper_Abstract {
	const CODE = "zolagopwr";
	protected $_code = self::CODE;

	public function getCode()
	{
		return $this->_code;
	}
}