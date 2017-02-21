<?php
class Zolago_Solrsearch_Model_Resource_Improve extends Mage_Core_Model_Resource_Db_Abstract {

    const JOIN_STOCK	= "stock";
    const JOIN_PRICE	= "price";
    const JOIN_URL		= "url";

    protected $_entity;

    protected $_categories = array();
    protected $_vendors = array();

    /**
     * Use link as main table
     */
    protected function _construct() {
        $this->_init('catalog/product_super_link', 'link_id');
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return type
     */
    public function getAllIdsToSolveAsianTricks(Mage_Catalog_Model_Resource_Product_Collection $collection) {
        $select = clone $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns($collection->getEntity()->getEntityIdField());
        return $this->getReadConnection()->fetchCol($select);
    }
    /* Load from eav index
    * @param Varien_Data_Collection $collection
    * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $attributesColleciton
    * @param array $allIds
    * @param int $storeId
    * @return \Zolago_Solrsearch_Model_Ultility
    */
    public function loadAttributesDataFromIndex(
        Zolago_Solrsearch_Model_Improve_Collection $collection,
        Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrbiuteCollection,
        array $allIds, $storeId) {

        $values = $this->getEavIndexAttributeValues($allIds, $storeId);

        foreach($values as $productId=>$attributes) {
            $item = $collection->getItemById($productId);
            if(!$item) {
                continue;
            }
            foreach($attributes as $attributeId=>$attributeValues) {
                $attributeModel = $attrbiuteCollection->getItemById($attributeId);
                if ($attributeModel) {
                    $item->setOrigData($attributeModel->getAttributeCode() . "_facet", $attributeValues);
                }
            }
            Mage::getSingleton("zolagosolrsearch/data")->extendConfigurable($item, $attrbiuteCollection);
        }
    }
    /**
     *
     * @param array $entityIds
     * @param type $storeId
     * @return array
     */
    public function getEavIndexAttributeValues(array $entityIds, $storeId) {

        array_walk($entityIds, function($item) {
            return (int)$item;
        });

        $tabel = $this->getTable('catalog/product_index_eav');
        $select = $this->getReadConnection()->select();
        $select->from(array("index"=>$tabel), array("entity_id", "attribute_id", "value"));
        $select->where("index.entity_id IN (?)", $entityIds);
        $select->where("store_id=?",$storeId);

        $out = array();


        $availableSizes = array();
        $childrenFeatParent = $this->getChildrenIds($entityIds);
        $children = array_keys($childrenFeatParent);
        $_subproducts = Mage::getModel('catalog/product')->getCollection()
                        ->addIdFilter($children)
                        ->addAttributeToSelect('size')
                        ->addAttributeToSelect('parent_id');
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_subproducts);
        foreach ($_subproducts as $_subproduct) {
            if ($_subproduct->getSize()) {
                $availableSizes[$childrenFeatParent[$_subproduct->getId()]][(int)$_subproduct->getSize()] = (int)$_subproduct->getSize();
            }
        }

        $attribute = Mage::getModel('catalog/resource_eav_attribute')
                     ->loadByCode(Mage_Catalog_Model_Product::ENTITY, "size");


        foreach($this->getReadConnection()->fetchAll($select) as $row) {

            if(!isset($out[$row['entity_id']][$row['attribute_id']])) {
                $out[$row['entity_id']][$row['attribute_id']] = array();
            }
            if($row['attribute_id'] == $attribute->getId()) {
                if(isset($availableSizes[$row['entity_id']][$row['value']])) {
                    $out[$row['entity_id']][$row['attribute_id']][] = $row['value'];
                }
            } else {
                $out[$row['entity_id']][$row['attribute_id']][] = $row['value'];
            }

        }

        return $out;

    }


