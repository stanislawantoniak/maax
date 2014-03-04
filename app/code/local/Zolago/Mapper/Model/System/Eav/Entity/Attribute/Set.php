<?php

/**
 * Description of Set
 */
class Zolago_Mapper_Model_System_Eav_Entity_Attribute_Set {
	/**
	 * @var Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
	 */
	var $_collection;
	
	public function toOptionHash() {
		return $this->getCollection()->toOptionHash();
	}
	/**
	 * @return Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
	 */
	public function getCollection() {
		if(!$this->_collection){
			$entity = Mage::getSingleton("eav/config")->getEntityType(Mage_Catalog_Model_Product::ENTITY);
			$this->_collection = Mage::getResourceModel("eav/entity_attribute_set_collection");
			$this->_collection->setEntityTypeFilter($entity->getId());
		}
		return $this->_collection;
	}
}