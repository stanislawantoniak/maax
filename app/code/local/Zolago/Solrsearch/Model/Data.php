<?php
/**
 * rewrited solrsearch data model
 */
class Zolago_Solrsearch_Model_Data extends SolrBridge_Solrsearch_Model_Data {
	
	protected $_tmpProduct;
	
	/**
	 * @param Varien_Object $product
	 * @return Zolago_Solrsearch_Model_Data
	 */
	public function prepareImproveFinalProductData(Varien_Object $product, &$docData) {
		return $this;
	}
	
	
	/**
	 * @return Mage_Catalog_Model_Product
	 */
	public function getTmpProduct() {
		if(!$this->_tmpProduct){
			$this->_tmpProduct =  Mage::getModel("catalog/product");
		}
		return $this->_tmpProduct;
	}
	
	public function getWeightAttributeCode() {
		return trim(Mage::helper('solrsearch')->getSetting('search_weight_attribute_code'));
	}
	
	public function getBrandAttributeCode() {
		return trim(Mage::helper('solrsearch')->getSetting('display_brand_suggestion'));
	}
	
	public function useInSugestions() {
		return Mage::helper('solrsearch')->getSetting('display_brand_suggestion');
	}
	
	public function processFinalItemData(Varien_Object $item) {
		$storeId = $item->getOrigData('store_id');
		$store = Mage::app()->getStore($storeId);
		$docData = array();
		//Remove store from Product Url
		$remove_store_from_url = Mage::helper('solrsearch')->getSetting('remove_store_from_url');

		if (intval($remove_store_from_url) > 0) {
//			$params['_store_to_url'] = false;
//			$productUrl = $_product->getUrlInStore($product, $params);
//			$baseurl = $store->getBaseUrl();
//			$productUrl = str_replace($baseurl, '/', $productUrl);
		}else{
//			$productUrl = $_product->getProductUrl();
		}
		
		$productUrl = Mage::getUrl("catalog/product/view", array("id"=>$item->getId()));
		
		// Url process @todo fix, appen in base colleciton
		if (strpos($productUrl, 'solrbridge.php')) {
			$productUrl = str_replace('solrbridge.php', 'index.php', $productUrl);
		}
		$docData['url_path_varchar'] = $productUrl;

		// Sku process
		$sku = $item->getOrigData("sku");
		$docData['sku_varchar'] = $sku;
		$docData['sku_boost'] = $sku;
		$docData['sku_boost_exact'] = $sku;
		$docData['sku_relative_boost'] = $sku;
		$this->pushTextSearchToObject ($item, $sku);
		$this->pushTextSearchToObject ($item, str_replace(array('-', '_'), '', $sku) );

		
		$productName = $item->getOrigData('name');
		
		$docData['name_varchar'] = $productName;
		$docData['name_boost'] = $productName;
		$docData['name_boost_exact'] = $productName;
		$docData['name_relative_boost'] = $productName;

		$docData['attribute_set_varchar'] = Mage::getModel('eav/entity_attribute_set')->
				load($item->getOrigData("attribute_set_id"))->getAttributeSetName();
		
		$this->pushTextSearchToObject($item, $docData['attribute_set_varchar']);
		$this->pushTextSearchToObject($item, $productName);

		// Load by primary colleciton
		$catIndexPosition = $item->getOrigData('cat_index_position');

		if (!empty($catIndexPosition) && is_numeric($catIndexPosition)) {
			$docData['sort_position_decimal'] = floatval($catIndexPosition);
		}else{
			$docData['sort_position_decimal'] = 0;
		}

		// @todo add later
		//$docData['sort_bestselling_decimal'] = $this->getProductOrderedQty($_product, $this->store);


		$docData['products_id'] = $item->getId();
		$docData['product_type_static'] = (string)$item->getOriginData("type_id");
		$docData['unique_id'] = $store->getId().'P'.$item->getId();
		if (!isset($docData['product_search_weight_int'])) {
			$docData['product_search_weight_int'] = 0;
		}

		$multipleStoreModeSetting = Mage::helper('solrsearch')->getSetting('multiplestore');
		if (intval($multipleStoreModeSetting) > 0) {//multiple store by different category root and different website
		    $docData['store_id'] = $store->getId();
		    $docData['website_id'] = $store->getWebsiteId();
		}else{
		    if(isset($docData['category_id']) && !empty($docData['category_id'])){
		        $docData['store_id'] = $store->getId();
		        $docData['website_id'] = $store->getWebsiteId();
		    }else{
		        $docData['store_id'] = 0;
		        $docData['website_id'] = 0;
		    }
		}

		$docData['filter_visibility_int'] = $item->getOrigData('visibility');

		$docData['instock_int'] = $item->getOrigData('stock_status');
		
		$docData['product_status'] = $item->getOrigData('status');
		
		$item->addData($docData);
	}
	
