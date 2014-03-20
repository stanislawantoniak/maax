<?php
/**
 * @category SolrBridge
 * @package SolrBridge_Solrsearch
 * @author	Hau Danh
 * @copyright	Copyright (c) 2011-2014 Solr Bridge (http://www.solrbridge.com)
 *
 */
class SolrBridge_Solrsearch_Model_Data
{
	public $ignoreFields = array();

	public $includedBrandAttributeCodes = array();

	public $includedSearchWeightAttributeCodes = array();

	public $textSearch = array();

	public $textSearchText = array();

	public $store = null;

	public $allowCategoryIds = array();

	public function setBrandAttributes($attributes)
	{
		$this->includedBrandAttributeCodes = $attributes;
	}

	public function setSearchWeightAttributes($attributes)
	{
		$this->includedSearchWeightAttributeCodes = $attributes;
	}

	public function setIgnoreFields($ignoreFields)
	{
		$this->ignoreFields = $ignoreFields;
	}

	public function setStore($store)
	{
		$this->store = $store;
	}

	public function setAllowCategoryIds($ids)
	{
		$this->allowCategoryIds = $ids;
	}

	public function getTextSearch()
	{
		return $this->textSearch;
	}

	public function getTextSearchText()
	{
		return $this->textSearchText;
	}

	public function pushTextSearch($rawstring) {
		if (! empty ( $rawstring )) {
			$string = trim ( $rawstring );
			if (! in_array ( $string, $this->textSearch )) {
				$this->textSearch [] = $string;
			}
		}
	}

	public function pushTextSearchText($rawstring) {
		if (! empty ( $rawstring )) {
			$string = trim ( $rawstring );
			if (! in_array ( $string, $this->textSearchText )) {
				$this->textSearchText [] = $string;
			}
		}
	}

	public function isAttributeIgnore($atributeObj)
	{
		$attributeCode = $atributeObj->getAttributeCode();

		if (!$atributeObj->getIsSearchable() && !in_array($attributeCode, $this->includedSearchWeightAttributeCodes)) // ignore fields which are not searchable or not used as product search weight
		{
			if (!empty($this->includedBrandAttributeCodes)  && in_array($attributeCode, $this->includedBrandAttributeCodes))
			{
				return false;
			} else {
				return true;//Mean that the attribute will be ignored
			}
		}
		return false;
	}

