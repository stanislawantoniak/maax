<?php

/**
 * Solr helper
 *
 *
 * @category    Zolago
 * @package     Zolago_Solrsearch
 */
class Zolago_Solrsearch_Helper_Data extends Mage_Core_Helper_Abstract {

	const ZOLAGO_USE_IN_SEARCH_CONTEXT = 'use_in_search_context';
	protected $numFound;
	protected $_rootId;
	/**
	 * @var array
	 */
	protected $_solrToMageMap = array(
		"products_id" => "id",
		"product_type_static" => "type_id",
		"name_varchar" => "name",
		"store_id" => "store_id",
		"website_id" => "website_id",
		"category_id" => "category_ids",
		"sku_static" => "sku",
		"vsku_text" => "vsku",
		"in_stock_int" => "in_stock",
		"product_status" => "status",
		"image_varchar" => "image",
		"wishlist_count_int" => "wishlist_count",
		"tax_class_id_int" => "tax_class_id",
		"is_new_int" => "is_new",
		"product_rating_int" => "product_rating",
		"is_bestseller_int" => "is_bestseller",
		"product_flag_int" => "product_flag",
		"special_price_decimal" => "special_price",
		"special_from_date_varchar" => "special_from_date",
		"special_to_date_varchar" => "special_to_date",
        "campaign_regular_id_int" => "campaign_regular_id",
        "campaign_strikeout_price_type_int" => "campaign_strikeout_price_type",
		"udropship_vendor_id_int" => "udropship_vendor",
		"udropship_vendor_logo_varchar" => "udropship_vendor_logo",
		"udropship_vendor_url_key_varchar" => "udropship_vendor_url_key",
		"udropship_vendor_varchar" => "udropship_vendor_name",
		"manufacturer_logo_varchar" => "manufacturer_logo",
		"manufacturer_varchar" => "manufacturer"
	);

	/**
	 * @var array
	 */
	protected $_cores;

	/**
	 * @var array
	 */
	protected $_availableStoreIds;

	/**
	 * @param Mage_Catalog_Model_Product | null $product
	 * @param int $rootId
	 * @return type
	 */
	public function getDefaultCategory($product, $rootId) {

		/* @var $product Mage_Catalog_Model_Product */

		if(!$product){
			return null;
		}

        $category = null;
        // if no category, try to get category from product
		
		$catIds = $product->getCategoryIds();

		$collection = $this->getProductCategoriesCollection($catIds, $rootId);

		if($collection->count()) {
            // Get first category
			$category = $collection->getFirstItem();
            foreach($collection as $collectionItem){
                // Get first basic category if exist
                if($collectionItem->getData("basic_category")){
                    $category = $collectionItem;
                    break;
                }
            }
		}

        return $category;
	}

    /**
     * @param $catIds
     * @param $rootId
     * @return Mage_Catalog_Model_Resource_Category_Collection|Object
     * @throws Mage_Core_Exception
     */
    public function getProductCategoriesCollection($catIds, $rootId){
        $collection = Mage::getResourceModel('catalog/category_collection');
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection->addAttributeToSelect("basic_category");
        $collection->addAttributeToFilter("entity_id", array("in"=>$catIds));
        $collection->addAttributeToFilter("is_active", 1);
        $collection->addPathFilter("/$rootId/");
        return $collection;
    }
	
	/*
	 * @return int
	 */
	public function getRootCategoryId() {
		if (is_null($this->_rootId)) {
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            if ($vendor) {
                $rootId = Mage::helper('zolagodropshipmicrosite')->getVendorRootCategory(
					$vendor,
					Mage::app()->getWebsite()->getId()
				);
            } else {
                $rootId = Mage::app()->getStore()->getRootCategoryId();
            }
            $this->_rootId = $rootId;
        }
        return $this->_rootId;     
	}
	
