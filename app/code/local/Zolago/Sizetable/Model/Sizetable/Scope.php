<?php
class Zolago_Sizetable_Model_Sizetable_Scope extends Mage_Core_Model_Abstract {
	protected function _construct() {
		$this->_init('zolagosizetable/sizetable_scope');
	}

	/**
	 * @param mixed[] @data
	 * @throws Exception When array is empty and when cannot set values
	 * @return Zolago_Sizetable_Model_Sizetable_Scope
	 */
	public function updateModelData($data){
		try{
			if(!empty($data)){
				$this->setSizetableId($data['sizetable_id']);
				$this->setStoreId($data['store_id']);
				$this->setDefaultValue($data['value']);
			}else{
				throw new Exception("Error Processing Request: Insuficient Data Provided.");
			}
		} catch (Exception $e){
			Mage::logException($e);
		}
		return $this;
	}
}