    /**
     *
     * Retrieve Required children-parent ids
     *
     * @param $parentIds
     * @return array
     */
    public function getChildrenIds($parentIds)
    {
        $childrenFeatParent = array();

        $select = $this->_getReadAdapter()->select()
                  ->from(array('l' => $this->getMainTable()), array('product_id', 'parent_id'))
                  ->join(
                      array('e' => $this->getTable('catalog/product')),
                      'e.entity_id = l.product_id AND e.required_options = 0',
                      array()
                  )
                  ->where('parent_id IN (?)', $parentIds);

        $rows = $this->_getReadAdapter()->fetchAll($select);
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $childrenFeatParent[$row["product_id"]] = $row["parent_id"];
            }
        }

        return $childrenFeatParent;
    }

    /**
     * @param int|array $childId
     * @return array
     */
    public function getStoreIdsByProducts($childId)
    {
        $parentIds = array();

        $select = $this->_getReadAdapter()->select()
                  ->from(array("s"=>$this->getTable("core/store")), array('store_id'))
                  ->join(
                      array("g"=>$this->getTable("core/store_group")),
                      "g.group_id=s.group_id",
                      array())
                  ->join(
                      array("c"=>$this->getTable("catalog/product_website")),
                      "c.website_id=g.website_id",
                      array("product_id"))
                  ->where("c.product_id IN (?)", $childId);

        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            if(!isset($parentIds[$row['product_id']])) {
                $parentIds[$row['product_id']] = array();
            }
            $parentIds[$row['product_id']][] = $row['store_id'];
        }

        return $parentIds;
    }

    /**
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId, $asFlatList = false)
    {
        $parentIds = array();

        $select = $this->_getReadAdapter()->select()
                  ->from($this->getMainTable(), array('product_id', 'parent_id'))
                  ->where('product_id IN(?)', $childId);
        if ($asFlatList) {
            foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
                $parentIds[$row['parent_id']] = $row['parent_id'];
            }
        } else {
            foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
                if (!isset($parentIds[$row['product_id']])) {
                    $parentIds[$row['product_id']] = array();
                }
                $parentIds[$row['product_id']][] = $row['parent_id'];
            }
        }
        return $parentIds;
    }

    /**
     * @param array|int $parentIds
     * @return array (parenId=>array(child1, child2,...),...)
     */
    public function getAllChildIds($parentIds) {
        if(!is_array($parentIds)) {
            $parentIds = array($parentIds);
        }
        $select = $this->getReadConnection()->select();
        $select->from($this->getMainTable(), array("parent_id", "product_id"));
        $select->where("parent_id IN (?)", $parentIds);
        $out = array();
        foreach ($this->getReadConnection()->fetchAll($select) as $row) {
            if(!isset($out[$row['parent_id']])) {
                $out[$row['parent_id']] = array();
            }
            $out[$row['parent_id']][] = $row['product_id'];
        }
        return $out;
    }
    
    /**
     * collect orders count
     */
    public function loadOrdersData(Zolago_Solrsearch_Model_Improve_Collection $collection) {
        $ids = $collection->getAllIds();
        // raw query (faster)
        if (empty($ids)) {
            return array();
        }
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('udpo/po_item');
        $query = "SELECT product_id,sum(qty) as popularity from ".$table." WHERE product_id in (".implode(',',$ids).") GROUP BY product_id";
        $results = $readConnection->fetchAll($query);
        $popularity = array();
        foreach ($results as $val) {
            $popularity[$val['product_id']] = (int)$val['popularity'];
        }
        return $popularity;
        
    }
        
    /**
     * @param Zolago_Solrsearch_Model_Improve_Collection $collection
     * @param int $storeId
     * @return Zolago_Solrsearch_Model_Improve_Collection
     */
    public function loadCategoryData(Zolago_Solrsearch_Model_Improve_Collection $collection, $storeId) {

        $asFacet = Mage::helper('solrsearch')->getSetting('use_category_as_facet');
        $isSearchable = Mage::helper('solrsearch')->getSetting('solr_search_in_category');

        // Get collection ids to be indexed
        $allIds = $collection->getCategoryIds() ?
                  $collection->getCategoryIds() : $collection->getAllIds();

        if(!$allIds) {
            return $this;
        }

        // Collect category data
        $this->_collectCategories($allIds, $storeId, true);

        // Group category by product
        $facets = array();
        $ids = array();
        foreach($this->_categories as $row) {
            $catId = $row['category_id'];
            // Facetes
            if(!isset($facets[$row['product_id']])) {
                $facets[$row['product_id']] = array();
            }
            $facets[$row['product_id']][$catId] = $row['name'] . "/" . $row['category_id'];
            // Ids
            if(!isset($ids[$row['product_id']])) {
                $ids[$row['product_id']] = array();
            }
            $ids[$row['product_id']][$catId] = $row['category_id'];
        }

        // Assign categories to product
        foreach($ids as $productId => $prodcutCategoryIds) {
            if($item = $collection->getItemById($productId)) {
                if(isset($facets[$productId])) {
                    // Set faces
                    $facets[$productId] = array_values($facets[$productId]);
                    if($asFacet) {
                        $item->setCategoryFacet($facets[$productId]);
                        $item->setCategoryText($facets[$productId]);
                        $item->setCategoryBoost($facets[$productId]);
                        $item->setCategoryBoostExact($facets[$productId]);
                        $item->setCategoryRelativeBoost($facets[$productId]);
                    }
                    // Process search
                    if($isSearchable) {
                        $textSearch = $item->getSearchText();
                        if(is_array($textSearch)) {
                            $textSearch = array_merge($textSearch, $facets[$productId]);
                        }
                        $item->setSearchText($textSearch);
                    }
                    /*
                     * Info:
                     * All parent categories are calculating
                     * @see $this->_collectCategories
                     */

                    $item->setCategoryPath($facets[$productId]);
                }
                // Finally set categoru ids
                $item->setCategoryId(array_values($prodcutCategoryIds));
            }
        }

        return $this;
    }


    /**
     * check if category is visible in gallery
     *
     * @param array $related
     * @return bool
     */
    protected function _checkIsVisible($related) {
        $path = $related['path'];
        $pathArray = explode('/',$path);
        foreach ($pathArray as $id) {
            if (!$this->_checkIsActive($id)) {
                return false;
            }
        }
        return true;

    }


    /**
     * check if category is active
     *
     * @param int $categoryId
     * @return bool
     */
    protected function _checkIsActive($categoryId) {
        if (empty($this->_categories[$categoryId])) {
            $this->_categories[$categoryId] = Mage::getModel('catalog/category')->load($categoryId);
        }
        return $this->_categories[$categoryId]->getIsActive();
    }
    /**
     * find vendors in which category is visible
     *
     * @param array $related
     * @return array
     */
    protected function _checkAssignedVendorsFromCategory($related,$vendorCheck,$storeId) {
        if (empty($this->_vendors)) {
            $_helper = Mage::helper('udropship');
            $collection = Mage::getModel('udropship/vendor')->getCollection()
                          ->addFieldToFilter('status',ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE)
                          ->addFieldToFilter('vendor_id',array('in' => array($vendorCheck)));
            foreach ($collection as $vendor) {
                $_helper->loadCustomData($vendor,'root_category');
                foreach ($vendor->getRootCategory() as $categoryId) {
                    // set vendors to categories
                    if (!$categoryId) {
                        // no root category - overriding by gallery root category
                        $categoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    }
                    if ($categoryId) {
                        if (!$this->_checkIsActive($categoryId)) {
                            continue;
                        }
                        if (!($assignedVendors = $this->_categories[$categoryId]->getData('assigned_vendors'))) {
                            $assignedVendors = array();
                        }
                        $assignedVendors[$vendor->getId()] = $vendor->getId();
                        $this->_categories[$categoryId]->setData('assigned_vendors',$assignedVendors);
                    }
                }
                $this->_vendors[$vendor->getId()] = $vendor->getRootCategory();
            }
        }
        $path = explode('/',$related['path']);
        $path = array_reverse($path);
        $final = array();
        $denyVendors = array();
        foreach ($path as $cId) {
            if (!$this->_checkIsActive($cId)) {
                break;
            }
            if (!empty($this->_categories[$cId])) {
                $out = $this->_categories[$cId]->getData('assigned_vendors');
                if (is_array($out)) {
                    $final = array_merge($final,$out);
                }
            }
        }
        return $final;
    }

    /**
     * list of inactive categories
     * @return array
     */
    protected function _getDenyCategories() {
        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $attributeActive = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_category', 'is_active')->getId();

        $select->from(
            array("category"=>Mage::getSingleton("core/resource")->getTableName("catalog_category_entity_int")),
            array(
                "entity_id",
//				"cat_index_position" => "category_product.position",
//				"name"=>new Zend_Db_Expr("IF(store_value_name.value_id>0, store_value_name.value, default_value_name.value)")
            )
        );
        $select->where("category.attribute_id = ?", $attributeActive);
        $select->where("category.value = 0");
        $all = $adapter->fetchAll($select);
        $out = array();
        foreach ($all as $row) {
            $out[$row['entity_id']] = $row['entity_id'];
        }
        return $out;
    }
    /**
     * @param array $allIds
     * @param int $storeId
     * @return Zolago_Solrsearch_Model_Resource_Improve
     */
    protected function _collectCategories(array $allIds, $storeId, $isParent=null) {

        $adapter = $this->getReadConnection();
        $config = Mage::getModel("eav/config");
        $rootCat = Mage::app()->getStore($storeId)->getRootCategoryId();
        $treeRoot = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $attributeBrandshop = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'brandshop')->getId();
        $attributeVendor = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'udropship_vendor')->getId();
        /* @var $config Mage_Eav_Model_Config */