	public function getProductAttributesData($_product)
	{
		$docData = array();

		foreach ($_product->getAttributes() as $atributeObj){
			$backendType = $atributeObj->getBackendType();
			$frontEndInput = $atributeObj->getFrontendInput();
			$attributeCode = $atributeObj->getAttributeCode();
			$attributeData = $atributeObj->getData();

			if ($this->isAttributeIgnore($atributeObj)) {
			    continue;
			}

			if ($backendType == 'int') {
				$backendType = 'varchar';
			}

			$attributeKey = $attributeCode.'_'.$backendType;

			$attributeKeyFacets = $attributeCode.'_facet';

			if (!is_array($atributeObj->getFrontEnd()->getValue($_product))){
				$attributeVal = strip_tags($atributeObj->getFrontEnd()->getValue($_product));
			} else {
				$attributeVal = $atributeObj->getFrontEnd()->getValue($_product);
				$attributeVal = implode(' ', $attributeVal);
			}

			if ($_product->getData($attributeCode) == null)
			{
				$attributeVal = null;
			}

			//Generate sort attribute
			if ($atributeObj->getUsedForSortBy() && !empty($attributeVal)) {
				$sortValue = $_product->getData($attributeCode);
				if (!empty($sortValue)) {
					$docData['sort_'.$attributeCode.'_'.$backendType] = $sortValue;
				}
			}

			//Generate product search weight value
			if (in_array($attributeCode, $this->includedSearchWeightAttributeCodes)) {
				if (!empty($attributeVal) && is_numeric($attributeVal)) {
					$docData['product_search_weight_int'] = $attributeVal;
					if ( !$atributeObj->getIsSearchable() )
					{
						continue;
					}
				}
			}

			//Start collect values
			if (empty($attributeVal) || $attributeVal == 'No' || $attributeVal == 'None') {
				unset($docData[$attributeKey]);
				unset($docData[$attributeKeyFacets]);
				unset($docData[$attributeCode.'_boost']);
				unset($docData[$attributeCode.'_boost_exact']);
				unset($docData[$attributeCode.'_relative_boost']);
			}else{
				$attributeValFacets = array();
				if($frontEndInput == 'multiselect') {
					$attributeValFacetsArray = @explode(',', $attributeVal);
					$attributeValFacets = array();
					foreach ($attributeValFacetsArray as $val) {
						$attributeValFacets[] = trim($val);
					}
				} else {
					$attributeValFacets[] = trim($attributeVal);
				}

				if ($backendType == 'datetime') {
					$attributeVal = date("Y-m-d\TG:i:s\Z", $attributeVal);
				}

				if (!in_array($attributeVal, $this->textSearch) && $attributeVal != 'None' && $attributeCode != 'status' && $attributeCode != 'sku'){
					if (strlen($attributeVal) > 255) {
						$this->pushTextSearchText ( $attributeVal );
					}else{
						$this->pushTextSearch ( $attributeVal );
						$this->pushTextSearch ( $atributeObj->getStoreLabel().' '.$attributeVal );
					}
				}

				if ($backendType != 'text' && !in_array($attributeCode, $this->ignoreFields)) {
					$docData[$attributeCode.'_boost'] = $attributeVal;

					$docData[$attributeCode.'_boost_exact'] = $attributeVal;

					$docData[$attributeCode.'_relative_boost'] = $attributeVal;

					$docData[$attributeCode.'_text'] = $attributeVal;

					$docData[$attributeKey] = $attributeVal;
				}

				if (
				(isset($attributeData['solr_search_field_weight']) && !empty($attributeData['solr_search_field_weight']))
				||
				(isset($attributeData['solr_search_field_boost']) && !empty($attributeData['solr_search_field_boost']))
				) {
					$docData[$attributeCode.'_boost'] = $attributeVal;
					$docData[$attributeCode.'_boost_exact'] = $attributeVal;
					$docData[$attributeCode.'_relative_boost'] = $attributeVal;
				}

				if (
				(isset($attributeData['is_filterable_in_search']) && !empty($attributeData['is_filterable_in_search']) && $attributeValFacets != 'No' && $attributeKey != 'price_decimal' && $attributeKey != 'special_price_decimal')
				) {
					$docData[$attributeKeyFacets] = $attributeValFacets;
				}
			}
		}
		return $docData;
	}

	public function prepareCategoriesData($_product, &$docData)
	{
		$store = $this->store;

		//is category name searchable
		$solr_include_category_in_search = Mage::helper('solrsearch')->getSetting('solr_search_in_category');
		//use category for facets
		$use_category_as_facet = Mage::helper('solrsearch')->getSetting('use_category_as_facet');

		//Calculate allow categories
		if( !isset($this->allowCategoryIds[$store->getId()]) )
		{
			$this->allowCategoryIds[$store->getId()] = ($allowCatIds = $this->getAllowCategoriesByStore($store))?$allowCatIds:array();
		}

		$cats = $_product->getCategoryIds();
		$catNames = array();
		$categoryPaths = array();
		$categoryIds = array();
		foreach ($cats as $category_id) {
			$storeid = $this->store->getId();
			if (in_array($category_id, $this->allowCategoryIds[$storeid])) {
				$_cat = Mage::getModel('catalog/category')->setStoreId($storeid)->load($category_id) ;
				if ( $_cat && $_cat->getIsActive() && $_cat->getIncludeInMenu() ) {
					$catNames[] = $_cat->getName().'/'.$_cat->getId();
					$categoryPaths[] = $this->getCategoryPath($_cat, $this->store);
					$categoryIds[] = $_cat->getId();
				}
			}

		}

		if ($use_category_as_facet) {
			$docData['category_facet'] = $catNames;
			$docData['category_text'] = $catNames;
			$docData['category_boost'] = $catNames;
			$docData['category_boost_exact'] = $catNames;
			$docData['category_relative_boost'] = $catNames;
		}
		$docData['category_path'] = $categoryPaths;
		$docData['category_id'] = $categoryIds;

		//Extend text search
		if ($solr_include_category_in_search > 0) {
			$this->textSearch = array_merge($this->textSearch, $catNames);
		}
		return array(
				'catNames' => $catNames,
				'catPaths' => $categoryPaths,
				'catIds'   => $categoryIds,
		);
	}

