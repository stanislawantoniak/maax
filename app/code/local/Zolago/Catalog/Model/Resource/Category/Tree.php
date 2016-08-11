<?php

class Zolago_Catalog_Model_Resource_Category_Tree extends Mage_Catalog_Model_Resource_Category_Tree
{
    const CACHE_NAME = 'RESOURCE_CATEGORY_TREE';

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
     * Build unique cache key for category tree
     *
     * @param null $parentNode
     * @param int $recursionLevel
     * @return string
     */
    protected function _getCacheKey($parentNode=null, $recursionLevel = 0) {

        $prefix = $this->getCacheKeyPrefix('load_');
        $id = 'null';
        if ($parentNode instanceof Varien_Data_Tree_Node) {
            $id = $parentNode->getId();
        } else if (is_numeric($parentNode)) {
            $id = $parentNode;
        } else if (is_string($parentNode)) {
            // Probably never used by magento
            $id = $parentNode;
        }
        return $prefix . $id . $recursionLevel;
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
     * Load tree
     *
     * @param int|Varien_Data_Tree_Node $parentNode
     * @param int $recursionLevel
     * @return Zolago_Catalog_Model_Resource_Category_Tree
     */
    public function load($parentNode=null, $recursionLevel = 0)
    {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::load($parentNode, $recursionLevel);
        }

        if (!$this->_loaded) {

            $cacheKey = $this->_getCacheKey($parentNode, $recursionLevel);
            if($cacheData = $this->_loadFromCache($cacheKey)) {
                $childrenItems = $cacheData["children_items"];
                $parentPath    = $cacheData["parent_path"];
                $parentNode    = isset($cacheData["parent_node"]) ? $cacheData["parent_node"] : $parentNode;
                $this->addChildNodes($childrenItems, $parentPath, $parentNode);
                $this->_loaded = true;
                return $this;
            }

            $startLevel = 1;
            $parentPath = '';

            if ($parentNode instanceof Varien_Data_Tree_Node) {
                $parentPath = $parentNode->getData($this->_pathField);
                $startLevel = $parentNode->getData($this->_levelField);
            } else if (is_numeric($parentNode)) {
                $select = $this->_conn->select()
                    ->from($this->_table, array($this->_pathField, $this->_levelField))
                    ->where("{$this->_idField} = ?", $parentNode);
                $parent = $this->_conn->fetchRow($select);

                $startLevel = $parent[$this->_levelField];
                $parentPath = $parent[$this->_pathField];
                $parentNode = null;
            } else if (is_string($parentNode)) { // Probably never used
                $parentPath = $parentNode;
                $startLevel = count(explode('/',$parentPath))-1; // Fixed bug anyway
                $parentNode = null;
            }

            $select = clone $this->_select;

            $select->order($this->_table . '.' . $this->_orderField . ' ASC');
            if ($parentPath) {
                $pathField = $this->_conn->quoteIdentifier(array($this->_table, $this->_pathField));
                $select->where("{$pathField} LIKE ?", "{$parentPath}/%");
            }
            if ($recursionLevel != 0) {
                $levelField = $this->_conn->quoteIdentifier(array($this->_table, $this->_levelField));
                $select->where("{$levelField} <= ?", $startLevel + $recursionLevel);
            }

            $arrNodes = $this->_conn->fetchAll($select);

            $childrenItems = array();

            foreach ($arrNodes as $nodeInfo) {
                $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
                array_pop($pathToParent);
                $pathToParent = implode('/', $pathToParent);
                $childrenItems[$pathToParent][] = $nodeInfo;
            }

            $this->addChildNodes($childrenItems, $parentPath, $parentNode);
            $this->_loaded = true;

            // Save child nodes data
            $cacheData = array(
                "children_items"    => $childrenItems,
                "parent_path"       => $parentPath
            );
            if (is_null($parentNode)) {
                $cacheData["parent_node"] = $parentNode;
            }
            $this->_saveInCache($cacheKey, $cacheData);
        }

        return $this;
    }

