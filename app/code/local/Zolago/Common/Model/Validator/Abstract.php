<?php
/**
 * abstract for validators
 */
abstract class Zolago_Common_Model_Validator_Abstract {
	protected $_errors = array();
	
	protected $_data;
	
	protected $_helper;
	
	
	public function __construct() {
	    $this->_helper = $this->_getHelper();
	}
    /**
     * not empty 
     */
    protected function _notEmpty($field,$message) {
		if (!Zend_Validate::is($this->_data[$field], 'NotEmpty')) {
			$this->_errors[] = $this->_helper->__('%s is required', $this->_helper->__($message));
		}
    }
	
    /**
     * string length
     */
     
    protected function _stringLength($field,$message,$max) {
		if (!empty($this->_data[$field]) &&
				!Zend_Validate::is($this->_data[$field], "StringLength", array("max" => $max))) {
			$this->_errors[] = $this->_helper->__('Max length of %s is %d', $this->_helper->__($message), $max);
		}
    }

    abstract protected function _getHelper();
    abstract public function validate($data);
}