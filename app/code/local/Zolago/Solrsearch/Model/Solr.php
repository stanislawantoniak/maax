<?php
class Zolago_Solrsearch_Model_Solr extends SolrBridge_Solrsearch_Model_Solr
{
	
	const REGISTER_KEY = "current_solr_data";
	const FLAGS_FACET = "flags_facet";
    const SUPER_ATTRIBUTE_FACET = "flags_facet";
	const RATING_FACET = "product_rating_facet";
	const FLAGS_CODE = "flags";
	const RATING_CODE = "product_rating";
	const PRICE_CODE = "price";
	
	protected $_specialKeys = array(
		'is_new_facet',
		'is_bestseller_facet',
		'product_flag_facet'
	);
	
	protected $_currentCategory;
	protected $_fallbackCategoryId;
	protected $_originalQuery;
	/**
	 * !!!! Force fix - shame style !!!!
	 */
	public function __construct() {
		Mage::getSingleton('core/session')->setSolrFilterQuery(null);
		parent::__construct();
	}
	
	public function setOriginalQuery($query) {
	    $this->_originalQuery = $query;
	}
	public function setFallbackCategoryId($id) {
	    $this->_fallbackCategoryId = $id;
    }
	/**
	 * If relevance use solr score
	 * @param type $attributeCode
	 * @param type $direction
	 * @return boolean
	 */
	public function getSortFieldByCode($attributeCode, $direction){
		if($attributeCode=="relevance"){
			return false;
		}
		return parent::getSortFieldByCode($attributeCode, $direction);
	}
	
	public function setSolrData($data) {
        $this->_solrData = $data;
	}

	
	public function prepareFacetAndBoostFields() {
		parent::prepareFacetAndBoostFields();
		$this->facetFields[] = Zolago_Solrsearch_Model_Solr::FLAGS_FACET;
		// remove facets non existing in filters
		$availableFacets = $this->getAvailableFacets();
		$this->facetFields = array_intersect($this->facetFields,$availableFacets);
        $this->facetFields[] = "super_attribute_facet";
	}

	
    /**
     * facets available for category
     * @return array
     */
     public function getAvailableFacets() {
         $category = $this->getCurrentCategory();
         $facets = array('category_facet'); //always
         if ($category->getUsePriceFilter()) {
             $facets[] = Mage::helper('zolagosolrsearch')->getPriceFacet();
         }
         if ($category->getUseFlagFilter()) {
             $facets[] = Zolago_Solrsearch_Model_Solr::FLAGS_FACET;
         }
         if ($category->getUseReviewFilter()) {
             $facets[] = self::RATING_FACET;
         }
         $collection = Mage::getResourceModel('zolagocatalog/category_filter_collection');
         $collection->joinAttributeCode();
         $collection->addCategoryFilter($category);
         if (!count($collection)) {
             // check related category
             $related = $category->getRelatedCategory();
             if ($related) {
                 $collection = Mage::getResourceModel('zolagocatalog/category_filter_collection');
                 $collection->joinAttributeCode();
                 $collection->addCategoryFilter($related);
             }
         }
         foreach ($collection as $item) {
            $facets[]  = $item->getAttributeCode().'_facet';             
         }
         return $facets;
     }
	
    /**
     * add category id to search results
     */
    public function query($queryText,$params = array()) {
        $results = parent::query($queryText,$params);
        $category = $this->getCurrentCategory();
        if ($category) {	
            $results['responseHeader']['params']['category'] = (int)$category->getId();
        }
        if ($this->_fallbackCategoryId) {
            $results['responseHeader']['params']['fallbackCategory'] = (int)$this->_fallbackCategoryId;
        }
        $results['responseHeader']['params']['originalq'] = $this->_originalQuery;
        $this->_solrData = $results;
        return $results;
    }
	/**
	 * Solr query with register results
	 * @param array $queryText
	 * @param array $params
	 * @return array
	 */
	public function queryRegister($queryText, $params = array()) {
	    $this->setOriginalQuery($queryText);
		$profiler = Mage::helper("zolagocommon/profiler");
		/* @var $profiler Zolago_Common_Helper_Profiler */
		//$profiler->start();
		Mage::register(self::REGISTER_KEY, $this->query($queryText, $params));
		//$profiler->log("Lor loading");
		return Mage::registry(self::REGISTER_KEY);
	}
	