	/**
	 * 
	 * @param Varien_Object $item
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return \Zolago_Solrsearch_Model_Data
	 */
	public function afterLoadAttribute(
			Varien_Object $item, 
			Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj) {
		
		$storeId = $item->getOrigData('store_id');
		$tmpProduct = $this->getTmpProduct();
		
		$attributeObj->setStoreId($storeId);
		$backendType = $attributeObj->getBackendType();
		$frontEndInput = $attributeObj->getFrontendInput();
		$attributeCode = $attributeObj->getAttributeCode();
		$attributeData = $attributeObj->getData();
		$addData = array();
		
		// Set org data to template product
		$origValue = $item->getOrigData($attributeCode);

		$tmpProduct->setId($item->getId());
		$tmpProduct->setData($attributeCode, $origValue);

		if ($backendType == 'int') {
			$backendType = 'varchar';
		}

		$attributeKey = $attributeCode.'_'.$backendType;
		$attributeKeyFacets = $attributeCode.'_facet';

		$attributeVal = $attributeObj->getFrontEnd()->getValue($tmpProduct);

		if(is_array($attributeVal)){
			$attributeVal = implode(' ', $attributeVal);
		}


		//Generate sort attribute
		if ($attributeObj->getUsedForSortBy() && !empty($attributeVal)) {
			if (!empty($origValue)) {
				$addData['sort_'.$attributeCode.'_'.$backendType] = $origValue;
				//$docData[$attributeKey] = $sortValue;
				$addData[$attributeKey] = $attributeVal;
			}
		}
		
		//Generate product search weight value
		if ($attributeCode==$this->getWeightAttributeCode()) {
			if (!empty($attributeVal) && is_numeric($attributeVal)) {
				$docData['product_search_weight_int'] = $attributeVal;
			}
		}
		
		if (empty($attributeVal) || $attributeVal == 'No' || $attributeVal == 'None') {
			unset($addData[$attributeKey]);
			unset($addData[$attributeKeyFacets]);
			unset($addData[$attributeCode.'_boost']);
			unset($addData[$attributeCode.'_boost_exact']);
			unset($addData[$attributeCode.'_relative_boost']);
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
			
				
			if($attributeObj->getIsSearchable()){
				
				
				if (!$this->isInObject($item, $attributeVal) && $attributeVal != 'None' 
						&& $attributeCode != 'status' && $attributeCode != 'sku' && $attributeCode != 'price'){
					if (strlen($attributeVal) > 255) {
						$this->pushTextSearchToObject ($item, $attributeVal, 'textSearchText');
					}else{
						$this->pushTextSearchToObject ($item, $attributeVal);
					}
				}
			}

			if ($attributeObj->getIsFilterable() || $attributeObj->getIsFilterableInSearch()) {
				if ($backendType != 'text' && !in_array($attributeCode, $this->ignoreFields))
				{
					$addData[$attributeCode.'_boost'] = $attributeVal;
					$addData[$attributeCode.'_boost_exact'] = $attributeVal;
					$addData[$attributeCode.'_relative_boost'] = $attributeVal;
					$addData[$attributeCode.'_text'] = $attributeVal;
					$addData[$attributeKey] = $attributeVal;
					$this->pushTextSearchToObject ($item, $attributeObj->getStoreLabel() . ' ' . $attributeVal );
				}
			}
			
			if ($attributeObj->getData("solr_search_field_weight") || 
					$attributeObj->getData("solr_search_field_boost")){
				
				$addData[$attributeCode.'_boost'] = $attributeVal;
				$addData[$attributeCode.'_boost_exact'] = $attributeVal;
				$addData[$attributeCode.'_relative_boost'] = $attributeVal;
				$addData[$attributeKey] = $attributeVal;
			}
			
			if ($attributeObj->getIsFilterableInSearch()) {
				$addData[$attributeKeyFacets] = $attributeValFacets;
			}
		}
		
			
		if($addData){
			$item->addData($addData);
		}
		
		return $this;
	}
	
