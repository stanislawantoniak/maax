<?php

/**
 * Class Modago_Integrator_Helper_Data
 */
class Modago_Integrator_Helper_Data extends Mage_Core_Helper_Abstract
{
	const STATUS_ERROR = 'ERROR'; //Modago_Integrator error - error that we've been expecting
	const STATUS_FATAL_ERROR = 'FATAL'; //Modago server error, contact Modago administration
	const STATUS_OK = 'OK';
	const FILE_DESCRIPTIONS = 'DESCRIPTIONS';
	const FILE_PRICES = 'PRICES';
	const FILE_STOCKS = 'STOCKS';

	public function getFileTypes() {
		return array(self::FILE_DESCRIPTIONS,self::FILE_PRICES,self::FILE_STOCKS);
	}
}