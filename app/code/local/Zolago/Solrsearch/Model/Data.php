<?php
/**
 * rewrited solrsearch data model
 */
class Zolago_Solrsearch_Model_Data extends SolrBridge_Solrsearch_Model_Data {
	
	/**
	 * @var Mage_Catalog_Model_Product
	 */
	protected $_tmpProduct;
	
	/**
	 * @var Mage_Customer_Model_Resource_Group_Collection 
	 */
	protected $_groupCollection;
	
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
	
	/**
	 * @return Mage_Customer_Model_Resource_Group_Collection
	 */
	protected function _getGroupCollection() {
		if(!$this->_groupCollection){
			$this->_groupCollection = Mage::getResourceModel('customer/group_collection')
				->addTaxClass();
		}
		return $this->_groupCollection;
	}
	
	/**
	 * 
	 * @param Varien_Object $item
	 * @return \Zolago_Solrsearch_Model_Data
	 */
	public function processPriceData(Varien_Object $item) {
		
		$product = $this->getTmpProduct();
		$product->setData($item->getOrigData());
		$product->setId($item->getId());
		
		$customerGroupCollection = $this->_getGroupCollection();

		$storeObject = Mage::app()->getStore($item->getOrigData('store_id'));
		
		$currenciesCode = $storeObject->getAvailableCurrencyCodes(true);
		$data = array();
			
		foreach ($currenciesCode as $currencycode)
		{
			$currency  = Mage::getModel('directory/currency')->load($currencycode);
			$storeObject->setData('current_currency', $currency);
			
			foreach ($customerGroupCollection as $group){

				$price = 0;//including tax
				$specialPrice = 0;//including tax
				$sortSpecialPrice = 0;

				$returnData = Mage::getModel('solrsearch/price')->
						getProductPrice($product, $storeObject, $group->getId());

				if (isset($returnData['price']) && $returnData['price'] > 0) {
				    $price = $returnData['price'];
				}
				if (isset($returnData['special_price']) && $returnData['special_price'] > 0) {
					$specialPrice = $returnData['special_price'];
				}

				$code = SolrBridge_Base::getPriceFieldPrefix($currencycode, $group->getId());

				$data[$code.'_price_decimal'] = $price;
				$data[$code.'_special_price_decimal'] = $specialPrice;

				$data['sort_'.$code.'_special_price_decimal'] = ($specialPrice > 0)?$specialPrice:$price;

				$specialPriceFromDate = 0;

				$specialPriceToDate = 0;

				if ($specialPrice > 0 && isset($returnData['product']) && is_object($returnData['product'])) {
					$specialPriceFromDate = $returnData['product']->getSpecialFromDate();
					$specialPriceToDate = $returnData['product']->getSpecialToDate();
				}
				if ($specialPriceFromDate > 0 && $specialPriceToDate > 0) {
					$data[$code.'_special_price_fromdate_int'] = strtotime($specialPriceFromDate);
					$data[$code.'_special_price_todate_int'] = strtotime($specialPriceToDate);
				}else{
					if (isset($returnData['special_price_from_time']) && $returnData['special_price_from_time'] > 0) {
						$data[$code.'_special_price_fromdate_int'] = $returnData['special_price_from_time'];
					}
					if (isset($returnData['special_price_to_time']) && $returnData['special_price_to_time'] > 0) {
						$data[$code.'_special_price_todate_int'] = $returnData['special_price_to_time'];
					}
				}

				if(isset($data[$code.'_special_price_fromdate_int']) && !is_numeric($data[$code.'_special_price_fromdate_int'])){
					//$data[$code.'_special_price_fromdate_int'] = strtotime($data[$code.'_special_price_fromdate_int']);
					$data[$code.'_special_price_fromdate_int'] = 0;
				}
				if(isset($data[$code.'_special_price_todate_int']) && !is_numeric($data[$code.'_special_price_todate_int'])){
					//$data[$code.'_special_price_todate_int'] = strtotime($data[$code.'_special_price_todate_int']);
					$data[$code.'_special_price_fromdate_int'] = 0;
				}

				if (!isset($data[$code.'_special_price_fromdate_int'])) {
					$data[$code.'_special_price_fromdate_int'] = 0;
				}
				if (!isset($data[$code.'_special_price_todate_int'])) {
					$data[$code.'_special_price_todate_int'] = 0;
				}
			}
		}
		
		$item->addData($data);
		
		return $this;
	}
	
