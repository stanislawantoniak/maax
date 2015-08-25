<?php

class Zolago_Catalog_Model_Category extends Mage_Catalog_Model_Category
{
	
    protected $_relatedCategory;
	/**
	 * @return string
	 */
	public function getNoVendorContextUrl() {
		if(!$this->hasData("no_vendor_context_url")){
			$this->setData(
				"no_vendor_context_url",
				Mage::helper("zolagodropshipmicrosite")->convertToNonVendorContext($this->getUrl())
			);
		}
		return $this->getData("no_vendor_context_url");
	}
	
	/**
	 * Overload load method - load cached data if possible
	 * @param int $id
	 * @param string $field
	 * @return Zolago_Catalog_Model_Category
	 */
	public function load($id, $field=null) {
		
		// Skip cache in admin
		if(Mage::app()->getStore()->isAdmin()){
			return parent::load($id, $field);
		}
		
		Varien_Profiler::start("Loading category");
		$cacheKey = $this->_getCacheKey($id, $field, $this->getStoreId());
		
		if($cacheData = $this->_loadFromCache($cacheKey)){
			$this->_beforeLoad($id, $field);
			$this->setData(unserialize($cacheData));
			$this->_afterLoad();
			$this->setOrigData();
			$this->_hasDataChanges = false;
			Varien_Profiler::start("Loading category");
			return $this;
		}
		
		// Load origin 
		parent::load($id, $field);
		// Do save
		$this->_saveInCache($cacheKey, $this->getData());
		Varien_Profiler::start("Loading category");
		
		return $this;
	}
	
	/**
	 * @param string $key
	 * @return null | string
	 */
	protected function _loadFromCache($key){
		return Mage::app()->getCache()->load($key);
	}
	
	/**
	 * @param string $key
	 * @param array $data
	 */
	protected function _saveInCache($key, $data){
		$cache = Mage::app()->getCache();
		$oldSerialization = $cache->getOption("automatic_serialization");
		$cache->setOption("automatic_serialization", true);
		$cache->save($data, $key, array(), 600);
		$cache->setOption("automatic_serialization", $oldSerialization);
	}
	
	/**
	 * @param mixed $id
	 * @param string | null $field
	 * @param int $storeId
	 * @return string
	 */
	protected function _getCacheKey($id, $field, $storeId){
		if($field==null){
			$field = $this->getIdFieldName();
		}
		return "CATEGORY_" . $field . "_" . $id . "_" . $storeId;
	}



    /**
     * Return parent categories of current category
     *
     * @return array
     */
    public function getParentCategories()
    {
        $pathIds = array_reverse(explode(',', $this->getPathInStore()));
        $categories = Mage::getResourceModel('catalog/category_collection')
            ->setStore(Mage::app()->getStore())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('long_name')
            ->addAttributeToSelect('url_key')
            ->addFieldToFilter('entity_id', array('in' => $pathIds))
            ->addFieldToFilter('is_active', 1)
            ->load()
            ->getItems();
        return $categories;
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
     * link to category with camaign context 
     *
     * @return string
     */
     public function getUrlContext() {
         
     }
     
    /**
     * category name with campaign context
     *
     * @return string
     */
     public function getNameContext() {
         $ids = Mage::helper('zolagocampaign')->getCampaignIdsFromUrl();
         $categoryIds = $this->getPathIds();
         $categoryIds[] = $this->getId();
         $vendor = Mage::helper('umicrosite')->getCurrentVendor();
         if (empty($ids)) {
             return $this->getLongName();
         }
         $resource = Mage::getResourceModel('zolagocampaign/campaign');
         if ($vendor) {
             $vendorId = $vendor->getId();
         } else {
             $vendorId = 0;
         }
         $categoryIds = array_unique($categoryIds);
         var_dump(array_unique($categoryIds));
         
         die();                  
     }

}