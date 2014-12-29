<?php
class Zolago_Payment_Model_Provider extends Mage_Core_Model_Abstract{

    protected function _construct() {
        $this->_init('zolagopayment/provider');
    }
	
	public function isValid() {
		return true;
	}
    
}
