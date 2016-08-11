<?php

class Zolago_Pos_Model_Pos_Validator extends Zolago_Common_Model_Validator_Abstract {
	
	protected function _getHelper() {
		return Mage::helper('zolagopos');
	}
	
	public function validate($data) {

		$this->_errors = array();


		$this->_data = $data;

		$this->_stringLength('external_id','External id',100);

		$this->_notEmpty('is_active','Is active');
		$this->_notEmpty('is_available_as_pickup_point','Is POS available as Pick-Up Point');

		$this->_stringLength('client_number','Client number',100);

		$this->_notEmpty('minimal_stock','Minimal stock');

		if (!Zend_Validate::is($this->_data['minimal_stock'], 'Digits')) {
			$this->_errors[] = $this->_helper->__('%s is not number', $this->_helper->__('Minimal stock'));
		}
		
		$this->_notEmpty('priority','Priority');

		if (!Zend_Validate::is($this->_data['priority'], 'Digits')) {
			$this->_errors[] = $this->_helper->__('%s is not number', $this->_helper->__('Priority'));
		}
		
		$this->_notEmpty('name','Name');
		$this->_stringLength('name','Name',100);
		
		$this->_stringLength('company','Company',150);

		$this->_notEmpty('country_id','Country');

		if (!empty($this->_data['region_id']) &&
				!Zend_Validate::is($this->_data['region_id'], 'Digits')) {
			$this->_errors[] = $this->_helper->__('%s is not number', $this->_helper->__('Region'));
		}
		$this->_stringLength('region','Region',100);

		$this->_notEmpty('postcode','Postcode');

		if (!Zend_Validate::is($this->_data['postcode'], 'PostCode', array("format" => "\d\d-\d\d\d"))) {
			$this->_errors[] = $this->_helper->__('Zip code has not valid format (ex.12-345)');
		}
		$this->_notEmpty('street','Street');
		$this->_stringLength('street','Street',150);

		
		$this->_notEmpty('city','City');
		$this->_stringLength('city','City',100);

		$this->_stringLength('email','Email',100);

		$this->_notEmpty('phone','Phone');
		$this->_stringLength('phone','Phone',50);

		return $this->_errors;
	}

}
