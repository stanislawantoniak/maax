<?php

/**
 * Class Zolago_Catalog_Model_Category
 *
 * @method string getDisplayMode()
 * @method int getCategoryBlogPostId()
 */
class Zolago_Catalog_Model_Category extends Mage_Catalog_Model_Category
{
    const CACHE_NAME = 'MODEL_CATEGORY';

    protected $_relatedCategory;

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
     * @param $id
     * @param $field
     * @param $storeId
     * @return string
     */
    protected function _getCacheKey($id, $field, $storeId) {
        if($field==null) {
            $field = $this->getIdFieldName();
        }
        return $this->getCacheKeyPrefix('load_') . $field . "_" . $id . "_" . $storeId;
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
     * @return string
     */
    public function getNoVendorContextUrl() {
        if(!$this->hasData("no_vendor_context_url")) {
            $this->setData(
                "no_vendor_context_url",
                Mage::helper("zolagodropshipmicrosite")->convertToNonVendorContext($this->getUrl())
            );
        }
        return $this->getData("no_vendor_context_url");
    }
    /**
     * @todo more flexible
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory() {
        if(Mage::registry('current_category') instanceof Mage_Catalog_Model_Category) {
            return Mage::registry('current_category');
        }
        return Mage::helper("zolagodropshipmicrosite")->getVendorRootCategoryObject();
    }



    /**
     * Overload load method - load cached data if possible
     * @param int $id
     * @param string $field
     * @return Zolago_Catalog_Model_Category
     */
    public function load($id, $field=null) {

        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::load($id, $field);
        }

        $cacheKey = $this->_getCacheKey($id, $field, $this->getStoreId());

        if($cacheData = $this->_loadFromCache($cacheKey)) {
            $this->_beforeLoad($id, $field);
            $this->setData($cacheData);
            $this->_afterLoad();
            $this->setOrigData();
            $this->_hasDataChanges = false;
            return $this;
        }

        // Clean all data from previous loaded instance
        $this->unsetData();
        // Load origin
        parent::load($id, $field);

        // Load common used data for much better performance
        $this->getUrl(); // Get in easy way request_path from rewrite
        $this->getNoVendorContextUrl();
        $this->unsetData('url'); // Trick for vendor/no vendor context
        $this->getParentCategories();


        // Do save
        $this->_saveInCache($cacheKey, $this->getData());

        return $this;
    }

    /**
     * Return parent categories of current category
     *
     * @return array
     */
    public function getParentCategories() {
        if (!$this->hasData("parent_categories")) {
            $pathIds = array_reverse(explode(',', $this->getPathInStore()));
            $categories = Mage::getResourceModel('catalog/category_collection')
                ->setStore(Mage::app()->getStore())
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('long_name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path')
                ->addFieldToFilter('entity_id', array('in' => $pathIds))
                ->addFieldToFilter('is_active', 1)
                ->load()
                ->getItems();
            $this->setData("parent_categories",  $categories);
        }
        return $this->getData("parent_categories");
    }

    /**
     * returns related category object
     *
     * @return Zolago_Catalog_Model_Category
     */
    public function getRelatedCategory() {
        if (is_null($this->_relatedCategory)) {
            $related = $this->getData('related_category');
            $category = Mage::getModel('catalog/category')->load($related);
            if ($category->getId()) {
                $this->_relatedCategory = $category;
            } else {
                $this->_relatedCategory = false;
            }
        }
        return $this->_relatedCategory;
    }

    /**
     * Return canonical link
     *
     * @return string
     */
    public function getCanonicalUrl($noVendor = false) {
        $canonical = $this->getData('canonical_link');
        if (empty($canonical)) {
            $related = $this->getRelatedCategory();
            if ($related) {
                $canonical = $related->getCanonicalUrl(true);
            } else {
                if ($noVendor) {
                    $categoryUrl = $this->getNoVendorContextUrl();
                } else {
                    $categoryUrl = $this->getUrl();
                }
                /** @var GH_Rewrite_Helper_Data $rewriteHelper */
                $rewriteHelper = Mage::helper('ghrewrite');
                /** @var Zolago_Solrsearch_Model_Catalog_Product_List $listModel */
                $listModel     = Mage::getSingleton('zolagosolrsearch/catalog_product_list');

                $path          = $listModel->getCurrentUrlPath();
                $categoryId    = $this->getId();
                $queryData     = Mage::app()->getRequest()->getParams();
                $url           = $rewriteHelper->prepareRewriteUrl($path, $categoryId, $queryData, true);

                $canonical     = $url ? $url : $categoryUrl;
            }
        }
        return $canonical;
    }

