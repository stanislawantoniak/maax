<?php

class GH_Dhl_Model_Dhl_Validator extends Zolago_Common_Model_Validator_Abstract {
	
	protected function _getHelper() {
		return Mage::helper('ghdhl');
	}
	
	public function validate($data) {

		$this->_errors = array();
		$this->_data = $data;
		
		$this->_notEmpty('dhl_account','DHL Account');
        $this->_notEmpty('dhl_login','DHL Login');
        $this->_notEmpty('dhl_password','DHL Password');

		return $this->_errors;
	}

}