//		$nameAttribute = $config->getAttribute(
//			Mage_Catalog_Model_Category::ENTITY,
//			"name"
//		);
        $includeAttribute = $config->getAttribute(
                                Mage_Catalog_Model_Category::ENTITY,
                                "include_in_menu"
                            );
        /* @var $nameAttribute Mage_Eav_Model_Entity_Attribute */

        $select = $adapter->select();

        // Start from index
        $select->from(
            array("category_product"=>$this->getTable("catalog/category_product_index")),
            array(
                "product_id",
                "category_id",
//				"cat_index_position" => "category_product.position",
//				"name"=>new Zend_Db_Expr("IF(store_value_name.value_id>0, store_value_name.value, default_value_name.value)")
            )
        );

        // Add store-root category descendant (without root store category, only descendants)
        $joinCond = array(
                        "category.entity_id=category_product.category_id",
                        $adapter->quoteInto("category.path LIKE ?", $treeRoot.'/'.$rootCat."/%")
                    );
        $select->join(
            array("category"=>$this->getTable("catalog/category")),
            implode(" AND ", $joinCond),
            'path'
        );
        $select->join(
            array("brandshop"=>Mage::getSingleton("core/resource")->getTableName('catalog_product_entity_int')),
            $adapter->quoteInto("brandshop.entity_id = category_product.product_id AND brandshop.attribute_id = ?",$attributeBrandshop),
            "value as brandshop_id"
        );
        $select->join(
            array("vendor"=>Mage::getSingleton("core/resource")->getTableName('catalog_product_entity_int')),
            $adapter->quoteInto("vendor.entity_id = category_product.product_id AND vendor.attribute_id = ?",$attributeVendor),
            "value as vendor_id"
        );

        // Join attributes data