    /**
     * product ids to rebuild in solr after save
     *
     * @return array
     */
    public function getRelatedProductsToRebuild () {
        $origRelated = $this->getOrigData('related_category');
        $origProducts = $this->getOrigData('related_category_products');
        $related = $this->getData('related_category');
        $products = $this->getData('related_category_products');
        if ($origProducts == $products) {
            if (!$products) {
                return; // nothing happends
            } else {
                if ($origRelated == $related) {
                    return; // no changes
                }
            }
        }
        $out = array();
        if ($origRelated && $origProducts) {
            $category = Mage::getModel('catalog/category')->load($origRelated);
            if ($category->getId()) {
                $ids = Mage::getResourceModel('catalog/product_collection')
                       ->addCategoryFilter($category)
                       ->getAllIds();
                $out = array_merge($out,$ids);
            }
        }
        if ($related && $products) {
            $category = Mage::getModel('catalog/category')->load($related);
            if ($category->getId()) {
                $ids = Mage::getResourceModel('catalog/product_collection')
                       ->addCategoryFilter($category)
                       ->getAllIds();
                $out = array_merge($out,$ids);
            }
        }
        return array_unique($out);
    }


    /**
     * prepare url with context (vendor, landing page, filters)
     *
     * @param bool $useFilters include filters context
     * @param bool $useCampaign include landing page context
     * @param bool $useSearchContext include search context
     * @param bool $useVendor include vendor context
     * @return string
     */
    public function getUrlContext($useFilters = true, $useCampaign = true, $useSearchContext = true, $useVendor = true) {
        if ($useSearchContext && Mage::helper('zolagosolrsearch')->isSearchContext()) {
            $query = Mage::helper("solrsearch")->getParam("q");
            $params = array(
                          "_query" => array(
                              "q" => $query,
                              "scat" => $this->getId()
                          )
                      );
            return Mage::getUrl('search',$params);
        }
        $urlPath = $this->getUrlPath();
        $campaign = $useCampaign ? $this->getCurrentCampaign():null;
        $vendor = $useVendor ? Mage::helper('umicrosite')->getCurrentVendor():null;
        $params = array();
        if ($campaign) {
            $key = $campaign->getCampaignFilterKey();
            $params['_query']['fq'][$key][] = $campaign->getId();
        }
        $categoryId = $this->getId();
        if (!empty($vendor)) {
            $vendorRootCategory = $vendor->getRootCategory();
            if (!empty($vendorRootCategory)) {
                $currentStoreId = Mage::app()->getStore()->getId();
                $vendorRootCategoryForSite = isset($vendorRootCategory[$currentStoreId]) ? $vendorRootCategory[$currentStoreId] : false;
                if ($vendorRootCategoryForSite) {
                    if ($vendorRootCategoryForSite == $this->getId()) {
                        $urlPath = '/';
                    }
                }
            }
        }
        $return = Mage::getUrl($urlPath,$params);
        return $return;
    }
    /**
     * category name with campaign context
     *
     * @param bool $long use long or short category name
     * @return string
     */
    public function getNameContext($long = true) {
        $campaign = $this->getCurrentCampaign();
        if (!$campaign) {
            return $long ? $this->getLongName(): $this->getName();
        } else {
            return $campaign->getNameCustomer();
        }
    }

