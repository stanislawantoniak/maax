<?php

class Zolago_Banner_Model_Banner_Validator extends Zolago_Common_Model_Validator_Abstract {
	
	protected function _getHelper() {
		return Mage::helper('zolagobanner');
	}	
	public function validate($data) {

		$this->_errors = array();
		$this->_data = $data;

		//@todo validate

		return $this->_errors;
	}

}
