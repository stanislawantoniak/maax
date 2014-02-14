<?php

class Zolago_Pos_Model_Pos_Validator {

	public function validate($data) {

		$errors = array();

		$helper = Mage::helper('zolagopos');

		if (isset($data['external_id']) && $data['external_id'] &&
				!Zend_Validate::is($data['external_id'], "StringLength", array("max" => 100))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('External id'), 100);
		}

		if (!Zend_Validate::is($data['is_active'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('Is active'));
		}

		if (isset($data['client_number']) && $data['client_number'] &&
				!Zend_Validate::is($data['client_number'], "StringLength", array("max" => 100))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('Client number'), 100);
		}

		if (!Zend_Validate::is($data['minimal_stock'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('Minimal stock'));
		}

		if (!Zend_Validate::is($data['minimal_stock'], 'Digits')) {
			$errors[] = $helper->__('%s is not number', $helper->__('Minimal stock'));
		}

		if (!Zend_Validate::is($data['name'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('Name'));
		}

		if (!Zend_Validate::is($data['name'], "StringLength", array("max" => 100))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('Name'), 100);
		}

		if (isset($data['company']) && $data['company'] && 
				!Zend_Validate::is($data['company'], "StringLength", array("max" => 150))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('Company'), 150);
		}

		if (!Zend_Validate::is($data['country_id'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('Country'));
		}

		if (isset($data['region_id']) && $data['region_id'] &&
				!Zend_Validate::is($data['region_id'], 'Digits')) {
			$errors[] = $helper->__('%s is not number', $helper->__('Region'));
		}

		if (isset($data['region']) && $data['region'] &&
				!Zend_Validate::is($data['region'], "StringLength", array("max" => 100))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('Region'), 100);
		}

		if (!Zend_Validate::is($data['postcode'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('Postcode'));
		}

		if (!Zend_Validate::is($data['postcode'], 'PostCode', array("format" => "\d\d-\d\d\d"))) {
			$errors[] = $helper->__('%s has not valid format (ex.12-345)', $helper->__('Postcode'));
		}

		if (!Zend_Validate::is($data['street'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('Street'));
		}

		if (!Zend_Validate::is($data['street'], "StringLength", array("max" => 150))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('Street'), 150);
		}

		if (!Zend_Validate::is($data['city'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('City'));
		}

		if (!Zend_Validate::is($data['city'], "StringLength", array("max" => 100))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('City'), 100);
		}

		if (isset($data['email']) && $data['email'] &&
				!Zend_Validate::is($data['email'], "StringLength", array("max" => 100))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('Email'), 100);
		}

		if (!Zend_Validate::is($data['phone'], 'NotEmpty')) {
			$errors[] = $helper->__('%s is required', $helper->__('City'));
		}

		if (!Zend_Validate::is($data['phone'], "StringLength", array("max" => 50))) {
			$errors[] = $helper->__('Max length of %s is %d', $helper->__('Phone'), 50);
		}

		return $errors;
	}

}

?>
