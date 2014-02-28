<?php

class Zolago_Mapper_Model_Resource_Mapper extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zolagomapper/mapper', "mapper_id");
	}
	
	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return array
	 */
	public function getCategoryIds(Mage_Core_Model_Abstract $object) {
		$select = $this->getReadConnection()->select();
		$select->from(
				array("mapper_category"=>$this->getTable("zolagomapper/mapper_category")),
				array("category_id")
		);
		$select->where("mapper_id=?", $object->getId());
		return $this->getReadConnection()->fetchCol($select);
	}

	/**
	 * Set times
	 * @param Mage_Core_Model_Abstract $object
	 * @return type
	 */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {

			$object->setCreatedAt($currentTime);
		}
		$object->setUpdatedAt($currentTime);
		return parent::_prepareDataForSave($object);
	}
	
	protected function _afterSave(Mage_Core_Model_Abstract $object) {
		$this->_setCategoryIds($object->getCategoryIds(), $object);
		return parent::_afterSave($object);
	}
	
	protected function _setCategoryIds(array $categoryIds, Mage_Core_Model_Abstract $object) {
		$wConn = $this->_getWriteAdapter();
		$catMapTable = $this->getTable("zolagomapper/mapper_category");
		$wConn->delete(
				$catMapTable, 
				$wConn->quoteInto("mapper_id=?", $object->getId())
		);
		$toInsert = array();
		foreach($categoryIds as $cId){
			$toInsert[] = array("mapper_id"=>$object->getId(), "category_id"=>$cId);
		}
		if(count($toInsert)){
			$wConn->insertMultiple($catMapTable, $toInsert);
		}
		return $this;
	}

}