//		$this->_joinAttribute($select, $nameAttribute, "category_product.category_id", $storeId);
        $this->_joinAttribute($select, $includeAttribute, "category_product.category_id", $storeId);

        // Filters
        $select->where("category_product.product_id IN (?)", $allIds);
        $select->where("category_product.store_id = ?", $storeId);
        $select->where(
            "IF(".
            "store_value_include_in_menu.value_id>0,".
            "store_value_include_in_menu.value, ".
            "default_value_include_in_menu.value".
            ")=?", 1
        );

        // Parent filter
        if($isParent!==null) {
            $select->where("category_product.is_parent=?", $isParent ? 1 : 0);
        }
        // ###################################################################
        // Getting all categories ids where 'should be' product in tree hierarchy logic
        // ###################################################################
        $categories = $adapter->fetchAll($select);
        // add related categories
        $categoryRelatedCheck = array();
        $vendorCheck = array();
        foreach ($categories as $category) {
            $categoryRelatedCheck[$category['category_id']] = $category['category_id'];
            $vendorCheck[$category['vendor_id']] = $category['vendor_id'];
            $vendorCheck[$category['brandshop_id']] = $category['brandshop_id'];
        }
        $categoryRelated = array();
        if ($categoryRelatedCheck) {
            $relatedCategories = Mage::getModel('catalog/category')->getCollection()
                                 ->addAttributeToFilter('related_category_products',1)
                                 ->addAttributeToFilter('related_category',$categoryRelatedCheck)
                                 ->addAttributeToSelect('entity_id')
                                 ->addAttributeToSelect('path');
            //

            foreach ($relatedCategories as $related) {
                // check if related category is visible in gallery
                if (!$this->_checkIsVisible($related)) {
                    // find vendors in which related category is visible
                    if (!$vendors = $this->_checkAssignedVendorsFromCategory($related,$vendorCheck,$storeId)) {
                        // no vendors, category is invisible
                        continue;
                    }
                } else {
                    // visible in gallery (vendor 0)
                    $vendors = array(0 =>0);
                }
                $categoryRelated[$related['related_category']][$related['entity_id']] = array(
                            'path'		=> $related['path'],
                            'vendors' => $vendors,

                        );
            }
        }
        $tmp = $categories;
        foreach ($tmp as $product) {
            if (!empty($categoryRelated[$product['category_id']])) {
                // can be in related
                $related = $categoryRelated[$product['category_id']];
                foreach ($related as $relatedId => $row) {
                    if (in_array($product['brandshop_id'],$row['vendors']) ||
                            in_array($product['vendor_id'],$row['vendors'])) {
                        $categories[] = array (
                                            'product_id' => $product['product_id'],
                                            'category_id' => $relatedId,
                                            'path' => $row['path'],
                                            'brandshop_id' => $product['brandshop_id'],
                                            'vendor_id' => $product['vendor_id'],
                                        );
                    }
                }
            }
        }
        $denyCategories = $this->_getDenyCategories();
        $idsToLoad = array();
        foreach ($categories as $idx => $value) {
            $ex = explode('/', $value['path']);
            foreach ($ex as $catIdx) {
                if (in_array($catIdx,$denyCategories) ||
                        ($catIdx == $treeRoot) ||
                        ($catIdx == $rootCat)
                   ) {
                    continue;
                }
                $categories[$idx]['cats'][$catIdx] = $catIdx; //Saving for easier processing
                $idsToLoad[] = $catIdx;
            }
        }

        // Removing duplicates and removing magento tree root and store root
        $idsToLoad = array_unique($idsToLoad);

        // Getting info about categories
        /** @var Zolago_Catalog_Model_Category $modelCC */
        $modelCC = Mage::getModel('catalog/category');
        /** @var Mage_Catalog_Model_Resource_Category_Collection $coll */
        $coll = $modelCC->getCollection();
        $coll->addNameToResult()->addIdFilter($idsToLoad);
        // todo
        // Creating array for solr purpose
        $categoriesExtend = array();
        foreach ($categories as $cat) {
            foreach ($cat['cats'] as $id) {
                $categoriesExtend[] = array(
                                          'product_id' => $cat['product_id'],
                                          'name' => $coll->getItemById($id)->getName(),
                                          'category_id' => $id
                                      );
            }
        }

        $this->_categories = $categoriesExtend;
        return $this;
    }

    /**
     *
     * @param Varien_Db_Select $select
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param $joinEntityField
     * @param int $storeId
     * @return $this
     */
    protected function _joinAttribute(Varien_Db_Select $select,
                                      Mage_Eav_Model_Entity_Attribute $attribute,
                                      $joinEntityField,
                                      $storeId) {

        $code = $attribute->getAttributeCode();
        $adapter = $this->getReadConnection();

        // Join default attr value of name
        $joinCond = array(
                        $adapter->quoteInto('default_value_'.$code.'.attribute_id = ?', $attribute->getId()),
                        'default_value_'.$code.'.entity_id='.$joinEntityField,
                        $adapter->quoteInto('default_value_'.$code.'.store_id = ?',
                                            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
                    );
        $select->join(
            array('default_value_'.$code => $attribute->getBackendTable()),
            implode(' AND  ', $joinCond) ,
            array()
        );
        $joinCond = array(
                        'store_value_'.$code.'.attribute_id = default_value_'.$code.'.attribute_id',
                        'store_value_'.$code.'.entity_id = default_value_'.$code.'.entity_id',
                        $adapter->quoteInto('store_value_'.$code.'.store_id = ?', $storeId)
                    );
        $select->joinLeft(
            array('store_value_'.$code => $attribute->getBackendTable()),
            implode(' AND ', $joinCond),
            array()
        );

        return $this;

    }

    /**
     * Load attributes data by n*query - n = num data table
     * @param Varien_Data_Collection $collection
     * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrbiuteCollection
     * @param array $allIds
     * @param int $storeId
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function loadAttributesData(Varien_Data_Collection $collection,
                                       Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrbiuteCollection,
                                       array $allIds, $storeId) {


        if (!$collection->count()) {
            return $this;
        }

        $attrIds = $attrbiuteCollection->getAllIds();
        //array_walk($attrIds, function($item){return (int)$item;});

        //array_walk($allIds, function($item){return (int)$item;});

        $entity = $this->getEntity();

        $tableAttributes = array();
        $attributeTypes  = array();

        // Collect backend tables
        foreach ($attrbiuteCollection as $attributeId=>$attribute) {
            $attribute->setStoreId($storeId);
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

    /**
     * @param Varien_Data_Collection $collection
     * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrbiuteCollection
     * @param array $valueInfo
     * @return \Zolago_Solrsearch_Model_Resource_Improve
     * @throws Exception
     */
    protected function _setItemAttributeValue(Varien_Data_Collection $collection,
            Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrbiuteCollection,
            $valueInfo)
    {

        $entityIdField  = $this->getEntity()->getEntityIdField();
        $entityId       = $valueInfo[$entityIdField];
        $item			= $collection->getItemById($entityId);
        $attributeCode  = null;

        if (!$item) {
            throw Mage::exception('Mage_Eav',
                                  Mage::helper('eav')->__('Data integrity: No header row found for attribute')
                                 );
        }

        $attribute = $attrbiuteCollection->getItemById($valueInfo['attribute_id']);
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */

        if($attribute && $attribute->getId()) {
            $attributeCode = $attribute->getAttributeCode();
        }


        if (!$attributeCode) {
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute(
                             $this->getEntity()->getType(),
                             $valueInfo['attribute_id']
                         );
            $attributeCode = $attribute->getAttributeCode();
        }

        if($attributeCode) {
            $item->setOrigData($attributeCode, $valueInfo['value']);
            if($collection->isReagularItem($item)) {
                Mage::getSingleton("zolagosolrsearch/data")->
                afterLoadAttribute($item, $attribute);

            }
        }

        return $this;
    }

    /**
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntity() {
        if(!$this->_entity) {
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

    protected function _getLoadAttributesSelect($allIds, $table, array $attributeIds, $storeId)
    {
        $adapter = $this->getReadConnection();
        $entityIdField = $this->getEntity()->getEntityIdField();
        $joinConditionStore = array(
            't_s.attribute_id = t_main.attribute_id',
            't_s.entity_id = t_main.entity_id',
            't_s.entity_type_id = t_main.entity_type_id',
            $adapter->quoteInto('t_s.store_id = ?', $storeId)
        );
        $joinConditionDefault = array(
            't_d.attribute_id = t_main.attribute_id',
            't_d.entity_id = t_main.entity_id',
            't_d.entity_type_id = t_main.entity_type_id',
            $adapter->quoteInto('t_d.store_id = ?', $this->getDefaultStoreId())
        );
        $select = $adapter->select()
            ->distinct()
            ->from(array('t_main' => $table), array($entityIdField, 'attribute_id'))
            ->joinLeft(
                array('t_s' => $table),
                implode(' AND ', $joinConditionStore),
                array())
            ->joinLeft(
                array('t_d' => $table),
                implode(' AND ', $joinConditionDefault),
                array())
            ->where('t_main.entity_type_id = ?', $this->getEntity()->getTypeId())
            ->where("t_main.{$entityIdField} IN (?)", $allIds)
            ->where('t_main.attribute_id IN (?)', $attributeIds);

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
        if(!$extraJoins) {
            return $adapter->fetchAll($select);
        }

        // Join stocks index.
        // @spike Mayby from regular table?
        if(isset($extraJoins[self::JOIN_STOCK])) {
            $joinCond = array(
                            "stock_item.product_id=product.entity_id",
                            $adapter->quoteInto("stock_item.website_id=?", $websiteId),
                        );
            $select->joinLeft(
                array("stock_item"=>$this->getTable("cataloginventory/stock_status")),
                implode(" AND ", $joinCond),
                array("qty", "stock_status")
            );
        }
        // Join price data
        if(isset($extraJoins[self::JOIN_PRICE])) {
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
            $select->where('price', array('gt' => 0)); // We don't need product with price zero in solr
        }

        if(isset($extraJoins[self::JOIN_URL])) {
            $joinCond = array(
                            'url.product_id = product.entity_id',
                            'url.id_path = CONCAT(\'product/\', product.entity_id)',
                            $adapter->quoteInto('url.store_id = ?', $storeId),
                            'url.category_id IS NULL'
                        );
            $select->joinLeft(
                array("url"=>$this->getTable("core/url_rewrite")),
                implode(" AND ", $joinCond),
                array("request_path")
            );
        }

        return $adapter->fetchAll($select);
    }

    protected function _addTaxPercent() {

    }


    /**
     * @param Zolago_Solrsearch_Model_Catalog_Product_Collection $collection
     * @param int $storeId
     * @param int $customerGroupId
     * @return Zolago_Solrsearch_Model_Resource_Improve
     */
    public function loadAttributesDataForFrontend(
        Zolago_Solrsearch_Model_Catalog_Product_Collection $collection,
        $storeId, $customerGroupId) {

        // Add is in my wishlist
        $wishlist = Mage::helper("zolagowishlist")->getWishlist();
        /* @var $wishlist Mage_Wishlist_Model_Wishlist */

        $select = $this->getReadConnection()->select();
        $select->from($this->getTable("wishlist/item"), array("product_id"));
        $select->where("wishlist_id=?", $wishlist->getId());
        $select->where("store_id=?", $storeId);
        $select->where("product_id IN (?)", $collection->getAllIds());

        foreach($this->getReadConnection()->fetchCol($select) as $productId) {
            if($product=$collection->getItemById($productId)) {
                $product->setInMyWishlist(1);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRuleAppliedAffectedProducts() {
        try {
            $table = $this->getTable('catalogrule/rule_product_price_tmp');
            $connection = $this->getReadConnection();
            /* @var $connection Varien_Db_Adapter_Interface */
            $select = $connection->select();
            $select->from($table, array("product_id"));
            return $connection->fetchCol($select);

        } catch (Exception $ex) {
            Mage::logException($ex);
            return array();
        }
    }

}
