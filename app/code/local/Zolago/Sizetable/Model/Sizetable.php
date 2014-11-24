<?php
class Zolago_Sizetable_Model_Sizetable extends Mage_Core_Model_Abstract{

	protected function _construct() {
		$this->_init('zolagosizetable/sizetable');
	}

	/**
	 * @param mixed[] @data
	 * @throws Exception When array is empty and when cannot set values
	 * @return Zolago_Sizetable_Model_Sizetable
	 */
	public function updateModelData($data){
		try{
			if(!empty($data)){
				$vendor_id = Mage::getSingleton('udropship/session')->getVendor()->getVendorId();
				$this->setName($data['name']);
				$this->setVendorId($vendor_id);
				$this->setDefaultValue($data['default_value']);
			}else{
				throw new Exception("Error Processing Request: Insuficient Data Provided.");
			}
		} catch (Exception $e){
			Mage::logException($e);
		}
		return $this;
	}

}