<?php

class Zolago_Catalog_Model_Resource_Category extends Mage_Catalog_Model_Resource_Category
{
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
        if(Mage::app()->getStore()->isAdmin()) {
            return parent::_getIsActiveAttributeId();
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

        $cacheKey = "resource_getChildren_" . md5((string)$select);

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            $this->_isActiveAttributeId = unserialize($cacheData);
            return $this->_isActiveAttributeId;
        }

        $data = $adapter->fetchCol($select, $bind);

        $this->_saveInCache($cacheKey, $data);

        return $data;
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    protected function _getIsActiveAttributeId() {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin()) {
            return parent::_getIsActiveAttributeId();
        }

        $cacheKey = "resource__getIsActiveAttributeId";

        if ($cacheData = $this->_loadFromCache($cacheKey)) {
            $this->_isActiveAttributeId = unserialize($cacheData);
            return $this->_isActiveAttributeId;
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
     * @param string $key
     * @return false | mixed | string
     */
    protected function _loadFromCache($key) {
        return Mage::app()->getCache()->load($key);
    }

    /**
     * @param string $key
     * @param array $data
     */
    protected function _saveInCache($key, $data) {
        $cache = Mage::app()->getCache();
        $oldSerialization = $cache->getOption("automatic_serialization");
        $cache->setOption("automatic_serialization", true);
        $cache->save($data, $key, array(), 600);
        $cache->setOption("automatic_serialization", $oldSerialization);
    }
}