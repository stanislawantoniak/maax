<?php
class Zolago_Rma_Model_Resource_Rma_Reason_Vendor extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/rma_reason_vendor', "vendor_return_reason_id");
    }
	
	/**
     * Prepare data for save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object){
		
		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {

			$object->setCreatedAt($currentTime);
		}
		$object->setUpdatedAt($currentTime);
		return parent::_prepareDataForSave($object);
		
	}
}