<?php

class Zolago_Catalog_Model_Resource_Vendor_Mass 
	extends Mage_Core_Model_Resource_Db_Abstract{
	
	protected $_options = array();
	
	protected function _construct() {
		$this->_init("udropship/vendor_product_assoc", null);
	}

	/**
	 * Process value-add action to multiple attribute
	 * @param array $productIds
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @param array $valuesToAdd
	 * @param Mage_Core_Model_Store $store
	 * @return Zolago_Catalog_Model_Resource_Vendor_Mass
	 * 
	 * @todo optymalize performence via collect same attribute vale and product ids
	 */
	public function addValueToMultipleAttribute(
			array $productIds, 
			Mage_Catalog_Model_Resource_Eav_Attribute $attribute, 
			array $valuesToAdd, 
			Mage_Core_Model_Store $store,
			$mode="add") {
		
		if(!count($valuesToAdd) ||
		   !($attribute->getBackend() instanceof Mage_Eav_Model_Entity_Attribute_Backend_Array) || 
		   !$attribute->getBackendTable()){
			
			return $this;
		}
		
		$write = $this->_getWriteAdapter();
		$backend = $attribute->getBackendTable();
		$select = $this->getReadConnection()->select();
		
		$select->from($backend, array("entity_id", "value"));
		$select->where("entity_id IN (?)", $productIds);
		$select->where("entity_type_id=?", $attribute->getEntityTypeId());
		$select->where("attribute_id=?", $attribute->getId());
		$select->where("store_id=?", $store->getId());
		
		$insert = array();
		
		$dbValues  = $this->getReadConnection()->fetchPairs($select);
		$indexSameValues = $indexMixedValues = array();
		
		foreach($productIds as $productId){
			$item = array(
				"entity_type_id"=>$attribute->getEntityTypeId(),
				"attribute_id"	=> $attribute->getId(),
				"store_id"		=> $store->getId(),
				"entity_id"		=> $productId
			);
			if($dbValues && isset($dbValues[$productId])){
				// Some values in dob
				if($mode=="add"){ // Add values
					$item['value'] = $this->_joinValues($attribute, $dbValues[$productId], $valuesToAdd);
				}elseif($mode=="sub"){ // Substract Values
					$item['value'] = $this->_substractValues($attribute, $dbValues[$productId], $valuesToAdd);
				}
				$indexMixedValues[$productId] = $item['value'];
			}else{
				if($mode=="add"){
					// New values
					$item['value'] = implode(",", $this->_sortOptions($attribute, $valuesToAdd));
					$indexSameValues[] = $productId;
				}elseif($mode=="sub"){
					// Clear values
					$item['value'] = "";
					$indexSameValues[] = $productId;
				}
			}
			$insert[]=$item;
		}
		$write->insertOnDuplicate($backend, $insert, array("value"));
		
		
		
		/*
		// Reindex changed values - each value can be different
		// @todo Need to be reindex faster!
		foreach($indexMixedValues as $productId=>$value){
			$object = new Varien_Object(array(
				'product_ids'       => array($productId),
				'attributes_data'   => array($attribute->getAttributeCode()=>$value),
				'store_id'          => $store->getId()
			));
			Mage::getSingleton('index/indexer')->processEntityAction(
				$object, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
			);
		}
		// Reindex new (same) values as one event
		$object = new Varien_Object(array(
			'product_ids'       => array_unique($indexSameValues),
			'attributes_data'   => array($attribute->getAttributeCode()=>$valuesToAdd),
			'store_id'          => $store->getId()
		));
		
		Mage::getSingleton('index/indexer')->processEntityAction(
			$object, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
		);
		*/
		
		// Better performance
		$indexer = Mage::getResourceModel('catalog/product_indexer_eav_source');
		/* @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source */
		$indexer->reindexEntities($productIds);
		
		return $this;
	}
	
	/**
	 * @param array $str
	 * @param array $newValues
	 * @return string
	 */
	protected function _joinValues(Mage_Catalog_Model_Resource_Eav_Attribute $attribute, $str, array $newValues) {
		$result = array_unique(array_merge(explode(",", $str),$newValues));
		$this->_sortOptions($attribute, $result);
		return implode(",", $result);
	}
	
	/**
	 * @param array $str
	 * @param array $newValues
	 * @return string
	 */
	protected function _substractValues(Mage_Catalog_Model_Resource_Eav_Attribute $attribute, $str, array $newValues) {
		$result = array_unique(array_diff(explode(",", $str),$newValues));
		$this->_sortOptions($attribute, $result);
		return implode(",", $result);
	}
	
	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attrbiute
	 * @param array $result
	 */
	protected function _sortOptions(Mage_Catalog_Model_Resource_Eav_Attribute $attrbiute, array &$result) {
		$optionSort = $this->_getOptionSort($attrbiute);
		$_result = $result;
		$result = array();
		foreach($this->_getOptionSort($attrbiute) as $key=>$sortOrder){
			 if(($index=array_search($key, $_result))!==false){
				$result[] = $_result[$index];
				unset($_result[$index]);
			 }
		}
		foreach($_result as $optionId){
			$result[] = $optionId;
		}
		return $result;
	}
	
	protected function _getOptionSort(Mage_Catalog_Model_Resource_Eav_Attribute $attribute) {
		$attributeId = $attribute->getId();
		if(!isset($this->_options[$attributeId])){
			$this->_options[$attributeId] = array();
			foreach($attribute->getSource()->getAllOptions(false) as $opt){
				$this->_options[$attribute->getId()][$opt['value']] = $opt['label'];
			}
		}
		return $this->_options[$attribute->getId()];
	}
	
	/**
	 * Fixation of displaying values of attribute - wee need orign values
	 * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attrbiute
	 * @param stinrg $dir
	 */
	public function addEavTableSortToCollection(
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

		// the trick is swap attribute code name to do not override origin atttribute value
		// w can keep origin value from eav no text value
		$oldCode = $attrbiute->getAttributeCode();
		$attrbiute->setAttributeCode($oldCode."_filter");
			
        Mage::getResourceModel('eav/entity_attribute_option')
            ->addOptionValueToCollection($collection, $attrbiute, $valueExpr);
		

        $collection->getSelect()
            ->order("{$attrbiute->getAttributeCode()} {$dir}");
			
		$attrbiute->setAttributeCode($oldCode);
		
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
		
		/**
		 * @todo better performance if we can recogenize isnt default values
		 */
	
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
		
		// Sort by comma num first
		$collection->getSelect()->order("{$sortAttribute} {$dir}");
			
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
			"additional_table.column_attribute_order ASC", /* Custom field */
			"attribute_group.sort_order ASC", 
			"entity_attribute.sort_order ASC"
		));
		
	}
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
	 * @return array
	 */
	public function getAttributeSetsForVendor(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
		
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
	 * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
	 * 
	 * @return array
	 */	
	public function getStaticFiltersForVendor(ZolagoOs_OmniChannel_Model_Vendor $vendor, $attributeSetId)
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
				array("sortOrder" => "sort_order")
		);

		$select->join(
				array("attribute_eav"=>$setup->getTable('eav_attribute')),
				"attribute_eav.attribute_id=attribute.attribute_id",
				array(
					"label"	=> "frontend_label",
					"code"	=> "attribute_code"
				)
		);

		$select->join(
				array("attribute_set"=>$this->getTable('eav/attribute_set')),
				"attribute_set.attribute_set_id=product.attribute_set_id",
				array()
		);

		$select->join(
				array("attribute_group"=>$this->getTable('eav/attribute_group')),
				"attribute_group.attribute_group_id=attribute_link.attribute_group_id",
				array(
					"group"			=> "attribute_group_name",
					"groupOrder"	=> "sort_order"
				)
		);

		$select->where("index.vendor_id=?", $vendor->getId());
		$select->where("attribute.grid_permission=?", Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER);
		if ($attributeSetId) {
			$select->where("attribute_link.attribute_set_id=?", $attributeSetId);
			$select->where("attribute_set.attribute_set_id=?", $attributeSetId);
		}

		$select->distinct(true);
		$select->order(array("attribute_group.sort_order ASC", "attribute_link.sort_order ASC"));
		$select->group(array(
			"attribute_id",
			"value"
			)
		);

		return $this->getReadConnection()->fetchAll($select);
	}
	
	/**
	 * Get Static Filters for Vendor
	 * 
	 * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
	 * 
	 * @return array
	 */

    public function getStaticDropdownFiltersForVendorProductAssoc(ZolagoOs_OmniChannel_Model_Vendor $vendor, $attributeSetId)
    {

        $select = $this->getReadConnection()->select();
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');

        $select->from(
            array("index"=>"udropship_vendor_product_assoc"), array()
        );
        $select->join(
            array("product"=>$this->getTable('catalog/product')),
            "product.entity_id=index.product_id",
            array()
        );
        $select->join(
            array("attribute_set"=>$this->getTable('eav/attribute_set')),
            "attribute_set.attribute_set_id=product.attribute_set_id",
            array()
        );
        $select->join(
            array("product_attribute"=>$setup->getTable('catalog_product_entity_int')),
            "product_attribute.entity_id=product.entity_id",
            array("attribute_id", "value" , "option" => "value")
        );

        $select->join(
            array("attribute"=>$setup->getTable('catalog/eav_attribute')),
            "attribute.attribute_id=product_attribute.attribute_id",
            array()
        );

        $select->join(
            array("attribute_link"=>$setup->getTable('eav_entity_attribute')),
            "attribute_link.attribute_id=attribute.attribute_id",
            array("sortOrder" => "sort_order")
        );

        $select->join(
            array("attribute_eav"=>$setup->getTable('eav_attribute')),
            "attribute_eav.attribute_id=attribute.attribute_id",
            array(
                "label"	=> "frontend_label",
                "code"	=> "attribute_code"
            )
        );
        $select->join(
            array("attribute_group"=>$this->getTable('eav/attribute_group')),
            "attribute_group.attribute_group_id=attribute_link.attribute_group_id",
            array(
                "group"			=> "attribute_group_name",
                "groupOrder"	=> "sort_order"
            )
        );


        $select->where("product.attribute_set_id=?", $attributeSetId);

        $select->where("index.vendor_id=?", $vendor->getId());
        $select->where("attribute.grid_permission=?", Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER);
        $select->where("attribute_link.attribute_set_id=?", $attributeSetId);

        $select->distinct(true);
        $select->order(array("attribute_group.sort_order ASC", "attribute_link.sort_order ASC"));
        $select->group(array(
                "attribute_id",
                "option"
            )
        );

        return $this->getReadConnection()->fetchAll($select);
    }
	public function getOptionLabelbyId($attributeId, $labelId, $storeId = 0)
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
				array("product_attribute"=>$setup->getTable('catalog_product_entity_int')),
				"product_attribute.entity_id=product.entity_id",
				array()
		);
		
		$select->join(
				array("eav_attribute_option"=>$setup->getTable('eav_attribute_option')),
				"eav_attribute_option.attribute_id=product_attribute.attribute_id",
				array()
		);

		$select->join(
				array("eav_attribute_option_value"=>$setup->getTable('eav_attribute_option_value')),
				"eav_attribute_option_value.option_id=eav_attribute_option.option_id",
				array("value")
		);
        $select->where("eav_attribute_option.attribute_id=?", $attributeId);
		$select->where("eav_attribute_option_value.option_id=?", $labelId);
		$select->where("eav_attribute_option_value.store_id=?", $storeId);
		$select->distinct(true);
		$select->order(array("eav_attribute_option_value.option_id ASC"));

		return $this->getReadConnection()->fetchOne($select);
	}
}