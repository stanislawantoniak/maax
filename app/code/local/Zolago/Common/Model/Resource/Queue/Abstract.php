<?php
/**
 * abstract resource model for queues
 */
abstract class Zolago_Common_Model_Resource_Queue_Abstract extends Mage_Core_Model_Resource_Db_Abstract {
 	/**
	 * Set times
	 * @param Mage_Core_Model_Abstract $object
	 * @return type
	 */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getInsertDate()) {

			$object->setInsertDate($currentTime);
		}
		return parent::_prepareDataForSave($object);
	}

}