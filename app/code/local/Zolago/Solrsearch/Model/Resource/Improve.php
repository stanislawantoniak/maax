<?php
class Zolago_Solrsearch_Model_Resource_Improve extends Mage_Core_Model_Resource_Db_Abstract{
	
	const JOIN_STOCK = "stock";
	const JOIN_PRICE = "price";
	
	protected $_entity;
	
	/**
	 * Use link as main table
	 */
	protected function _construct() {
		$this->_init('catalog/product_super_link', 'link_id');
	}
	
	/**
	 * @param array|int $parentIds
	 * @return array (parenId=>array(child1, child2,...),...)
	 */
	public function getAllChildIds($parentIds) {
		if(!is_array($parentIds)){
			$parentIds = array($parentIds);
		}
		$select = $this->getReadConnection()->select();
		$select->from($this->getMainTable(), array("parent_id", "product_id"));
		$select->where("parent_id IN (?)", $parentIds);
		$out = array();
		foreach ($this->getReadConnection()->fetchAll($select) as $row){
			if(!isset($out[$row['parent_id']])){
				$out[$row['parent_id']] = array();
			}
			$out[$row['parent_id']][] = $row['product_id'];
		}
		return $out;
	}
	
	/**
	 * Load attributes data by n*query - n = num data table
	 * @param Varien_Data_Collection $collection
	 * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $attributesColleciton
	 * @param array $allIds
	 * @param int $storeId
	 * @return \Zolago_Solrsearch_Model_Ultility
	 */
	public function loadAttributesData(Varien_Data_Collection $collection,
			Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrbiuteCollection, 
			array $allIds, $storeId) {
		
		
		if (!$collection->count()) {
            return $this;
        }
		
		$attrIds = $attrbiuteCollection->getAllIds();

        $entity = $this->getEntity();

        $tableAttributes = array();
        $attributeTypes  = array();
		
		// Collect backend tables
        foreach ($attrbiuteCollection as $attributeId=>$attribute) {
			$attributeCode = $attribute->getAttributeCode();
            if (!$attributeId) {
                continue;
            }
            if ($attribute && !$attribute->isStatic()) {
                $tableAttributes[$attribute->getBackendTable()][] = $attributeId;
                if (!isset($attributeTypes[$attribute->getBackendTable()])) {
                    $attributeTypes[$attribute->getBackendTable()] = $attribute->getBackendType();
                }
            }
        }
		
		
        $selects = array();
        foreach ($tableAttributes as $table=>$attributes) {
            $select = $this->_getLoadAttributesSelect($allIds, $table, $attributes, $storeId);
            $selects[$attributeTypes[$table]][] = $this->_addLoadAttributesSelectValues(
                $select,
                $table,
                $attributeTypes[$table],
				$storeId
            );
        }
		
        $selectGroups = Mage::getResourceHelper('eav')->getLoadAttributesSelectGroups($selects);
		
		
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                try {
                    $select = implode(' UNION ALL ', $selects);
                    $values = $this->getReadConnection()->fetchAll($select);
                } catch (Exception $e) {
                    Mage::printException($e, $select);
                    throw $e;
                }

                foreach ($values as $value) {
                    $this->_setItemAttributeValue($collection, $attrbiuteCollection, $value);
                }
            }
        }
	}
	
	protected function _setItemAttributeValue(Varien_Data_Collection $collection, 
			Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrbiuteCollection,  $valueInfo)
    {
        $entityIdField  = $this->getEntity()->getEntityIdField();
        $entityId       = $valueInfo[$entityIdField];
		$item			= $collection->getItemByColumnValue($entityIdField, $entityId);
		$attributeCode  = null;
		
        if (!$item) {
            throw Mage::exception('Mage_Eav',
                Mage::helper('eav')->__('Data integrity: No header row found for attribute')
            );
        }
		
        $attribute = $attrbiuteCollection->getItemById($valueInfo['attribute_id']);
		/* @var $attribute Mage_Eav_Model_Entity_Attribute */
		
		if($attribute && $attribute->getId()){
			$attributeCode = $attribute->getAttributeCode();
		}
		
		
        if (!$attributeCode) {
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute(
                $this->getEntity()->getType(),
                $valueInfo['attribute_id']
            );
            $attributeCode = $attribute->getAttributeCode();
        }
		
		if($attributeCode){
			$item->setData($attributeCode, $valueInfo['value']);
			//$value = $attribute->getFrontend()->getValue($item);
			//$item->setData($attributeCode."_front", $value);
		}

        return $this;
    }
	
	/**
	 * @return Mage_Eav_Model_Entity_Abstract
	 */
	public function getEntity() {
		if(!$this->_entity){
			$this->_entity =  Mage::getModel('eav/entity')->setType(
				Mage_Catalog_Model_Product::ENTITY
			);
		}
		return $this->_entity;
	}
	
	/**
     * @param Varien_Db_Select $select
     * @param string $table
     * @param string $type
     * @return Varien_Db_Select
     */
    protected function _addLoadAttributesSelectValues($select, $table, $type, $storeId)
    {
        if ($storeId) {
            $helper = Mage::getResourceHelper('eav');
            $adapter        = $this->getReadConnection();
            $valueExpr      = $adapter->getCheckSql(
                't_s.value_id IS NULL',
                $helper->prepareEavAttributeValue('t_d.value', $type),
                $helper->prepareEavAttributeValue('t_s.value', $type)
            );

            $select->columns(array(
                'default_value' => $helper->prepareEavAttributeValue('t_d.value', $type),
                'store_value'   => $helper->prepareEavAttributeValue('t_s.value', $type),
                'value'         => $valueExpr
            ));
        } else {
            $helper = Mage::getResourceHelper('eav');
			$select->columns(array(
				'value' => $helper->prepareEavAttributeValue($table. '.value', $type),
			));
        }
        return $select;
    }
	
	public function getDefaultStoreId() {
		return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
	}
	
    protected function _getLoadAttributesSelect($allIds, $table, array $attributeIds, $storeId = null)
    {	
        if ($storeId) {
            $adapter        = $this->getReadConnection();
            $entityIdField  = $this->getEntity()->getEntityIdField();
            $joinCondition  = array(
                't_s.attribute_id = t_d.attribute_id',
                't_s.entity_id = t_d.entity_id',
                $adapter->quoteInto('t_s.store_id = ?', $storeId)
            );
            $select = $adapter->select()
                ->from(array('t_d' => $table), array($entityIdField, 'attribute_id'))
                ->joinLeft(
                    array('t_s' => $table),
                    implode(' AND ', $joinCondition),
                    array())
                ->where('t_d.entity_type_id = ?', $this->getEntity()->getTypeId())
                ->where("t_d.{$entityIdField} IN (?)", $allIds)
                ->where('t_d.attribute_id IN (?)', $attributeIds)
                ->where('t_d.store_id = ?', 0);
        } else {
			$helper = Mage::getResourceHelper('eav');
			$entityIdField = $this->getEntity()->getEntityIdField();
			$select = $this->getConnection()->select()
				->from($table, array($entityIdField, 'attribute_id'))
				->where('entity_type_id =?', $this->getEntity()->getTypeId())
				->where("$entityIdField IN (?)", $allIds)
				->where('attribute_id IN (?)', $attributeIds);
            $select->where('store_id = ?', $this->getDefaultStoreId());
        }
        return $select;
    }
	
	/**
	 * @param int $storeId
	 * @param array $allIds
	 * @param array $extraJoins
	 * @return array
	 */
	public function getFlatProducts($storeId, array $allIds = array(), array $extraJoins = array()) {
		$store = Mage::app()->getStore($storeId);
		$websiteId = $store->getWebsiteId();
		$adapter = $this->getReadConnection();
		$select =  $adapter->select();
		$select->from(array(
			"product" => $this->getTable('catalog/product')
		));
		// Store & visability filter
		$joinCond = array(
			"product_category.product_id=product.entity_id",
			$adapter->quoteInto("product_category.store_id=?", $storeId),
			$adapter->quoteInto("product_category.category_id=?", $store->getRootCategoryId()),
			/*$adapter->quoteInto("product_category.visibility<>?", Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)*/	
		);
		$select->join(
				array("product_category"=>$this->getTable('catalog/category_product_index')),
				implode(" AND ", $joinCond),
				array()
		);
		
		$select->where("product.entity_id IN (?)", $allIds);
		
		// No joins just return
		if(!$extraJoins){
			return $adapter->fetchAll($select);
		}
		
		// Join stocks index. 
		// @spike Mayby from regular table?
		if(isset($extraJoins[self::JOIN_STOCK])){
			$joinCond = array(
				"stock_item.product_id=product.entity_id",
				$adapter->quoteInto("stock_item.website_id=?", $websiteId),
			);
			$select->joinLeft(
					array("stock_item"=>$this->getTable("cataloginventory/stock_status_indexer_idx")), 
					implode(" AND ", $joinCond),
					array("qty", "stock_status")
			);
		}
		// Join price data
		if(isset($extraJoins[self::JOIN_PRICE])){
			$joinCond = array(
				'price_index.entity_id = product.entity_id',
				$adapter->quoteInto('price_index.website_id = ?', $websiteId),
				// Default not logged in
				$adapter->quoteInto('price_index.customer_group_id = ?', Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
			);
			
			$least = $adapter->getLeastSql(
				array('price_index.min_price', 'price_index.tier_price')
			);
            $minimalExpr = $adapter->getCheckSql(
					'price_index.tier_price IS NOT NULL', $least, 'price_index.min_price'
			);
			
            $colls = array(
				'price', 
				'tax_class_id', 
				'final_price',
                'minimal_price' => $minimalExpr , 
				'min_price', 
				'max_price', 
				'tier_price'
			);
			
            $tableName = array('price_index' => $this->getTable('catalog/product_index_price'));
			
            $select->joinLeft($tableName, implode(' AND ', $joinCond), $colls);
		}
		
		return $adapter->fetchAll($select);
	}
	
}