    public function prepareCleanFlagQueryData()
    {
        $this->prepareFieldList();
        if (!$this->isAutocomplete) {
            $this->preparePagingAndSorting();
        }
        $this->prepareFacetAndBoostFields();
		$this->prepareCleanFlagFilterQuery();
        $this->prepareSynonym();
        return $this;
    }	
	
	/**
	 * Prepare sorting ang pagin
	 * @return type
	 */
	public function preparePagingAndSorting() {
		// Sorting
		$sortOrder = $this->getListModel()->getCurrentOrder();
		$sortDir = $this->getListModel()->getCurrentDir();
		$this->sort = $this->getSortFieldByCode($sortOrder, $sortDir);
		
		// Paginaton
		$itemsPerPage = $this->getListModel()->getCurrentLimit();
		$start = $this->getListModel()->getCurrentStart();

		$this->start = $start;
        $this->rows = $itemsPerPage;
	}
	
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_List
	 */
	public function getListModel() {
		return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
	}
	
    /**
     * Prepare solr filter query paprams
     */
    protected function prepareFilterQuery()
    {
        $filterQuery = Mage::getSingleton('core/session')->getSolrFilterQuery();
        $standardFilterQuery = array();
        if ($standardFilterQuery = $this->getStandardFilterQuery()) {
            $filterQuery = $this->getStandardFilterQuery();
        }

        if (!is_array($filterQuery) || !isset($filterQuery)) {
            $filterQuery = array();
        }
        $defaultFilterQuery = array(
                'store_id' => array(Mage::app()->getStore()->getId()),
                'website_id' => array(Mage::app()->getStore()->getWebsiteId()),
                'product_status' => array(1)
        );
        $checkInstock =  (int) Mage::helper('solrsearch')->getSetting('check_instock');
        if ($checkInstock > 0) {
        	$defaultFilterQuery['instock_int'] = array(1);
        }

        $filterQuery = array_merge($filterQuery, $defaultFilterQuery);
        /**
         * Ignore the following section if the request is for autocomplete
         * The purpose is the speed up autocomplete
         */
         if (!$this->isAutocomplete) {

            if (in_array(Mage::app()->getRequest()->getRouteName(), array('catalog', 'umicrosite', 'orbacommon', 'solrsearch'))) {

                $_category = $this->getCurrentCategory();
                $currentCategoryId = $_category->getId();
				
				// In root sore category do not filter categories
				// Do not filter in vendor root to (only vendor filter is applayed @todo)
				if(!$this->isStoreRoot($_category) /* && !$this->isVendorRoot($_category) */){
						
					$_category = $this->getCurrentCategory();
					$currentCategoryId = $_category->getId();

					if (empty($filterQuery['category_id'])) {
						$filterQuery['category_id'] = array($currentCategoryId);
					}

					$filterQuery['filter_visibility_int'] = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

					//Check category is anchor
//					if ($_category->getIsAnchor()) {
//                        /** @var Mage_Catalog_Model_Resource_Category $res */
//                        $res = $_category->getResource();
//						$childrenIds = $res->getChildren($_category, false);
//
//						if (is_array($childrenIds) && isset($filterQuery['category_id']) && is_array($filterQuery['category_id'])) {
//							if (!isset($standardFilterQuery['category_id'])){
//								$filterQuery['category_id'] = array_merge($filterQuery['category_id'], $childrenIds);
//							}
//						}
//					}
				}
            };
        }
        $filterQueryArray = array();
		$extendedFilterQueryArray = array();
        $rangeFields = $this->rangeFields;
        foreach($filterQuery as $key=>$filterItem){
            //Ignore cateory facet - using category instead
            if ($key == 'category_facet') {
                continue;
            }

            $cats = array();
            if(is_array($filterItem) && sizeof($filterItem) > 0){
                $query = '';
				$extendedQuery = '';
                foreach($filterItem as $value){                
                    if ($key == 'price_decimal') {
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                    }else if($key == 'price'){
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
					} else if ($key == 'filter_slider_facet') {
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                    }
					/*else if(in_array($key, $this->_specialKeys, true)) {
						$extendedQuery .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
					}*/
                    else if($key == 'super_attribute_facet') {
                        $cats[] = "super_attribute_boost:%22".urlencode(trim(addslashes($value)))."%22";
                    }
                    else if($key == 'category_id') {
                        $cats[] = "category_id:%22{$value}%22";
                    }
					else{
                        $face_key = substr($key, 0, strrpos($key, '_'));
                        if ($key == 'price_facet') {
							$value = $this->_paresPriceValue($value);
							// price name - @refs SolrBridge_Solrsearch_Model_Solr_Query->getSortFieldByCode();
							$priceFields = Mage::helper('solrsearch')->getPriceFields();
							$priceName = 'sort_'.$priceFields[1];
                            $query .= $priceName.':['.urlencode(trim($value).'.99999').']+OR+';
                        }
                        else if(array_key_exists($face_key, $rangeFields))
                        {
                            $query .= $rangeFields[$face_key].':['.urlencode(trim(addslashes($value))).']+OR+';
                        }else{
                            $query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
                        }
                    }
                }
                if (!empty($cats)) {
                    $catIds = implode('+OR+', $cats);
                    $query .= "({$catIds})";
                }
				if ($query) {
					$query = trim($query, '+OR+');
					$filterQueryArray[] = $query;
				}

				
				if ($extendedQuery) {
					$extendedQuery				= trim($extendedQuery, '+OR+');
					$extendedFilterQueryArray[] = $extendedQuery;					
				}
            }
        }
		
		// Vendor context - add vendor/brandshop filter according to vendor type
		$_vendor = Mage::helper('umicrosite')->getCurrentVendor();
		if ($_vendor && $_vendor->getId()) {
			switch ($_vendor->getVendorType()) {
				case Zolago_Dropship_Model_Source::VENDOR_TYPE_BRANDSHOP:
					$filterQueryArray[] = 'udropship_brandshop_id_int'. ':' . $_vendor->getId();
				break;
				case Zolago_Dropship_Model_Source::VENDOR_TYPE_STANDARD:
					$filterQueryArray[] = 'udropship_vendor_id_int'. ':' . $_vendor->getId();
				break;
			}
		}
		
        $filterQueryString = '';

        if(count($filterQueryArray) > 0) {
            if(count($filterQueryArray) < 2) {
                $filterQueryString .= $filterQueryArray[0];
            }else{
                $filterQueryString .= '%28'.@implode('%29+AND+%28', $filterQueryArray);
            }
        }
        Mage::log($filterQueryArray);
        if(count($extendedFilterQueryArray) > 0) {
			$filterQueryString .= '%29+AND+%28'.@implode('+OR+', $extendedFilterQueryArray).'%29';
        } else {
			$filterQueryString .= '%29';
		}
        $this->filterQuery = $filterQueryString;
    }
	