	/**
	 * @param Varien_Data_Collection $collection
	 * @return Zolago_Solrsearch_Model_Data
	 */
	public function addTaxPercents(Varien_Data_Collection $collection, $storeId=null) {
		
		$helper = Mage::helper('tax');
        if (!$helper->needPriceConversion($storeId)) {
            return $this;
        }
		
		$request = Mage::getSingleton('tax/calculation')->getRateRequest();
		$classToRate = array();
		foreach ($collection as $item) {
			/* @var $item Varien_Object */
			$taxClassId = $item->getOrigData('tax_class_id');
			if (null === $taxClassId) {
				$item->setOrigData('tax_percent', 0);
			}
			if (!isset($classToRate[$taxClassId])) {
				$request->setProductClassId($taxClassId);
				$classToRate[$taxClassId] = Mage::getSingleton('tax/calculation')->getRate($request);
			}
			$item->setOrigData('tax_percent', $classToRate[$taxClassId]);
		}
		return $this;
	}
	
	/**
	 * @param Varien_Object $item
	 * @return Zolago_Solrsearch_Model_Data
	 */
	public function processFinalItemData(Varien_Object $item) {
		$storeId = $item->getOrigData('store_id');
		$store = Mage::app()->getStore($storeId);
		$docData = array();
		//Remove store from Product Url
		$remove_store_from_url = Mage::helper('solrsearch')->getSetting('remove_store_from_url');
		
		
		// Url process 
		if($path =$item->getOrigData('request_path')){
			$productUrl = $path;
			if(!$remove_store_from_url){
				$productUrl = $store->getBaseUrl() . $productUrl;
			}else{
				$productUrl = "/" . $productUrl;
			}
		}else{
			if($remove_store_from_url){
				$productUrl = "catalog/product/view/id/" . $item->getId();
			}else{
				$productUrl = $store->getUrl("catalog/product/view", array("id"=>$item->getId()));
			}
		}
		
		if (strpos($productUrl, 'solrbridge.php')) {
			$productUrl = str_replace('solrbridge.php', 'index.php', $productUrl);
		}
		$docData['url_path_varchar'] = $productUrl;

		// Sku process
		$sku = $item->getOrigData("sku");
		$docData['sku_static'] = $sku;
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
			$docData['sort_position_decimal'] = 1;
		}

		// @todo add later
		//$docData['sort_bestselling_decimal'] = $this->getProductOrderedQty($_product, $this->store);


		$docData['products_id'] = $item->getId();
		$docData['product_type_static'] = (string)$item->getOrigData("type_id");
		$docData['unique_id'] = $store->getId().'P'.$item->getId();
		if (!isset($docData['product_search_weight_int'])) {
			$docData['product_search_weight_int'] = 0;
		}

		$multipleStoreModeSetting = Mage::helper('solrsearch')->getSetting('multiplestore');
		if (intval($multipleStoreModeSetting) > 0) {//multiple store by different category root and different website
		    $docData['store_id'] = $store->getId();
		    $docData['website_id'] = $store->getWebsiteId();
		}else{
		    if($item->getData('category_id')){
		        $docData['store_id'] = $store->getId();
		        $docData['website_id'] = $store->getWebsiteId();
		    }else{
		        $docData['store_id'] = 0;
		        $docData['website_id'] = 0;
		    }
		}
		
		if(!$item->hasData('textSearchText')){
			$item->setData('textSearchText', array());
		}

		
		// Unique data
		foreach($item->getData() as $key=>$value){
			if(is_array($value) && preg_match("/_facet$/", $key)){
				$item->setData($key, array_unique($value));
			}
		}

		$docData['filter_visibility_int'] = $item->getOrigData('visibility');
		$docData['instock_int'] = $item->getOrigData('stock_status');
		$docData['product_status'] = $item->getOrigData('status');
		
		$docData['textSearchStandard'] = $item->getData('textSearch');
		
		
		$item->addData($docData);
		
