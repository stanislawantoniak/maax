<?php
class Zolago_Mapper_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Remember errors from process to register
	 * Purpose: AOE cron messages for admin
	 * @param $e
	 */
	public function registerError($e) {
		$value = '';
		if (!is_null(Mage::registry('zolago_mapper_error'))) {
			$value = Mage::registry('zolago_mapper_error');
			Mage::unregister('zolago_mapper_error');
		}
		$value .= $e->getMessage(). " \n";
		Mage::register('zolago_mapper_error', $value);
	}
}