	/**
	 * @param Varien_Object $item
	 * @param string $string
	 * @param string $field
	 * @return bool
	 */
	public function isInObject(Varien_Object $item, $string, $field = "textSearch") {
		if($item->getOrignData($field)){
			return in_array($string, $item->getOrignData($field));
		}
		return false;
	}
	
	/**
	 * @param Varien_Object $item
	 * @param string $string
	 * @param string $field
	 */
	public function pushTextSearchToObject(Varien_Object $item, $string, $field = "textSearch") {
		$texts = $item->getData($field);
		if(!is_array($texts)){
			$texts = array();
		}
		$texts[] = $string;
		$item->setData($field, $texts);
	}
	
	/**
	 * Fix instock_int param
	 * @param type $_product
	 * @param int $docData
	 */
	public function prepareFinalProductData($_product, &$docData){
		parent::prepareFinalProductData($_product, $docData);
		if($docData['instock_int']==0){
			try{
				$stock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $_product );
				if ($stock->getIsInStock() /* && $stock->getQty() > 0*/) {
					$docData['instock_int'] = 1;
				} 
			}
			catch (Exception $e){}
		}
	}
	
	public function getProductOrderedQty($_product, $store)
	{
		$visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds();
		if ($_product->getId() && in_array($_product->getVisibility(), $visibility) && $_product->getStatus())
		{
			$oldStore = Mage::app ()->getStore ();
			
			// Fix dla plaskiego: zmiana store view w adminie powduje przelaczenie 
			// ktore przestaje korzysatac z EAV tylko plaskiego katalog
			// metoda addOrderedQty nastepnie przywraca tabelke eav (catalog_product_entity)
			// a potem odaje atrybuty jak do plskiego katalogu
			
			//Mage::app ()->setCurrentStore ( $store );

			$storeId    = $store->getId();
			$products = Mage::getResourceModel('reports/product_collection')
				->addOrderedQty()
				->addAttributeToSelect(array('name')) //edit to suit tastes
				->setStoreId($storeId)
				->addStoreFilter($storeId)
				->addIdFilter($_product->getId())->setOrder('ordered_qty', 'desc'); //best sellers on top
			$data = $products->getFirstItem()->getData();

			//Mage::app ()->setCurrentStore ( $oldStore );

			if(isset($data['ordered_qty']) && (int) $data['ordered_qty'])
			{
				return (int)$data['ordered_qty'];
			}
		}else{
			return 0;
		}
		return 0;
	}
	
	/**
	 * Get allow categories by store
	 * @param Mage_Core_Model_Store $store
	 * @return array
	 */
	public function getAllowCategoriesByStore($store)
	{
		$cachedKey = 'solrbridge_solrsearch_indexing_allowcategories_' . $store->getId ();

		$useCache = Mage::app()->useCache('solrbridge_solrsearch');
		
		if ((false !== ($returnData = Mage::app ()->getCache ()->load ( $cachedKey ))) && $useCache) {
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

		if (! empty ( $allowCatIds ) && $useCache) {
			Mage::app ()->getCache ()->save ( serialize ( $allowCatIds ), $cachedKey, array ('SOLRBRIDGE_SOLRSEARCH') );
		}

		return $allowCatIds;
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
		$categoryModel = Mage::getModel('catalog/category');
		$tmp = array();
		foreach ($cats as $catid) {
		    $category = $categoryModel->load($catid);
		    $parents = $category->getParentCategories();
		    foreach ($parents as $parent) {
		        if ($parent->getIsAnchor()) {
        		    $tmp[] = $parent->getId();
                }
            }
		}
		$cats = array_unique(array_merge($cats,$tmp));
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

}