	public function prepareTagsData($_product, &$docData)
	{
		//use tags for search and facets
		$use_tags_for_search = Mage::helper('solrsearch')->getSetting('use_tags_for_search');

		//Use product tags for search
		if ($use_tags_for_search) {
			$tagNames = $this->getProductTags($_product);
			$docData['product_tags_facet'] = $tagNames;
			$docData['product_tags_boost'] = $tagNames;
			$docData['product_tags_boost_exact'] = $tagNames;
			$docData['product_tags_relative_boost'] = $tagNames;
			$this->textSearch = array_merge($this->textSearch, $tagNames);
		}
	}
	/**
	 * Get product tags
	 * @param unknown $product
	 * @return multitype:NULL
	 */
	public function getProductTags($product)
	{
		$productId = $product->getId();
		$tags = array();
		if ($productId > 0) {
			$collection = Mage::getModel('tag/tag')
			->getResourceCollection()
			->addProductFilter($productId)
			->addPopularity()
			->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED);
			foreach ($collection as $tag) {
				$tags[] = $tag->getName();
			}
		}
		return $tags;
	}

	/**
	 * Get category path
	 * @param unknown_type $category
	 */
	public function getCategoryPath($category, $store){
		$categoryPath = str_replace('/', '_._._',$category->getName()).'/'.$category->getId();
		while ($category->getParentId() > 0){
			$parentCategory = $category->getParentCategory();
			$category = Mage::getModel('catalog/category')->setStoreId($store->getId())->load($parentCategory->getId());
			if (in_array($category->getId(), $this->allowCategoryIds[$store->getId()]))
			{
				if ( $category && $category->getIsActive() && $category->getIncludeInMenu() ) {
					$categoryPath = str_replace('/', '_._._',$category->getName()).'/'.$category->getId().'/'.$categoryPath;
				}
			}
		}
		return trim($categoryPath, '/');
	}

	/**
	 * Get category path
	 * @param unknown_type $category
	 */
	public function getCategoryPathBk($category, $store)
	{

		$categoryPath = '';

		$parentCategories = $category->getParentCategories();

		foreach ($parentCategories as $parentCat)
		{
			if (in_array($parentCat->getId(), $this->allowCategoryIds[$store->getId()]))
			{
				if ( $parentCat && $parentCat->getIsActive())
				{
					$categoryPath .= '/'.str_replace('/', '_._._',$parentCat->getName()).'/'.$parentCat->getId();
				}
			}
		}

		return trim($categoryPath, '/');
	}

	/**
	 * Get allow categories by store
	 * @param Mage_Core_Model_Store $store
	 * @return array
	 */
	public function getAllowCategoriesByStore($store)
	{
		$cachedKey = 'solrbridge_solrsearch_indexing_allowcategories_' . $store->getId ();

		if (false !== ($returnData = Mage::app ()->getCache ()->load ( $cachedKey ))) {
			return unserialize ( $returnData );
		}

		$rootCatId = $store->getRootCategoryId();

		$rootCat = Mage::getModel('catalog/category')->load($rootCatId);

		$allowCatIds = Mage::getModel('catalog/category')->getResource()->getChildren($rootCat, true);

		$excludedCategoriesIds = Mage::helper('solrsearch')->getSetting('excluded_categories');
		$excludedCategoriesIdsArray = array();

		if (!empty($excludedCategoriesIds)) {

			$excludedCategoriesIdsArray = explode(',', trim($excludedCategoriesIds, ','));
			//Loaded categories recusive for excluding
			$recusiveExcludedCategory = Mage::helper('solrsearch')->getSetting('excluded_categories_recusive');

			if (isset($recusiveExcludedCategory) && intval($recusiveExcludedCategory) > 0) {

				$excludedChildrenCategoriesIdsArray = array();

				foreach ( $excludedCategoriesIdsArray as $catId ) {
					$parentCat = Mage::getModel('catalog/category')->load($catId);
					$excludedChildrenCategoriesIds = Mage::getModel('catalog/category')->getResource()->getChildren($parentCat, true);
					if (count($excludedChildrenCategoriesIds)) {
						$excludedChildrenCategoriesIdsArray = array_merge($excludedChildrenCategoriesIdsArray, $excludedChildrenCategoriesIds);
					}
				}
				//Merge categories id from settings and its children,
				$excludedCategoriesIdsArray = array_merge($excludedCategoriesIdsArray, $excludedChildrenCategoriesIdsArray);
			}

			if (count($excludedCategoriesIdsArray)) {
				$allowCatIds = array_diff($allowCatIds, $excludedCategoriesIdsArray);
			}
		}

		if (! empty ( $allowCatIds )) {
			Mage::app ()->getCache ()->save ( serialize ( $allowCatIds ), $cachedKey, array ('solrbridge_solrsearch_indexing') );
		}

		return $allowCatIds;
	}

	public function prepareFinalProductData($_product, &$docData)
	{
		$store = $this->store;
		//Remove store from Product Url
		$remove_store_from_url = Mage::helper('solrsearch')->getSetting('remove_store_from_url');

		if (intval($remove_store_from_url) > 0) {
			$params['_store_to_url'] = false;
			$productUrl = $_product->getUrlInStore($product, $params);
			$baseurl = $store->getBaseUrl();
			$productUrl = str_replace($baseurl, '/', $productUrl);
		}else{
			$productUrl = $_product->getProductUrl();
		}

		if (strpos($productUrl, 'solrbridge.php')) {
			$productUrl = str_replace('solrbridge.php', 'index.php', $productUrl);
		}

		$sku = $_product->getSku();
		$this->pushTextSearch ( $sku );
		$this->pushTextSearch ( str_replace(array('-', '_'), '', $sku) );

		$docData['url_path_varchar'] = $productUrl;
		$productName = $_product->getName();
		$docData['name_boost'] = $productName;
		$docData['name_boost_exact'] = $productName;
		$docData['name_relative_boost'] = $productName;

		$this->pushTextSearch ( $productName );

		$catIndexPosition = $_product->getData('cat_index_position');

		if (!empty($catIndexPosition) && is_numeric($catIndexPosition)) {
			$docData['sort_position_decimal'] = floatval($catIndexPosition);
		}else{
			$docData['sort_position_decimal'] = 0;
		}

		$docData['sort_bestselling_decimal'] = $this->getProductOrderedQty($_product, $this->store);


		$docData['products_id'] = $_product->getId();
		$docData['product_type_static'] = (string)$_product->getTypeId();
		$docData['unique_id'] = $store->getId().'P'.$_product->getId();
		if (!isset($docData['product_search_weight_int'])) {
			$docData['product_search_weight_int'] = 0;
		}
		$docData['store_id'] = $store->getId();
		$docData['website_id'] = $store->getWebsiteId();

		$docData['filter_visibility_int'] = $_product->getVisibility();

		$stock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $_product );
		if ($stock->getIsInStock()) {
			$docData['instock_int'] = 1;
		} else {
			$docData['instock_int'] = 0;
		}

		$docData['product_status'] = $_product->getStatus();
	}

	public function getProductOrderedQty($_product, $store)
	{
		$visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds();
		if ($_product->getId() && in_array($_product->getVisibility(), $visibility) && $_product->getStatus())
		{
			$oldStore = Mage::app ()->getStore ();
			Mage::app ()->setCurrentStore ( $store );

			$storeId    = $store->getId();
			$products = Mage::getResourceModel('reports/product_collection')
			->addOrderedQty()
			->addAttributeToSelect(array('name')) //edit to suit tastes
			->setStoreId($storeId)
			->addStoreFilter($storeId)
			->addIdFilter($_product->getId())->setOrder('ordered_qty', 'desc'); //best sellers on top
			$data = $products->getFirstItem()->getData();

			Mage::app ()->setCurrentStore ( $oldStore );

			if(isset($data['ordered_qty']) && (int) $data['ordered_qty'])
			{
				return (int)$data['ordered_qty'];
			}
		}else{
			return 0;
		}
		return 0;
	}
}