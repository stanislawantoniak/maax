<?php

class Zolago_Catalog_Model_Resource_Vendor_Price
	extends Mage_Core_Model_Resource_Db_Abstract{
	
	protected $_options = array();
	
	protected $_attributeLabelCache = array();
	protected $_optionLabelCache = array();
	
	protected function _construct() {
		$this->_init("catalog/product", null);
	}

	
	/**
	 * @param array $ids
	 * @return array
	 */
	public function getDetails($ids=array(), $storeId) {
		
		$out = array();
		
		$adapter = $this->getReadConnection();
		$baseSelect = $adapter->select();
		
		$baseSelect->from(array("product"=>$this->getMainTable()));
		$baseSelect->where("product.entity_id IN (?)", $ids);
		
		foreach($adapter->fetchAll($baseSelect) as $row){
			$out[$row['entity_id']] = array_merge($row, array(
				"var" => rand(0,10000),
			));
		}
		
		foreach($this->_getChilds($ids, $storeId) as $child){
			if(!isset($out[$child['parent_id']]['children'][$child['attribute_id']])){
				$out[$child['parent_id']]['children'][$child['attribute_id']] = array(
					"children"		=> array(),
					"label"			=> $this->getAttributeLabel($child['attribute_id'], $storeId),
					"attribute_id"	=> $child['attribute_id']
				);
			}
			
			$child['option_text'] = $this->getAttributeOption($child['value'], $child['attribute_id'], $storeId);
			
			$out[$child['parent_id']]['children'][$child['attribute_id']]['children'][]=$child;
		}
		
		foreach ($out as &$item){
			if(isset($item['children'])){
				$item['children'] = array_values($item['children']);
			}
		}
		return array_values($out);
	}
	
	/**
	 * @param int $attributeId
	 * @param int $storeId
	 * @return string
	 */
	protected function getAttributeLabel($attributeId, $storeId) {
		if(!isset($this->_attributeLabelCache[$storeId][$attributeId])){
			$attribute = Mage::getSingleton('eav/config')->
				getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeId);
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$this->_attributeLabelCache[$storeId][$attributeId] = $attribute->getStoreLabel($storeId);
		}
		return $this->_attributeLabelCache[$storeId][$attributeId];
	}
	
	/**
	 * @param int $attributeId
	 * @param int $storeId
	 * @return string
	 */
	protected function getAttributeOption($optionId, $attributeId, $storeId) {
		if(!isset($this->_optionLabelCache[$storeId][$attributeId][$optionId])){
			$attribute = Mage::getSingleton('eav/config')->
				getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeId)->
				setStoreId($storeId);
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$this->_optionLabelCache[$storeId][$attributeId][$optionId] = $attribute->
					getSource()->getOptionText($optionId);
		}
		return $this->_optionLabelCache[$storeId][$attributeId][$optionId];
	}
	
	
	/**
	 * @param array $ids
	 * @param int $storeId
	 * @return type
	 */
	protected function _getChilds(array $ids, $storeId) {
		// 
		$select = $this->getReadConnection()->select();
		$select->from(
			array("link"=>$this->getTable("catalog/product_super_link")),
			array("parent_id", "product_id")
		);
		
		// Add configurable attribute
		$select->join(
			array("sa"=>$this->getTable("catalog/product_super_attribute")),
			"sa.product_id=link.parent_id",
			array("attribute_id")
		);
		
		// Add values of attributes
		$select->join(
			array("product_int"=>$this->getValueTable("catalog/product", "int")),
			"product_int.entity_id=link.product_id AND product_int.attribute_id=sa.attribute_id",
			array("value")
		);
		
		// Add stock
		$select->join(
			array("stock"=>$this->getTable("cataloginventory/stock_item")),
			"stock.product_id=link.product_id AND stock.stock_id=" . Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID,
			array("is_in_stock", "qty")
		);
				
		// Add optional pricing
		$select->joinLeft(
			array("sa_price"=>$this->getTable("catalog/product_super_attribute_pricing")),
			"sa_price.product_super_attribute_id=sa.product_super_attribute_id AND sa_price.value_index=product_int.value",
			array()
		);
		
		// Optional price
		$select->columns(array("price"=>new Zend_Db_Expr("IF(sa_price.value_id>0, sa_price.pricing_value, 0)")));

		$select->where("link.parent_id IN (?)", $ids);
		$select->order("sa.position");
		
		return $this->getReadConnection()->fetchAll($select);
	}
}