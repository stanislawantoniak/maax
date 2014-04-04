<?php

class Zolago_Catalog_Model_Resource_Vendor_Mass 
	extends Mage_Core_Model_Resource_Db_Abstract{
	
	protected function _construct() {
		$this->_init("udropship/vendor_product_assoc", null);
	}
	
	
	
	/**
	 * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attrbiute
	 * @param stinrg $dir
	 */
	public function addMultipleValueSortToCollection(
		Mage_Catalog_Model_Resource_Eav_Attribute $attrbiute,
		Mage_Catalog_Model_Resource_Product_Collection $collection,
		$dir = Varien_Db_Select::SQL_ASC) {
	
		$valueTable1    = $attrbiute->getAttributeCode() . '_t1';
        $valueTable2    = $attrbiute->getAttributeCode() . '_t2';
        $collection->getSelect()
            ->joinLeft(
                array($valueTable1 => $attrbiute->getBackend()->getTable()),
                "e.entity_id={$valueTable1}.entity_id"
                . " AND {$valueTable1}.attribute_id='{$attrbiute->getId()}'"
                . " AND {$valueTable1}.store_id=0",
                array())
            ->joinLeft(
                array($valueTable2 => $attrbiute->getBackend()->getTable()),
                "e.entity_id={$valueTable2}.entity_id"
                . " AND {$valueTable2}.attribute_id='{$attrbiute->getId()}'"
                . " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                array()
            );
        $valueExpr = $collection->getSelect()->getAdapter()
            ->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");
		
		$comma = ",";
		// Sort by comma count - separed values
		
		$sortAttribute = "{$attrbiute->getAttributeCode()}_count";
		
		$expressions = "ROUND ((LENGTH({$valueExpr}) - LENGTH( REPLACE ({$valueExpr}, '{$comma}', ''))) / LENGTH('{$comma}'))";
		
		$collection->getSelect()->columns(array(
			$sortAttribute => $expressions
		));
		
		$collection->getSelect()
			->order("{$sortAttribute} {$dir}");
			
	}
	/**
	 * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attrbiute
	 * @param stinrg $dir
	 */
	public function addBoolValueSortToCollection(
		Mage_Catalog_Model_Resource_Eav_Attribute $attrbiute,
		Mage_Catalog_Model_Resource_Product_Collection $collection,
		$dir = Varien_Db_Select::SQL_ASC) {
		
		$valueTable1    = $attrbiute->getAttributeCode() . '_t1';
        $valueTable2    = $attrbiute->getAttributeCode() . '_t2';
        $collection->getSelect()
            ->joinLeft(
                array($valueTable1 => $attrbiute->getBackend()->getTable()),
                "e.entity_id={$valueTable1}.entity_id"
                . " AND {$valueTable1}.attribute_id='{$attrbiute->getId()}'"
                . " AND {$valueTable1}.store_id=0",
                array())
            ->joinLeft(
                array($valueTable2 => $attrbiute->getBackend()->getTable()),
                "e.entity_id={$valueTable2}.entity_id"
                . " AND {$valueTable2}.attribute_id='{$attrbiute->getId()}'"
                . " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                array()
            );
        $valueExpr = $collection->getSelect()->getAdapter()
            ->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");
		
		$collection->getSelect()
            ->order("{$valueExpr} {$dir}");
			
		return $this;
		
	}


	/**
	 * Add sorting by group, and attribute sort order in atttribute set
	 * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection
	 * @param Mage_Eav_Model_Entity_Attribute_Set $attributeSet
	 */
	public function addAttributeSetFilterAndSort(
		Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection, 
		Mage_Eav_Model_Entity_Attribute_Set $attributeSet) {
		
		$select = $collection->getSelect();
		$adapter = $select->getAdapter();
		
		$collection->setAttributeSetFilter($attributeSet->getId());
		
		$condition = $adapter->quoteInto(
				"entity_attribute.attribute_group_id=attribute_group.attribute_group_id AND attribute_group.attribute_set_id=?", 
				$attributeSet->getId()
		);
		
		$select->join(
				array("attribute_group"=>$this->getTable('eav/attribute_group')),
				$condition,
				null
		);
		
		$select->order(array(
			"attribute_group.sort_order ASC", 
			"entity_attribute.sort_order ASC"
		));
		
	}
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor $vendor
	 * @return array
	 */
	public function getAttributeSetsForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
		
		$select = $this->getReadConnection()->select();
		
		$select->from(
				array("index"=>$this->getMainTable()), array()
		);
		$select->join(
				array("product"=>$this->getTable('catalog/product')),
				"product.entity_id=index.product_id",
				array()
		);
		$select->join(
				array("attribute_set"=>$this->getTable('eav/attribute_set')),
				"attribute_set.attribute_set_id=product.attribute_set_id",
				array("attribute_set_id","attribute_set_name")
		);
		
		$select->where("index.vendor_id=?", $vendor->getId());
		$select->distinct(true);
		$select->order("attribute_set_name");
		
		return  $this->getReadConnection()->fetchPairs($select);
	}

	/**
	 * Get Static Filters for Vendor
	 * 
	 * @param Unirgy_Dropship_Model_Vendor $vendor
	 * 
	 * @return array
	 */	
	public function getStaticFiltersForVendor(Unirgy_Dropship_Model_Vendor $vendor, $attributeSetId)
	{
		$select = $this->getReadConnection()->select();
		$setup = new Mage_Core_Model_Resource_Setup('core_setup');
		
		$select->from(
				array("index"=>$this->getMainTable()), array()
		);
		$select->join(
				array("product"=>$this->getTable('catalog/product')),
				"product.entity_id=index.product_id",
				array()
		);
		$select->join(
				array("product_attribute"=>$setup->getTable('catalog_product_entity_varchar')),
				"product_attribute.entity_id=product.entity_id",
				array("attribute_id","value")				
		);
		
		$select->join(
				array("attribute"=>$setup->getTable('catalog/eav_attribute')),
				"attribute.attribute_id=product_attribute.attribute_id",
				array()
		);
		
		$select->join(
				array("attribute_link"=>$setup->getTable('eav_entity_attribute')),
				"attribute_link.attribute_id=attribute.attribute_id",
				array()
		);		
		
		$select->join(
				array("attribute_eav"=>$setup->getTable('eav_attribute')),
				"attribute_eav.attribute_id=attribute.attribute_id",
				array("frontend_label")
		);
		
		$select->join(
				array("attribute_set"=>$this->getTable('eav/attribute_set')),
				"attribute_set.attribute_set_id=product.attribute_set_id",
				array()
		);		
		
		$select->where("index.vendor_id=?", $vendor->getId());
		$select->where("attribute.grid_permission=?", Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER);
		if ($attributeSetId) {
			$select->where("attribute_link.attribute_set_id=?", $attributeSetId);
			$select->where("attribute_set.attribute_set_id=?", $attributeSetId);
		}
		
		$select->distinct(true);
		$select->order(array("attribute_link.sort_order ASC"));
		$select->group(array(
			"attribute_id",
			"value"
			)
		);

		return $this->getReadConnection()->fetchAll($select);
	}
}