    /**
     *
     * @return Zolago_Campaign_Model_Campaign | null
     */
    public function getCurrentCampaign() {
        if (is_null($this->_campaign)) {
            $this->_campaign = false;
            $ids = Mage::helper('zolagocampaign')->getCampaignIdsFromUrl();
            if (empty($ids)) {
                return false;
            }
            $categoryIds = $this->getPathIds();
            $categoryIds[] = $this->getId();
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            $resource = Mage::getResourceModel('zolagocampaign/campaign');
            if ($vendor) {
                $vendorId = $vendor->getId();
            } else {
                $vendorId = 0;
            }
            $categoryIds = array_unique($categoryIds);
            $list = $resource->getLandingPagesByCategories($categoryIds,$vendorId,$ids);
            $id = null;
            // promotion nearest our category
            while(!empty($categoryIds)) {
                $categoryId = array_pop($categoryIds);
                if (!empty($list[$categoryId])) {
                    $id = reset($list[$categoryId]); // first campaign from list
                    break;
                }
            }
            if ($id) {
                $campaign = Mage::getModel('zolagocampaign/campaign')->load($id);
                if ($campaign->getId()) {
                    $this->_campaign = $campaign;
                }
            }
        }
        return $this->_campaign;
    }

    /**
     * @param $category
     * @param bool|FALSE $vendorContext
     * @return int
     */
    public function getSolrProductsCount($category, $vendorContext = FALSE)
    {
        /** @var Zolago_Solrsearch_Model_Solr_Category_Solr $solrModel */
        $solrModel = Mage::getModel('zolagosolrsearch/solr_category_solr');
        $vid = 0;
        if ($vendorContext) {
            $solrModel->setVendorContext($vendorContext);
            $vid = $vendorContext->getId();
        }

        $cacheKey = sprintf("SOLR_PRODUCTS_COUNT_%d_%d_%d", $category->getId(), $vid, Mage::app()->getStore()->getId());

        $cacheData = Mage::helper("zolagomodago/category")->loadFromCache($cacheKey);

        if ($cacheData === FALSE) {
            $solrModel->setCurrentCategory($category);
            $solrFieldList = array("category_id" => $category->getId());
            $solrModel->setCategory($category);

            $solrModel->setFieldList($solrFieldList);
            $resultSet = $solrModel->query("*");
            $cacheData = (empty($resultSet['response']['numFound']) ? 0 : (int)$resultSet['response']['numFound']);
            // Do save
            $this->_saveInCache($cacheKey, $cacheData);
        }

        return $cacheData;

    }

    /**
     * Retrieve categories by parent
     *
     * @param int $parent
     * @param int $recursionLevel
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return mixed
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true) {
        // Skip cache in admin
        if(Mage::app()->getStore()->isAdmin() || !$this->canUseCache()) {
            return parent::getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
        }

        // Cache key for parent node
        $cacheKey = $this->getCacheKeyPrefix("getCategories_").$parent.'_'.$recursionLevel.'_' . (int)$sorted.'_'.(int)$asCollection.'_'.(int)$toLoad;

        if($cacheData = $this->_loadFromCache($cacheKey)) {
            /* @var $tree Zolago_Catalog_Model_Resource_Category_Tree */
            $tree = Mage::getResourceModel('catalog/category_tree');
            $node = new Varien_Data_Tree_Node($cacheData, 'entity_id', $tree);
            $node->loadChildren($recursionLevel);
            $tree->addCollectionData(null, $sorted, $parent, $toLoad, true);

            if ($asCollection) {
                return $tree->getCollection();
            }
            return $node->getChildren();
        }

        // Load origin
        /** @var Zolago_Catalog_Model_Resource_Category $resource */
        $resource = $this->getResource();
        /** @var Varien_Data_Tree_Node_Collection $categories */
        $categories = $resource->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);

        // Save parent node, cache for children in tree->load()
        /** @var Varien_Data_Tree_Node $parentNode */
        $parentNode = $resource->getParentNode($parent);
        if ($parentNode->getId()) {
            $this->_saveInCache($cacheKey, $parentNode->getData());
        }

        return $categories;
    }

    protected function _afterLoad()
    {
        /*
         * prevents products to be included in post on category save
         * we handle product / category associations another way - converters
         */
        $this->setProductsReadonly(true);
        return parent::_afterLoad(); // TODO: Change the autogenerated stub
    }
}