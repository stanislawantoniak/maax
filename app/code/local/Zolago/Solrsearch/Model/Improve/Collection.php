<?php

class Zolago_Solrsearch_Model_Improve_Collection extends Varien_Data_Collection {
	
	protected $_indexed = array(
		"category"	=> array(),
		"regular"	=> array(),
		"parent"    => array()	
	);
	protected $_indexedHistogram = array(
		"category"	=> array(),
		"regular"	=> array(),
		"parent"    => array()	
	);
	
	protected $_handledAttributes = array();
	
	/**
	 * @param Mage_Eav_Model_Entity_Abstract $attributeObject
	 */
	public function addHandledAttribute(Mage_Eav_Model_Entity_Abstract $attributeObject) {
		$this->_handledAttributes[$attributeObject->getAttributeCode()] = $attributeObject;
	}
	
	/** 
	 * @param string|Mage_Eav_Model_Entity_Abstract $attribute
	 * @return bool
	 */
	public function isAttributeHandled($attribute) {
		if($attribute instanceof Mage_Eav_Model_Entity_Abstract){
			$attribute = $attribute->getAttributesByCode();
		}
		return isset($this->_handledAttributes[$attribute]);
	}
	
	/**
	 * @param array $items
	 * @return Zolago_Solrsearch_Model_Improve_Collection
	 */
	public function setCategoryIds(array $items) {
		$this->_indexed['category'] = $items;
		foreach($items as $id){
			$this->_indexedHistogram['category'][$id] = true;
		}
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getCategoryIds() {
		return $this->_indexed['category'];
	}
	
	/**
	 * @param type $item
	 * @return bool
	 */
	public function isCategoryItem($item) {
		return isset($this->_indexedHistogram['category'][$item->getId()]);
	}
	
	/**
	 * @param array $items
	 * @return Zolago_Solrsearch_Model_Improve_Collection
	 */
	public function setRegularIds(array $items) {
		$this->_indexed['regular'] = $items;
		foreach($items as $id){
			$this->_indexedHistogram['regular'][$id] = true;
		}
		return $this;
	}


	
	/**
	 * @return array
	 */
	public function getRegularIds() {
		return $this->_indexed['regular'];
	}
	
	/**
	 * @param type $item
	 * @return bool
	 */
	public function isReagularItem($item) {
		return isset($this->_indexedHistogram['regular'][$item->getId()]);
	}
	
	/**
	 * @param array $items
	 * @return Zolago_Solrsearch_Model_Improve_Collection
	 */
	public function setParentIds(array $items) {
		$this->_indexed['parent'] = $items;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getParentIds() {
		return $this->_indexed['parent'];
	}
	
	/**
	 * @param type $item
	 * @return bool
	 */
	public function isParentItem($item) {
		return isset($this->_indexed['parent'][$item->getId()]);
	}
}