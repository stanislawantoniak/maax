<?php

class GH_Statements_Model_Calendar_Item_Validator extends Zolago_Common_Model_Validator_Abstract {

	const DATE_FORMAT = 'Y-m-d';

	protected function _getHelper() {
		return Mage::helper('ghstatements');
	}
	
	public function validate($data) {

		$this->_errors = array();
		$this->_data = $data;
		
		$this->_notEmpty('event_date','Event date');
		$this->_validateDate('event_date','Event date');
		return $this->_errors;
	}
	
	protected function _validateDate($field,$message) {
        if (!empty($this->_data[$field])) {
            $date = $this->_data[$field];
            $format = self::DATE_FORMAT;
    	    $d = DateTime::createFromFormat($format, $date);
            if (!$d || ($d->format($format) != $date)) {
                $this->_errors[] = $this->_helper->__('Wrong date format at field: %s',$this->_helper->__($message));
            }
	    }
	}
}
