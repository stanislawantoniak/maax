<?php

class Zolago_Catalog_Model_Resource_Category extends Mage_Catalog_Model_Resource_Category
{
    protected $_relatedCategoryAttributeId;
    const CACHE_NAME = 'RESOURCE_CATEGORY';

    /**
     * Get helper for category cache
     *
     * @return Zolago_Modago_Helper_Category
     */
    public function getCategoryCacheHelper() {
        /** @var Zolago_Modago_Helper_Category $helper */
        $helper = Mage::helper("zolagomodago/category");
        return $helper;
    }

    /**
     * Get unified prefix for this object
     *
     * @param $name
     * @return string
     */
    public function getCacheKeyPrefix($name) {
        return $this->getCategoryCacheHelper()->getPrefix(self::CACHE_NAME. '_' .$name);
    }

    /**
     * Load from cache by key
     *
     * @param string $key
     * @param bool $unserialize
     * @return false | mixed | string
     */
    protected function _loadFromCache($key, $unserialize = true) {
        return $this->getCategoryCacheHelper()->loadFromCache($key, $unserialize);
    }

    /**
     * Save to cache by key
     * Data will be serialized
     *
     * @param string $key
     * @param array $data
     */
    protected function _saveInCache($key, $data) {
        $this->getCategoryCacheHelper()->_saveInCache($key, $data);
    }

    /**
     * Check whether to use cache for category cache
     *
     * @return bool
     */
    public function canUseCache() {
        return $this->getCategoryCacheHelper()->useCache();
    }

    /**
     * Load and return parentNode
     *
     * @param $nodeId
     * @return Varien_Data_Tree_Node
     */
    public function getParentNode($nodeId) {
        /* @var $tree Mage_Catalog_Model_Resource_Category_Tree */
        $tree = Mage::getResourceModel('catalog/category_tree');
        return $tree->loadNode($nodeId);
    }

    /**
     * Return children ids of category
     *
     * @param Mage_Catalog_Model_Category $category
     * @param boolean $recursive
     * @return array
     */
    public function getChildren($category, $recursive = true) {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::getChildren($category, $recursive);
        }

        $attributeId  = (int)$this->_getIsActiveAttributeId();
        $backendTable = $this->getTable(array($this->getEntityTablePrefix(), 'int'));
        $adapter      = $this->_getReadAdapter();
        $checkSql     = $adapter->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $bind = array(
            'attribute_id' => $attributeId,
            'store_id'     => $category->getStoreId(),
            'scope'        => 1,
        );
        $select = $this->_getChildrenIdSelect($category, $recursive);
        $select
            ->joinLeft(
                array('d' => $backendTable),
                'd.attribute_id = :attribute_id AND d.store_id = 0 AND d.entity_id = m.entity_id',
                array()
            )
            ->joinLeft(
                array('c' => $backendTable),
                'c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.entity_id = m.entity_id',
                array()
            )
            ->where($checkSql . ' = :scope');

        $cacheKey = $this->getCacheKeyPrefix("getChildren_") . md5((string)$select);

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            return $this->_isActiveAttributeId = $cacheData;
        }

        $data = $adapter->fetchCol($select, $bind);

        $this->_saveInCache($cacheKey, $data);

        return $data;
    }

	/**
	 * get related ids of category list
	 * Id $asKeyValue = true then pairs like
	 * array(<category_id> => <related_category_id>)
	 *
	 * @param array $ids
	 * @param bool $asKeyValue
	 * @return array
	 */
	public function getRelatedIds($ids, $asKeyValue = false) {
		$attributeId = $this->_getRelatedCategoryAttributeId();

		$adapter = $this->getReadConnection();
		$select = $adapter->select();

		$cols = array('attribute.value');
		if ($asKeyValue) $cols = array('entity_id', 'attribute.value');

		$select->from(
			array('attribute' => $this->getTable('catalog_category_entity_int')),
			$cols
		)
			->where('attribute.value IS NOT NULL')
			->where('attribute.value > 0')
			->where('attribute.attribute_id = ?', $attributeId)
			->where('entity_id IN (?)', $ids)
			->distinct();

		if ($asKeyValue) return $adapter->fetchPairs($select);
		return $adapter->fetchCol($select);
	}
	
    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    protected function _getIsActiveAttributeId() {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::_getIsActiveAttributeId();
        }

        $cacheKey = $this->getCacheKeyPrefix("_getIsActiveAttributeId");

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            return $this->_isActiveAttributeId = $cacheData;
        }

        if ($this->_isActiveAttributeId === null) {
            $bind = array(
                'catalog_category' => Mage_Catalog_Model_Category::ENTITY,
                'is_active'        => 'is_active',
            );
            $select = $this->_getReadAdapter()->select()
                ->from(array('a'=>$this->getTable('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>$this->getTable('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = :catalog_category')
                ->where('attribute_code = :is_active');

            $this->_isActiveAttributeId = $this->_getReadAdapter()->fetchOne($select, $bind);

            $this->_saveInCache($cacheKey, $this->_isActiveAttributeId);
        }

        return $this->_isActiveAttributeId;
    }
    
    /**
     * Get 'related_category' attribute identifier
     *
     * @return int
     */
    protected function _getRelatedCategoryAttributeId() {
        $cacheKey = $this->getCacheKeyPrefix("_getRelatedCategoryAttributeId");

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            return $this->_relatedCategoryAttributeId = $cacheData;
        }

        if ($this->_relatedCategoryAttributeId === null) {
            $bind = array(
                'catalog_category' => Mage_Catalog_Model_Category::ENTITY,
                'related_category'        => 'related_category',
            );
            $select = $this->_getReadAdapter()->select()
                ->from(array('a'=>$this->getTable('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>$this->getTable('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = :catalog_category')
                ->where('attribute_code = :related_category');

            $this->_relatedCategoryAttributeId = $this->_getReadAdapter()->fetchOne($select, $bind);

            $this->_saveInCache($cacheKey, $this->_relatedCategoryAttributeId);
        }

        return $this->_relatedCategoryAttributeId;
    }

}