<?php

class Zolago_Operator_Model_Operator_Validator extends Zolago_Common_Model_Validator_Abstract {
	
	protected function _getHelper() {
		return Mage::helper('zolagooperator');
	}	
	public function validate($data) {

		$this->_errors = array();
		$this->_data = $data;

		$this->_stringLength('firstname','First name',100);
		$this->_stringLength('lastname','Last name',100);
		$this->_notEmpty('is_active','Is active');

		$this->_notEmpty('email', 'Email');
		$this->_stringLength('email','Email',100);
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($data['email'])) {
			$this->_errors[] = $this->_helper->__('Email address is not valid ');			
		}

		return $this->_errors;
	}

}
