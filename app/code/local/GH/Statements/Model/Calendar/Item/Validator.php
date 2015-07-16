<?php

class GH_Statements_Model_Calendar_Item_Validator extends Zolago_Common_Model_Validator_Abstract {
	
	protected function _getHelper() {
		return Mage::helper('ghstatements');
	}
	
	public function validate($data) {

		$this->_errors = array();
		$this->_data = $data;
		
		$this->_notEmpty('event_date','Event date');

		return $this->_errors;
	}

}