	protected function _paresPriceValue($value) {
		$value = trim($value);
		if(preg_match("/^TO/", $value)){
			$value = "0 " . $value;
		}elseif(preg_match("/TO$/", $value)){
			$value = $value . " " . "2147483647"; // Max integer on 32bit
		}
		return $value;
	}
	
    /**
     * Prepare solr filter query paprams
     */
    protected function prepareCleanFlagFilterQuery()
    {
        $filterQuery = Mage::getSingleton('core/session')->getSolrFilterQuery();
        $standardFilterQuery = array();
        if ($standardFilterQuery = $this->getStandardFilterQuery()) {
            $filterQuery = $this->getStandardFilterQuery();
        }

        if (!is_array($filterQuery) || !isset($filterQuery)) {
            $filterQuery = array();
        }

        $defaultFilterQuery = array(
                'store_id' => array(Mage::app()->getStore()->getId()),
                'website_id' => array(Mage::app()->getStore()->getWebsiteId()),
                'product_status' => array(1)
        );
        $checkInstock =  (int) Mage::helper('solrsearch')->getSetting('check_instock');
        if ($checkInstock > 0) {
        	$defaultFilterQuery['instock_int'] = array(1);
        }

        $filterQuery = array_merge($filterQuery, $defaultFilterQuery);

        /**
         * Ignore the following section if the request is for autocomplete
         * The purpose is the speed up autocomplete
         */
		
        if (!$this->isAutocomplete) {

            if (in_array(Mage::app()->getRequest()->getRouteName(), array('catalog', 'umicrosite', 'orbacommon', 'solrsearch'))) {

                $_category = $this->getCurrentCategory();
                $currentCategoryId = $_category->getId();
				
				// In root sore category do not filter categories
				// Do not filter in vendor root to (only vendor filter is applayed @todo)
				if(!$this->isStoreRoot($_category) /* && !$this->isVendorRoot($_category)*/){
					if (empty($filterQuery['category_id'])) {
						$filterQuery['category_id'] = array($currentCategoryId);
					}


					$filterQuery['filter_visibility_int'] = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

					//Check category is anchor
					if ($_category->getIsAnchor()) {
						$childrenIds = $_category->getAllChildren(true);

						if (is_array($childrenIds) && isset($filterQuery['category_id']) && is_array($filterQuery['category_id'])) {
							if (!isset($standardFilterQuery['category_id'])){
								$filterQuery['category_id'] = array_merge($filterQuery['category_id'], $childrenIds);
							}
						}
					}
				}
            };
        }

        $filterQueryArray = array();
        $rangeFields = $this->rangeFields;

        foreach($filterQuery as $key=>$filterItem){
            //Ignore cateory facet - using category instead
            if ($key == 'category_facet') {
                continue;
            }

            if(is_array($filterItem) && sizeof($filterItem) > 0){
                $query = '';
				$extendedQuery = '';
                foreach($filterItem as $value){
                    if ($key == 'price_decimal') {
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                    }else if($key == 'price'){
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
					}
					/*else if(in_array($key, $this->_specialKeys, true)) {
						continue;
					}*/
                    else if($key == 'category_id') {
                        $cats[] = "category_id:%22{$value}%22";
                    }
					else{
                        $face_key = substr($key, 0, strrpos($key, '_'));
                        if ($key == 'price_facet') {
                            $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                        }
                        else if(array_key_exists($face_key, $rangeFields))
                        {
                            $query .= $rangeFields[$face_key].':['.urlencode(trim(addslashes($value))).']+OR+';
                        }else{
                            $query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
                        }
                    }
                }
                if (!empty($cats)) {
                    $catIds = implode('+OR+', $cats);
                    $query .= "({$catIds})";
                }
                if ($query) {
                    $query = trim($query, '+OR+');
                    $filterQueryArray[] = $query;
                }
            }
        }

		//Add Vendor Facet to Filter Microsite Products - Start
		$_vendor = Mage::helper('umicrosite')->getCurrentVendor();
		if ($_vendor && $_vendor->getId()) {
			$vendorQuery = 'udropship_vendor_id_int'.':%22'.urlencode(trim(addslashes($_vendor->getId()))).'%22+OR+';
			$vendorQuery = trim($vendorQuery, '+OR+');
			$filterQueryArray[] = $vendorQuery;
		}
		//Add Vendor Facet to Filter Microsite Products - End

        $filterQueryString = '';

        if(count($filterQueryArray) > 0) {
            if(count($filterQueryArray) < 2) {
                $filterQueryString .= $filterQueryArray[0];
            }else{
                $filterQueryString .= '%28'.@implode('%29+AND+%28', $filterQueryArray).'%29';
            }
        }

        $this->filterQuery = $filterQueryString;
    }
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCurrentCategory() {
		if(!$this->_currentCategory){
			$this->_currentCategory = $this->_getDefaultCategory();
		}
		return $this->_currentCategory;
	}
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function setCurrentCategory(Mage_Catalog_Model_Category $category) {
		$this->_currentCategory = $category;
		return $this;
	}
	
	/**
	 * @param Mage_Catalog_Model_Category $category
	 * @return bool
	 */
	public function isVendorRoot(Mage_Catalog_Model_Category $category) {
		return Mage::helper("zolagodropshipmicrosite")->getVendorRootCategoryObject()->getId()== $category->getId();
	}
	
	
	/**
	 * @param Mage_Catalog_Model_Category $category
	 * @return bool
	 */
	public function isStoreRoot(Mage_Catalog_Model_Category $category) {
		return $category->getId()==Mage::app()->getStore()->getRootCategoryId();
	}
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function _getDefaultCategory() {
		$_category = Mage::registry("current_category");
		if(!$_category){
			$_category = Mage::helper("zolagodropshipmicrosite")->getVendorRootCategoryObject();	
		}
		return $_category;
	}
	
	/**
	 * Fileds to listing show
	 */
	protected function prepareFieldList()
    {
        if (empty($this->fieldList))
        {
            $this->fieldList = array_merge(
				Mage::helper("zolagosolrsearch")->getSolrDocFileds(),		
				array(
					$this->priceFieldName
				)
			);
        }
    }
	
}