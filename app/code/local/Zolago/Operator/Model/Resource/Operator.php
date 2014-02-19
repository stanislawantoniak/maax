<?php

class Zolago_Operator_Model_Resource_Operator extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zolagooperator/operator', "operator_id");
	}

    /**
     * fill times
     */	
     protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
 		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {

			$object->setCreatedAt($currentTime);
		}
		$object->setUpdatedAt($currentTime);
		
		// Do not modify passwd hash - return orig passwd
		if($object->getId()){
			$object->setPassword($object->getOrigData('password'));
		}
		// Modify passwd hash by special param
		if($object->getPostPassword()){
			$helper = Mage::helper('core');
			/* @var $helper Mage_Core_Helper_Data */
			$hash = $helper->getHash($object->getPostPassword());
			$object-setPassword($hash);
			$object->setPostPassword(null);
		}
		
		return parent::_prepareDataForSave($object);     	
     }
}