    /**
     * Process tree nodes with collection
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @param boolean $sorted
     * @param array $exclude
     * @param boolean $toLoad
     * @param boolean $onlyActive
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    public function addCollectionData($collection = null, $sorted = false, $exclude = array(), $toLoad = true, $onlyActive = false)
    {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::addCollectionData($collection, $sorted, $exclude, $toLoad, $onlyActive);
        }

        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        $nodeIds = array();
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }

        // Cache for default collection
        if ($toLoad && is_null($collection)) {
            $cacheKey = $this->getCacheKeyPrefix('addCollectionData_') . md5(implode('_', $nodeIds).'_'.(int)$sorted.'_'.(int)$onlyActive);
            if ($cacheData = $this->_loadFromCache($cacheKey)) {

                foreach ($cacheData as $categoryData) {
                    if ($node = $this->getNodeById($categoryData["entity_id"])) {
                        $node->addData($categoryData);
                    }
                }

                foreach ($this->getNodes() as $node) {
                    if (!isset($cacheData[$node->getId()]) && $node->getParent()) {
                        $this->removeNode($node);
                    }
                }
                return $this;
            }
        }

        // Custom collection flag
        $_collectionFlag = is_null($collection) ? 0 : 1; // To make sure category cache works for custom collection
        if (is_null($collection)) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {

            $disabledIds = $this->_getDisabledIds($collection);
            if ($disabledIds) {
                $collection->addFieldToFilter('entity_id', array('nin' => $disabledIds));
            }
            $collection->addAttributeToFilter('is_active', 1);
            $collection->addAttributeToFilter('include_in_menu', 1);
        }

        if ($this->_joinUrlRewriteIntoCollection) {
            $collection->joinUrlRewrite();
            $this->_joinUrlRewriteIntoCollection = false;
        }

        if ($toLoad) {

            if ($_collectionFlag) {
                // Custom collection can have other conditions
                $cacheKey = $this->getCacheKeyPrefix('addCollectionData_custom_collection_') . md5((string)$collection->getSelect());
                if ($cacheData = $this->_loadFromCache($cacheKey)) {

                    foreach ($cacheData as $categoryData) {
                        if ($node = $this->getNodeById($categoryData["entity_id"])) {
                            $node->addData($categoryData);
                        }
                    }

                    foreach ($this->getNodes() as $node) {
                        if (!isset($cacheData[$node->getId()]) && $node->getParent()) {
                            $this->removeNode($node);
                        }
                    }
                    return $this;
                }
            }

            $collection->load();
            $cacheData = array();

            foreach ($collection as $category) {
                if ($node = $this->getNodeById($category->getId())) {
                    $node->addData($category->getData());
                    $cacheData[$category->getId()] = $category->getData();
                }
            }

            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }

            $this->_saveInCache($cacheKey, $cacheData);
        }

        return $this;
    }

    /**
     * Returns attribute id for attribute "is_active"
     *
     * @return int
     */
    protected function _getIsActiveAttributeId()
    {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::_getIsActiveAttributeId();
        }

        $cacheKey = $this->getCacheKeyPrefix("_getIsActiveAttributeId");

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            return $this->_isActiveAttributeId = $cacheData;
        }

        $resource = Mage::getSingleton('core/resource');
        if (is_null($this->_isActiveAttributeId)) {
            $bind = array(
                'entity_type_code' => Mage_Catalog_Model_Category::ENTITY,
                'attribute_code'   => 'is_active'
            );
            $select = $this->_conn->select()
                ->from(array('a'=>$resource->getTableName('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>$resource->getTableName('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = :entity_type_code')
                ->where('attribute_code = :attribute_code');

            $this->_isActiveAttributeId = $this->_conn->fetchOne($select, $bind);

            $this->_saveInCache($cacheKey, $this->_isActiveAttributeId);
        }
        return $this->_isActiveAttributeId;
    }

    /**
     * Retrieve inactive category item ids
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @param int $storeId
     * @return array
     */
    protected function _getInactiveItemIds($collection, $storeId)
    {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::_getInactiveItemIds($collection, $storeId);
        }

        $filter = $collection->getAllIdsSql();
        $attributeId = $this->_getIsActiveAttributeId();

        $conditionSql = $this->_conn->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $table = Mage::getSingleton('core/resource')->getTableName(array('catalog/category', 'int'));
        $bind = array(
            'attribute_id' => $attributeId,
            'store_id'     => $storeId,
            'zero_store_id'=> 0,
            'cond'         => 0,

        );
        $select = $this->_conn->select()
            ->from(array('d'=>$table), array('d.entity_id'))
            ->where('d.attribute_id = :attribute_id')
            ->where('d.store_id = :zero_store_id')
            ->where('d.entity_id IN (?)', new Zend_Db_Expr($filter))
            ->joinLeft(
                array('c'=>$table),
                'c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.entity_id = d.entity_id',
                array()
            )
            ->where($conditionSql . ' = :cond');

        $cacheKey = $this->getCacheKeyPrefix("_getInactiveItemIds_") . md5((string)$select);

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            return $cacheData;
        }

        $data = $this->_conn->fetchCol($select, $bind);
        $this->_saveInCache($cacheKey, $data);

        return $data;
    }

    /**
     * Return disable category ids
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @return array
     */
    protected function _getDisabledIds($collection)
    {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::_getDisabledIds($collection);
        }

        $cacheKey = $this->getCacheKeyPrefix("_getDisabledIds_") . md5((string)$collection->getSelect());

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            return $cacheData;
        }

        $storeId = Mage::app()->getStore()->getId();

        $this->_inactiveItems = $this->getInactiveCategoryIds();

        $this->_inactiveItems = array_merge(
            $this->_getInactiveItemIds($collection, $storeId),
            $this->_inactiveItems
        );

        $allIds = $collection->getAllIds();
        $disabledIds = array();

        foreach ($allIds as $id) {
            $parents = $this->getNodeById($id)->getPath();
            foreach ($parents as $parent) {
                if (!$this->_getItemIsActive($parent->getId(), $storeId)){
                    $disabledIds[] = $id;
                    continue;
                }
            }
        }

        $this->_saveInCache($cacheKey, $disabledIds);

        return $disabledIds;
    }
}