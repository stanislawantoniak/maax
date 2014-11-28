<?php
class Zolago_Sizetable_Model_Sizetable_Rule extends Mage_Core_Model_Abstract {
	protected function _construct() {
		$this->_init('zolagosizetable/sizetable_rule');
	}

	/**
	 * @param mixed[] @data
	 * @throws Exception When array is empty and when cannot set values
	 * @return Zolago_Sizetable_Model_Resource_Sizetable_Rule
	 */
	public function updateModelData($data){
		try{
			if(!empty($data)){
				$this->setSizetableId($data['sizetable_id']);
				$this->setVendorId($data['vendor_id']);
				$this->setBrandId($data['brand_id']);
				$this->setAttributeSetId($data['attribute_set_id']);
			}else{
				throw new Exception("Error Processing Request: Insuficient Data Provided.");
			}
		} catch (Exception $e){
			Mage::logException($e);
		}
		return $this->getSelect();
	}
}