	/**
	 * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
	 * @return array
	 */
	public function prepareAjaxProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {
		// Create product list
		$products = array();

		foreach ($listModel->getCollection() as $product) {
			/* @var $product Zolago_Solrsearch_Model_Catalog_Product */

			$_product[0] = $product->getId();
			$_product[1] = $product->getName();
//			$_product[2] = $this->_prepareCurrentUrl($product->getCurrentUrl());
            $_product[2] = $product->getCurrentUrl();
			$_product[3] = $product->getStrikeoutPrice();
			$_product[4] = $product->getFinalPrice();
			$_product[5] = $product->getWishlistCount();
			$_product[6] = $product->getInMyWishlist();
			$_product[7] = $this->_prepareListingResizedImageUrl($product->getListingResizedImageUrl());
			$imageSizes = $product->getListingResizedImageInfo();
			$_product[8] = !is_null($imageSizes) ? 100 * round(($imageSizes["height"] / $imageSizes["width"]),2) : 1;
			$_product[9] = $this->_prepareManufacturerLogoUrl($product->getManufacturerLogoUrl());

			$products[] = $_product;
		}

		return $products;
	}

	protected function _prepareCurrentUrl($url) {
		return str_replace(Mage::getBaseUrl(),"/",$url);
	}

	protected function _prepareListingResizedImageUrl($url) {
		return str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."catalog/product/cache/","",$url);
	}

	protected function _prepareManufacturerLogoUrl($url) {
		return str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."m-image/","",$url);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function processFinalParams(array $params = array(), $force = false) {

		// Unset positition if regular http request

		if (!Mage::helper("zolagocommon")->isGoogleBot() || $force) {

			$params['rows'] = null;
			$params['start'] = null;
			$params['page'] = null;
		}

		$params["_"] = null;

		return $params;
	}

	/**
	 *
	 * @param type $storeId
	 * @return type
	 */
	public function getCoresByStoreId($storeId) {
		$cores = array();
		foreach ($this->getCores() as $core => $data) {
			if (isset($data['stores'])) {
				$ids = explode(",", trim($data['stores'], ","));
				if (in_array($storeId, $ids)) {
					$cores[] = $core;
				}
			}
		}
		return $cores;
	}

	/**
	 * @return array
	 */
	public function getCores() {
		if (!$this->_cores) {
			$this->_cores = (array) Mage::getStoreConfig('solrbridgeindices', 0);
		}
		return $this->_cores;
	}

	/**
	 * @return array
	 */
	public function getAvailableCores() {
		$cores = array();
		foreach ($this->getCores() as $core => $data) {
			if (isset($data['stores'])) {
				$ids = array_filter(explode(",", trim($data['stores'], ",")));
				if (count($ids)) {
					$cores[$core] = true;
				}
			}
		}
		return array_keys($cores);
	}

	/**
	 * Returns vaialble stores (cores with has assigned store)
	 * @return array
	 */
	public function getAvailableStores() {
		if (!is_array($this->_availableStoreIds)) {
			$this->_availableStoreIds = array();
			foreach ($this->getCores() as $core => $data) {
				if (isset($data['stores'])) {
					$ids = explode(",", trim($data['stores'], ","));
					$this->_availableStoreIds = array_merge($this->_availableStoreIds, $ids);
				}
			}
			$this->_availableStoreIds = array_values(
					array_filter(array_unique($this->_availableStoreIds)));
		}
		return $this->_availableStoreIds;
	}

	public function getTreeCategoriesSelect($parentId, $level, $cat) {
		if ($level > 5) {
			return '';
		} // Make sure not to have an endless recursion
		$allCats = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addAttributeToFilter('is_active', '1')
				->addAttributeToFilter(self::ZOLAGO_USE_IN_SEARCH_CONTEXT, array('eq' => 1))
				->addAttributeToFilter('include_in_menu', '1');

		$html = '';

		if ($allCats->count() > 0) {

			foreach ($allCats as $category) {

				$html .= '<option value="' . $category->getId() . '" >' . str_repeat("&nbsp;", 4 * $level)
						. $category->getName() . "</option>";
			}
		}
		return $html;
	}

	public function getTreeCategories($parentId, $isChild) {

		$cats = array();
		$allCats = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addAttributeToFilter('is_active', '1')
				->addAttributeToFilter(self::ZOLAGO_USE_IN_SEARCH_CONTEXT, array('eq' => 1))
				->addAttributeToFilter('include_in_menu', '1')
				->addAttributeToFilter('parent_id', array('eq' => $parentId));

		foreach ($allCats as $category) {
			$cats[$category->getId()]['id'] = $category->getId();
			$cats[$category->getId()]['name'] = Mage::helper('catalog')->__($category->getName());
			$subCats = $category->getChildren();
			if (strlen($subCats) > 0) {
				$cats[$category->getId()]['sub'] = self::getTreeCategories($category->getId(), true);
			}
		}

		return $cats;
	}

	public function getContextUrl() {
		$uri = '/zolagosolrsearch/context';
		return $uri;
	}

	/**
	 * @param array $solrData
	 * @param string $queryText
	 * @param
	 * @return
	 */
	public function makeFallback($solrData, $queryText) {
		if (!$this->isFallbackNeeded($solrData, $queryText)) {
			return $solrData;
		}
		$solrModel = Mage::getModel('solrsearch/solr');
		$currentCategory = $solrModel->getCurrentCategory();
		$rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
		$newCategory = Mage::getModel('catalog/category')->load($rootCategoryId);
		$solrModel->setCurrentCategory($newCategory);
		Mage::unregister(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
		$solrModel->setFallbackCategoryId($currentCategory->getId());
		$newSolrData = $solrModel->queryRegister($queryText);
		$numFound = empty($solrData['response']['numFound']) ? 0 : $solrData['response']['numFound'];
		$newNumFound = empty($newSolrData['response']['numFound']) ? 0 : $newSolrData['response']['numFound'];
		if (!$this->isFallbackNeeded($newSolrData, $queryText) ||
				(!$numFound && $newNumFound)
		) {
			Mage::unregister('current_category');
			Mage::register('current_category', $newCategory);
			return $newSolrData;
		}
		// return back solrData
		Mage::unregister(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
		Mage::register(Zolago_Solrsearch_Model_Solr::REGISTER_KEY, $solrData);
		return $solrData;
	}

	/**
	 * check if search fallback is needed
	 * @param array $solrData
	 * @param string $queryText
	 * @return
	 */
	public function isFallbackNeeded($solrData, $queryText) {
		$realQuery = empty($solrData['responseHeader']['params']['q']) ? '' : $solrData['responseHeader']['params']['q'];
		$numFound = empty($solrData['response']['numFound']) ? 0 : $solrData['response']['numFound'];
		$vendor = Mage::helper('umicrosite')->getCurrentVendor();
		$in_category = false;
		if (empty($vendor) || !$vendor->getId()) { // not vendor context
			$rootCatId = Mage::app()->getStore()->getRootCategoryId();
			$category = $this->getCurrentCategory();
			if (!empty($category) && $category->getId() && ($rootCatId != $category->getId())) {

				$in_category = true;
			}
		}
		if (!empty($queryText) &&
				$in_category &&
				(!$numFound ||
				($realQuery != $queryText)
				)
		) {
			//
			return true;
		}
		return false;
	}

	/**
	 * Construct context search selector Array
	 * @param array $contextData
	 * @return array
	 */
	public function getContextSelectorArray($contextData = array()) {
		/** @var $this Zolago_Solrsearch_Helper_Data */
		/** @var Zolago_Dropship_Model_Vendor $_vendor */
		$array = array();

		/** @var Unirgy_DropshipMicrosite_Helper_Data $micrositeHelper */
		$micrositeHelper = Mage::helper('zolagodropshipmicrosite');
		$_vendor = $micrositeHelper->getCurrentVendor();

		$currentCategory = $this->getCurrentCategory();
		
		// Setup varnish context
		if(!$currentCategory && isset($contextData['category_id'])){
			$categoryCandidate = Mage::getModel("catalog/category")->
				load($contextData['category_id']);
			/* @var $categoryCandidate Mage_Catalog_Model_Category */
			if($categoryCandidate->getIsActive()){
				$currentCategory = $categoryCandidate;
			}
		}

		$array['select_options'] = array(array(
			'value' => 0,
			'text' => $this->__('Everywhere'),
			'selected' => true
		));

		// This vendor
		if ($_vendor && $_vendor->getId()) {
			/** @var Zolago_DropshipMicrosite_Helper_Data $helperZDM */
			$helperZDM = Mage::helper("zolagodropshipmicrosite");
			$vendorRootCategoryId = $helperZDM->getVendorRootCategoryObject()->getId();

			$array['select_options'][] = array(
				'value' => "{$vendorRootCategoryId}",
				//like 'everywhere' is root category ( zero ),
				//so when vendor is set the category is his root category
				'text' => $this->__('in ') . $_vendor->getVendorName(),
				'selected' => true,
			);

			$array['input_empty_text'] = $this->__('Search in ') . $_vendor->getVendorName() . '...';

			// Make "Everywhere" unselected
			$array['select_options'][0]['selected'] = false;
		} else {
			// Categories are only shown for global context and not for vendor context
			$allCats = Mage::getModel('catalog/category')->getCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('is_active', '1')
					->addAttributeToFilter(self::ZOLAGO_USE_IN_SEARCH_CONTEXT, array('eq' => 1))
					->addAttributeToFilter('include_in_menu', '1');

			foreach ($allCats as $category) {

				if ($currentCategory && $currentCategory->getId() == $category->getId()) {
					
				} else {
					$selected = false;

					$array['select_options'][] = array(
						'text' => $category->getName(),
						'value' => $category->getId(),
						'selected' => $selected
					);
				}
			}
			
			if ($currentCategory) {
				$rootCategory = Mage::app()->getStore()->getRootCategoryId();
				if ($rootCategory != $currentCategory->getId()) {

					$array['select_options'][] = array(
						'text' => $this->__('This category'),
						'value' => $currentCategory->getId(),
						'selected' => true
					);

					$array['input_empty_text'] = $this->__('Search in ') . $currentCategory->getName() . "...";

					// Make "Everywhere" unselected
					$array['select_options'][0]['selected'] = false;
				}
			}
		}
		return $array;
	}

	/**
	 * Retrive info from solar for sibling categories
	 *
	 * @return array
	 */
	public function getAllCatgoryData($parent_category, $rollback_category = NULL) {

		if ($all_data = Mage::registry('all_category_data')) {
			return $all_data;
		}

		$facetfield = 'category_facet';
		$all_data = array();

		// Get query
		$queryText = Mage::helper('solrsearch')->getParam('q');
		if (empty($queryText)) {
			$queryText = '*';
		}

		$solrModel = Mage::getModel('solrsearch/solr');

		// Set parent category
		$solrModel->setCurrentCategory($parent_category);

		$resultSet = $solrModel->query($queryText);

		// Rollback
		if ($rollback_category) {
			$solrModel->setCurrentCategory($rollback_category);
		}

		if (isset($resultSet['facet_counts']['facet_fields'][$facetfield]) && is_array($resultSet['facet_counts']['facet_fields'][$facetfield])) {
			$all_data = $resultSet['facet_counts']['facet_fields'][$facetfield];
		}

		if ($all_data) {
			Mage::register('all_category_data', $all_data);
		}

		return $all_data;
	}

	/**
	 * Map solr docuemnt data to local ORM product
	 * @param array $item
	 * @param Mage_Catalog_Model_Product $product
	 * @return Mage_Catalog_Model_Product
	 */
	public function mapSolrDocToProduct(array $item, Mage_Catalog_Model_Product $product) {

		foreach ($this->_solrToMageMap as $solr => $mage) {
			if (isset($item[$solr])) {
				$product->setDataUsingMethod($mage, $item[$solr]);
			}
		}

		$product->setId((int) $product->getId());

		return $product;
	}

	/**
	 * @return array
	 */
	public function getSolrDocFileds() {
		return array_keys($this->_solrToMageMap);
	}

	/**
	 * @param Mage_Catalog_Model_Product $model
	 * @return string | empty_string
	 */
	public function getListingResizedImageUrl(Mage_Catalog_Model_Product $model) {

		if (!$model->hasData("listing_resized_image_url")) {

			$return = null;
			try {
				$return = Mage::helper('catalog/image')->
						init($model, 'image')->
						keepAspectRatio(true)->
						constrainOnly(true)->
						keepFrame(false)->
						resize(300, null);
			} catch (Exception $ex) {
				Mage::logException($ex);
			}

			$model->setData("listing_resized_image_url", $return . ""); // Cast to string
		}

		return $model->getData("listing_resized_image_url");
	}

	/**
	 * @return int
	 */
	public function getNumFound() {
		if (is_null($this->numFound)) {
			$num = Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getCollection()->getSolrData("response", "numFound");
			if (is_numeric($num)) {
				$this->numFound = $num;
			} else {
				$this->numFound = 0;
			}
		}
		return $this->numFound;
	}

	/**
	 * check if misspeling was used
	 * @return bool
	 */
	public function isOriginalQuery() {
		$cpl = Mage::getSingleton('zolagosolrsearch/catalog_product_list');
		$collection = $cpl->getCollection();
		$query = $collection->getSolrData("responseHeader", "params", "q");
		$originalQuery = $collection->getSolrData("responseHeader", "params", "originalq");
		return (bool) ($query == $originalQuery);
	}

	/**
	 * get category used in fallback
	 * @return Mage_Catalog_Model_Category
	 */
	public function getFallbackCategory() {
		$cpl = Mage::getSingleton('zolagosolrsearch/catalog_product_list');
		$collection = $cpl->getCollection();
		$category = $collection->getSolrData("responseHeader", "params", "category");
		$fallbackCategory = $collection->getSolrData("responseHeader", "params", "fallbackCategory");
		if (is_int($fallbackCategory) && ($category !== $fallbackCategory)) {
			return Mage::getModel('catalog/category')->load($fallbackCategory);
		}
		return null;
	}

	public function getSolrRealQ() {
		/** @var Zolago_Solrsearch_Model_Catalog_Product_List $clp */
		$cpl = Mage::getSingleton('zolagosolrsearch/catalog_product_list');
		return $cpl->getCollection()->getSolrData("responseHeader", "params", "q");
	}

	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCurrentCategory() {
    	return Mage::registry('current_category');
	}

	/**
	 * @return string
	 */
	public function getQueryText() {
		return Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getQueryText();
	}

	/**
	 * prepare url for filter in header
	 * @param string $query
	 * @param Mage_Catalog_Model_Category $category     
	 * @param Zolago_Dropship_Model_Vendor $vendor
	 * @return string
	 */
	public function getFilterUrl($query, $category, $vendor) {
        /** @var Zolago_DropshipMicrosite_Helper_Data $helperZDM */
        $helperZDM = Mage::helper("zolagodropshipmicrosite");

		$final = array();

		if (empty($vendor)) {
			$final['_no_vendor'] = true;
		}
		if (!$category || !$category->getId()) {
			$category = null;
			if ($vendor) {
				$category = $helperZDM->getVendorRootCategoryObject($vendor);
			}
			if (!$category) {
				$categoryId = Mage::app()->getStore()->getRootCategoryId();
				$category = Mage::getModel('catalog/category')->load($categoryId);
			}
		}
		$cat = $category->getId();
		if ($query) {
			$final['_query'] = array(
				"q" => $query,
				"scat" => $cat,
			);
			$url = Mage::getUrl("search/index/index", $final);
		} else {
			if ($vendor) {
				// check if vendor category
				if ($category->getId() == $helperZDM->getVendorRootCategoryObject($vendor)->getId()) {
					return $vendor->getVendorUrl();
				}
			} else {
				// check if galery root category
				if ($category->getId() == Mage::app()->getStore()->getRootCategoryId()) {
					return Mage::getUrl('', $final);
				}
			}
			$url = $category->getUrl($final);
		}
		return $url;
	}
	
    /**
     * price facet name
     * @return string
     */
     public function getPriceFacet() {
         $app = Mage::app()->getStore();
         $code = $app->getCurrentCurrencyCode();
         $id = Mage::getSingleton('customer/session')->getCustomerGroupId();
         $prefix = SolrBridge_Base::getPriceFieldPrefix($code,$id);
         return $prefix.'_price_decimal';
    } 


}
