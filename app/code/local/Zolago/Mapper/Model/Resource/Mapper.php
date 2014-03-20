<?php

class Zolago_Mapper_Model_Resource_Mapper extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zolagomapper/mapper', "mapper_id");
	}

	/**
	 * @param int|array $categoryIds
	 * @return array (attributeId=>attributeName,...)
	 */
	public function getAttributesByCategory($categoryIds, $excludeCodes=array()) {
		if(!is_array($categoryIds)){
			$categoryIds = array($categoryIds);
		}
		$inputTypes = array("select", "boolean", "multiselect");
		
		$adapter = $this->getReadConnection();
		$select = $adapter->select();
		
		// Attribute
		$select->from(
				array("attribute"=>$this->getTable("eav/attribute")), 
				array("attribute.attribute_id", "attribute.frontend_label")
		);
		
		// Catalog Attribute
		$select->join(
				array("catalog_attribute"=>$this->getTable("catalog/eav_attribute")), 
				"catalog_attribute.attribute_id=attribute.attribute_id",
				array()
		);
		
		// Group
		$select->join(
				array("entity_attribute"=>$this->getTable("eav/entity_attribute")), 
				"entity_attribute.attribute_id=attribute.attribute_id",
				array()
		);
		
		// Set
		$select->join(
				array("attribute_set"=>$this->getTable("eav/attribute_set")), 
				"attribute_set.attribute_set_id=entity_attribute.attribute_set_id",
				array()
		);
		
		// Mapper
		$select->join(
				array("mapper"=>$this->getMainTable()), 
				"mapper.attribute_set_id=attribute_set.attribute_set_id",
				array()
		);
		
		// Mapper categories
				$select->join(
				array("mapper_category"=>$this->getTable("zolagomapper/mapper_category")), 
				"mapper_category.mapper_id=mapper.mapper_id",
				array()
		);
				
		// Categories
		$select->join(
				array("category"=>$this->getTable("catalog/category")), 
				"mapper_category.category_id=category.entity_id",
				array()
		);
		
		if(count($excludeCodes)){
			$select->where("attribute.attribute_code NOT IN (?)", $excludeCodes);
		}
		
		$select->where("attribute.frontend_input IN (?)", $inputTypes);
		$select->where("category.entity_id IN (?)", $categoryIds);
		$select->where("catalog_attribute.is_filterable>?", 0);
		$select->where("catalog_attribute.is_filterable>?", 0);
		$select->order("attribute.frontend_label");
		$select->distinct();
		
		return $adapter->fetchPairs($select);
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

