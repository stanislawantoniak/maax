<?php

class Zolago_Mapper_Model_Resource_Mapper_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagomapper/mapper');
    }

	
	/**
	 * @param int $mode
	 * @return Zolago_Mapper_Model_Resource_Mapper_Collection
	 */
	public function addIsActiveFilter($mode=1) {
		$this->addFieldToFilter("is_active", (int)$mode);
		return $this;
	}
	
	/**
	 * @return Zolago_Mapper_Model_Resource_Mapper_Collection
	 */
	public function joinAttributeSet() {
		$eavConfig = Mage::getSingleton('eav/config');
		/* @var $eavConfig Mage_Eav_Model_Config */
		$productEntityType = $eavConfig->getEntityType(Mage_Catalog_Model_Product::ENTITY);
		
		$this->getSelect()->
			joinLeft(
				array("attribute_set"=>$this->getTable("eav/attribute_set")), 
				"main_table.attribute_set_id=attribute_set.attribute_set_id",
				array("attribute_set_name", "attribute_set_id")
			)->
			where("attribute_set.entity_type_id=?", $productEntityType->getId());
		return $this;
	}

    /**
     * Add custom identifier to collection
     * because row can be mapper or attribute set
     * custom_id is like attribute_set_id:mapper_id
     * if any is null zero is taken
     * @return Zolago_Mapper_Model_Resource_Mapper_Collection
     */
    public function addCustomId() {
        $this->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array("CONCAT(IFNULL(attribute_set.attribute_set_id,'0'), ':', IFNULL(main_table.mapper_id,'0')) AS custom_id",
                "attribute_set.attribute_set_name",
                "attribute_set.attribute_set_id"))
            ->columns();
        return $this;
    }
	
	public function getIdFieldName() {
		if($this->getFlag('abstract')){
			return null;
		}
		return parent::getIdFieldName();
	}
	
	protected function _getItemId(Varien_Object $item) {		
		if($this->getFlag('abstract')){
			return null;
		}
		return parent::_getItemId($item);
	}
	
}