		// Finally clear id
		$item->unsetData('id');
		return $this;
	}
	

	/**
	 * @param Varien_Object $item
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj
	 * @return \Zolago_Solrsearch_Model_Data
	 */
	protected function _processAttributeData(Varien_Object $item, 
			Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj) {
		
		$storeId = $item->getOrigData('store_id');
		
		$attributeObj->setStoreId($storeId);
		$backendType = $attributeObj->getBackendType();
		$frontEndInput = $attributeObj->getFrontendInput();
		$attributeCode = $attributeObj->getAttributeCode();
		$helper = Mage::helper('core');
		$addData = array();
		
		// Set org data to template product
		$origValue = $item->getOrigData($attributeCode);

		if ($backendType == 'int') {
			$backendType = 'varchar';
		}

		$attributeKey = $attributeCode.'_'.$backendType;
		$attributeKeyFacets = $attributeCode.'_facet';

		$attributeVal = $this->_getAttributeValue($attributeObj, $item);

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
				$addData['product_search_weight_int'] = $attributeVal;
			}
		}
		
		if (empty($attributeVal) || $attributeVal == $helper->__('No') || $attributeVal == $helper->__('None')) {
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
				
				
				if ($attributeVal != $helper->__('No') && $attributeCode != 'status' && $attributeCode != 'sku' && $attributeCode != 'price'){
					if (strlen($attributeVal) > 255) {
						$this->pushTextSearchToObject ($item, $attributeVal, 'textSearchText');
					}else{
						$this->pushTextSearchToObject ($item, $attributeVal);
					}
				}
			}

			if ($attributeObj->getIsFilterable() || $attributeObj->getIsFilterableInSearch()) {
				if ($backendType != 'text' && !in_array($attributeCode, array("price", "sku")))
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
	 * 
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj
	 * @param Varien_Object $item
	 */
	protected function _getAttributeValue(Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj, 
			Varien_Object $item) {
		
		// Frontend getter by vitrual product
		$tmpProduct = $this->getTmpProduct();
		$tmpProduct->setId($item->getId());
		$tmpProduct->setData(
				$attributeObj->getAttributeCode(), 
				$item->getOrigData($attributeObj->getAttributeCode())
		);
		
		$attributeVal = $attributeObj->getFrontEnd()->getValue($tmpProduct);

		if(is_array($attributeVal)){
			$attributeVal = implode(' ', $attributeVal);
		}
		
		return $attributeVal;
		
	}
	
	/**
	 * 
	 * @param Varien_Object $item
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj
	 * @return array()
	 */
	public function _processAttributeDataConfigurable(Varien_Object $parent,
			Varien_Object $child,
			Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj) 
				{
	
		$attributeCode = $attributeObj->getAttributeCode();
		
		// nullable data - return 
		if($child->getOrigData($attributeCode)==null){
			return $this;
		}
		
		$attributeVal = $this->_getAttributeValue($attributeObj, $child);
	
		$backendType = $attributeObj->getBackendType();
		$frontEndInput = $attributeObj->getFrontendInput();
		$attributeKey = $attributeCode.'_'.$backendType;
		$attributeKeyFacets = $attributeCode.'_facet';
		
		if ($attributeVal == 'No') {
			return $this;
		}
		
		if($frontEndInput == 'multiselect') {
			$attributeValFacetsArray = @explode(',', $attributeVal);
			$attributeValFacets = array();
			foreach ($attributeValFacetsArray as $val) {
				$attributeValFacets[] = trim($val);
			}
		}else {
			$attributeValFacets[] = trim($attributeVal);
		}

		if ($backendType == 'datetime') {
			$attributeVal = date("Y-m-d\TG:i:s\Z", $attributeVal);
		}

		if ($attributeVal != 'None' && $attributeCode != 'status' && $attributeCode != 'sku'){
			$this->pushTextSearchToObject($parent, $attributeVal);
		}


		if ($attributeObj->getIsFilterableInSearch() && $attributeValFacets != 'No' && 
				$attributeKey != 'price_decimal' && $attributeKey != 'special_price_decimal'){
			$this->pushToObject($parent, $attributeKeyFacets, array_unique($attributeValFacets));
		}
		return $this;
	}
	
	/**
	 * 
	 * @param Varien_Object $parent
	 * @param Varien_Object $child
	 * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $attributes
	 * @return \Zolago_Solrsearch_Model_Data
	 */
	public function extendConfigurable(Varien_Object $parent, Varien_Object $child, 
			Mage_Catalog_Model_Resource_Product_Attribute_Collection $attributes) {
		
		
		foreach($attributes as $attribute){
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			if(!$attribute->getIsSearchable()){
				continue;;
			}
			$this->_processAttributeDataConfigurable($parent, $child, $attribute);
			
		}
		
		return $this;
	}
	
	/**
	 * 
	 * @param Varien_Object $item
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return Zolago_Solrsearch_Model_Data
	 */
	public function afterLoadAttribute(
			Varien_Object $item, 
			Mage_Catalog_Model_Resource_Eav_Attribute $attributeObj) {
		
		
		

		
		return $this->_processAttributeData($item, $attributeObj);
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
	public function pushToObject(Varien_Object $item, $field, $value) {
		$texts = $item->getData($field);
		if(!is_array($texts)){
			$texts = array();
		}
		if(is_array($value)){
			$texts = array_merge($texts, $value);
		}else{
			if(!in_array($value, $texts)){
				$texts[] = $value;
			}
		}
		$item->setData($field, $texts);
	}
	
	/**
	 * @param Varien_Object $item
	 * @param mixed $string
	 * @param string $field
	 */
	public function pushTextSearchToObject(Varien_Object $item, $string, $field = "textSearch") {
		$this->pushToObject($item, $field, $string);
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