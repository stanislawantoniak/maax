<?php

/**
 * events log
 *
 * @method string getId()
 * @method string setId($value)
 * @method string getDate()
 * @method string setDate($value)
 * @method string getText()
 * @method string setText($value)
 */
class Modago_Integrator_Model_Log extends Mage_Core_Model_Abstract {

	public function _construct() {
		$this->_init('modagointegrator/log');
	}

	protected function _beforeSave() {
		parent::_beforeSave();
		if (!$this->hasData('date')) {
			$this->setDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
		}
		return $this;
	}
}