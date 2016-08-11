<?php
class Zolago_Payment_Model_Provider extends Mage_Core_Model_Abstract{
    protected function _construct() {
        $this->_init('zolagopayment/provider');
    }
	
	/**
	 * @param Mage_Core_Model_Website | int | string | null $website
	 * @return bool
	 */
	public function isValid($website=null) {
		if(null===$website){
			$website = Mage::app()->getWebsite()->getCode();
		}
		$config = Mage::getSingleton('zolagopayment/config')->getProviderConfig(
			$website, 
			$this, 
			$this->getType()
		);
		$valid =  (bool) (!is_null($config)) && (empty($config['deny']));
		return $valid;
	}
    
}
