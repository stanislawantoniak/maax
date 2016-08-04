<?php

/**
 * Class ZolagoOs_PickupPoint_Helper_Data
 */
class ZolagoOs_PickupPoint_Helper_Data extends Mage_Core_Helper_Abstract
{
	const CODE = "zolagopickuppoint";
	protected $_code = self::CODE;

    public function getCode()
    {
        return $this->_code;
    }
}