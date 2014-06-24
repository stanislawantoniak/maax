<?php
/**
 * Improve performance
 */
class Zolago_Solrsearch_Model_Ultility extends SolrBridge_Solrsearch_Model_Ultility
{
	/**
	 * @var array
	 */
	protected $_groupedChildIds;
	/**
	 * @var array
	 */
	protected $_groupedChildIdsFlat;
	/**
	 * @var array
	 */
	protected $_configurableChildIds;
	/**
	 * @var array
	 */
	protected $_configurableChildIdsFlat;
	
	/**
	 * @var Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	protected $_solrAttributesCollection;

	static protected $_logTime = 0;
	static protected $_logProd = 0;
	
	/**
	 * Get product collection by store id
	 * @param int $store_id
	 * @param int $page
	 * @param int $itemsPerPage
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	function getProductCollectionByStoreId($store_id)
	{
		$oldStore = Mage::app ()->getStore ();

		Mage::app ()->setCurrentStore ( $store_id );

		$collection = Mage::getResourceModel ( 'solrsearch/product_collection' );

		$collection->addAttributeToSelect ( '*' )
		->addMinimalPrice ()
		->addFinalPrice ()
		->addTaxPercents ()
		->addUrlRewrite ();

		Mage::getSingleton ( 'catalog/product_status' )->addVisibleFilterToCollection ( $collection );
		Mage::getSingleton ( 'catalog/product_visibility' )->addVisibleInSearchFilterToCollection ( $collection );

		Mage::helper ( 'solrsearch' )->applyInstockCheck ( $collection );

		Mage::app ()->setCurrentStore ( $oldStore );

		return $collection;
	}
	
	/**
	 * Collect grouped data
	 * @param Mage_Catalog_Model_Resource_Product_Collection $collection
	 * @return \Zolago_Solrsearch_Model_Ultility
	 */
	public function _collectGroupedChildIds(Mage_Catalog_Model_Resource_Product_Collection $collection) {
		$groupedIds = array();
		foreach($collection as $product){
			/* @var $product Mage_Catalog_Model_Product */
			if($product->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_GROUPED){
				$groupedIds[] = $product->getId();
			}
		}
		$this->_groupedChildIds = Mage::getResourceSingleton('zolagosolrsearch/improve')->
			getAllChildIds($groupedIds);
		$this->_groupedChildIdsFlat = $this->_flatten($this->_groupedChildIds);
		return $this;
	}

	
	/**
	 * Collect configurable data
	 * @param Mage_Catalog_Model_Resource_Product_Collection $collection
	 * @return \Zolago_Solrsearch_Model_Ultility
	 */
	public function _collectConfigurableChildIds(Mage_Catalog_Model_Resource_Product_Collection $collection) {
		$configurableIds = array();
		foreach($collection as $product){
			/* @var $product Mage_Catalog_Model_Product */
			if($product->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
				$configurableIds[] = $product->getId();
			}
		}
		$this->_configurableChildIds = Mage::getResourceSingleton('zolagosolrsearch/improve')->
			getAllChildIds($configurableIds);

		$this->_configurableChildIdsFlat = $this->_flatten($this->_configurableChildIds);
		return $this;
	}
	
	/**
	 * @param array $array
	 * @return array
	 */
	protected function _flatten(array $array) {
		$return = array();
		array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
		return $return;
	}
	
	/**
	 * @todo Add customer group prices
	 * 
	 * @param int $storeId
	 * @param array $allIds
	 * @return Varien_Data_Collection - faster
	 */
	protected function _prepareFinalCollection($storeId, array $allIds = array(), 
			Mage_Catalog_Model_Resource_Product_Attribute_Collection $attibutes) {
		
		
		Mage::log("Start");
		$finalCollection = new Varien_Data_Collection;
		
		// Basic select
		$rows = Mage::getResourceSingleton('zolagosolrsearch/improve')->
			getFlatProducts($storeId, $allIds, array("stock"=>true, "price"=>true));

		foreach($rows as $row){
			$product = new Varien_Object($row);
			$product->setId($product->getEntityId());
			$finalCollection->addItem($product);
		}
		// Load attributes data
		Mage::getResourceSingleton('zolagosolrsearch/improve')->
				loadAttributesData($finalCollection, $attibutes, $allIds, $storeId);
		
		Mage::log($finalCollection->getFirstItem()->getData());
		Mage::log("Stop " . $finalCollection->count() . " " . count($allIds));

		return $finalCollection;
	}
	
	
	

	
		
	/**
	 * Returns used by Solr regular attributes
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	public function getSolrUsedAttributes() {
		if(!$this->_solrAttributesCollection){
			
			$codes = array("name");
			
			//display brand suggestion
			$displayBrandSuggestion = Mage::helper('solrsearch')->getSetting('display_brand_suggestion');
			//display brand suggestion attribute code
			$brandAttributeCode = trim(Mage::helper('solrsearch')->getSetting('brand_attribute_code'));
			$includedBrandAttributeCodes = array();
			if ($displayBrandSuggestion > 0 && !empty($brandAttributeCode)) {
				$includedBrandAttributeCodes[] = $brandAttributeCode;
				$codes[] = $brandAttributeCode;
			}

			//Product search weight attribute code
			$includedSearchWeightAttributeCodes = array();
			$searchWeightAttributeCode =	trim(Mage::helper('solrsearch')->getSetting('search_weight_attribute_code'));
			if (!empty($searchWeightAttributeCode)) {
				$includedSearchWeightAttributeCodes[] = $searchWeightAttributeCode;
				$codes[] = $searchWeightAttributeCode;
			}
			
			$collection = Mage::getResourceModel("catalog/product_attribute_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
			$collection->addFieldToFilter(
				array(
					"attribute_code",
					"solr_search_field_weight",
					"solr_search_field_boost",
					"is_visible_in_advanced_search",
					"is_searchable",
					"is_filterable",
				),array(
					array("in"=>$codes),
					array("eq"=>1),
					array("eq"=>1),
					array("eq"=>1),
					array("eq"=>1),
					array("eq"=>1),
				)
			);
			
			$this->_solrAttributesCollection=$collection;
		}
		return $this->_solrAttributesCollection;
	}
	
	
	/**
	 * -2. Enlarge buffer limit
	 * -1. Improve loadded collection attrivures data. alod only needed attributes data.
	 *  0. Collect ids if childs via one query
	 *  1. Process all super products
	 *  2. Procesa all childs if needed
	 *  3. Load product attributes data via collected information
	 *  3. COllect categories data and process all categoriees data 
	 *  4. Process final data
	 *  
	 *  After processing produc clear it's instance
	 * 
	 * 
	 * Parse product collection into json
	 * @param Mage_Catalog_Model_Product_Collection $collection
	 * @param Mage_Core_Model_Store $store
	 * @return array
	 */
	public function parseJsonData($collection, $store, $onlyprice = false)
	{
		
		$time = $this->getMicrotime();
		$ignoreFields = array('sku', 'price', 'status');
		$storeId = $collection->getStoreId();
		
	    $fetchedProducts = 0;
		$index = 1;

		//included sub products for search
		$included_subproduct = (int)Mage::helper('solrsearch')->getSetting('included_subproduct');
		
		// Collect grouped child ids
		if($included_subproduct){
			$this->_collectGroupedChildIds($collection);
		}
		
		// Collect configuration children
		$this->_collectConfigurableChildIds($collection);
		
		$allIds = array_unique(array_merge(
				$collection->getAllIds(), 
				$this->_configurableChildIdsFlat, 
				$this->_groupedChildIdsFlat
		));
		
		// Final collection is a set with all types of products
		$finalCollection = $this->_prepareFinalCollection(
				$storeId, $allIds, $this->getSolrUsedAttributes());
		
		Mage::log("Count regular prods: " . count($collection->getAllIds()));
		Mage::log("Count configurable childs: " . count($this->_configurableChildIdsFlat));
		Mage::log("Count grouped childs: " . count($this->_groupedChildIdsFlat));
		die;
		// Process each product type each way

		

		$documents = "{";
		
		/*
		//loop products
		//$collection->load();
		foreach ($collection as $_product) {
			$_time = $this->getMicrotime();
			$textSearch = array();
			$textSearchText = array();
			$docData = array();
			$_product->setStoreId($store->getId());

			//Price index only
			if( $onlyprice )
			{
				$docData ['products_id'] = $_product->getId ();
				$docData ['unique_id'] = $store->getId () . 'P' . $_product->getId ();
				$docData ['store_id'] = $store->getId ();
				$docData ['website_id'] = $store->getWebsiteId ();

				$stock = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $_product );
				if ($stock->getIsInStock ()) {
					$docData ['instock_int'] = 1;
				} else {
					$docData ['instock_int'] = 0;
				}
				$docData ['product_status'] = $_product->getStatus ();

				$this->updatePriceData ( $docData, $_product, $store );
				$documents .= '"set": ' . json_encode ( array ('doc' => $docData) ) . ",";
			}
			else
			{
				$solrBridgeData = Mage::getModel('solrsearch/data');
				$solrBridgeData->setStore($store);
				$solrBridgeData->setBrandAttributes($includedBrandAttributeCodes);
				$solrBridgeData->setSearchWeightAttributes($includedSearchWeightAttributeCodes);
				$solrBridgeData->setIgnoreFields($ignoreFields);
				$docData = $solrBridgeData->getProductAttributesData($_product);

				//Categories
				$solrBridgeData->prepareCategoriesData($_product, $docData);
				//product tags
				$solrBridgeData->prepareTagsData($_product, $docData);

				$solrBridgeData->prepareFinalProductData($_product, $docData);

				$textSearch = $solrBridgeData->getTextSearch();
				$textSearchText = $solrBridgeData->getTextSearchText();

				
				if (!empty($included_subproduct) && $included_subproduct > 0) {
					if ($_product->getTypeId() == 'grouped') {
						Mage::log("Subproducts");
						$associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
						foreach ($associatedProducts as $subproduct) {
							$_subproduct = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($subproduct->getId());

							$solrBridgeSubData = Mage::getModel('solrsearch/data');
							$solrBridgeSubData->setStore($store);
							$solrBridgeSubData->setBrandAttributes($includedBrandAttributeCodes);
							$solrBridgeSubData->setSearchWeightAttributes($includedSearchWeightAttributeCodes);
							$solrBridgeSubData->setIgnoreFields($ignoreFields);
							$docSubData = $solrBridgeData->getProductAttributesData($_subproduct);

							//Categories
							$solrBridgeSubData->prepareCategoriesData($_subproduct, $docSubData);
							//product tags
							$solrBridgeSubData->prepareTagsData($_subproduct, $docSubData);

							$solrBridgeSubData->prepareFinalProductData($_subproduct, $docSubData);

							$subTextSearch = $solrBridgeSubData->getTextSearch();
							$textSearch = array_merge($textSearch, $subTextSearch);

							$subTextSearchText = $solrBridgeSubData->getTextSearchText();
							$textSearchText = array_merge($textSearchText, $subTextSearchText);
						}
					}
				}
				 

				//Prepare text search data
				$docData['textSearch'] = (array) $textSearch;
				$docData['textSearchText'] = (array) $textSearchText;
				$docData['textSearchStandard'] = (array) $textSearch;

				$this->updateDocDataForConfigurableProducts($docData, $_product, $store);

				$this->updatePriceData($docData, $_product, $store);

				try{
					$this->generateThumb($_product);
				}catch (Exception $e){
					$message = Mage::helper('solrsearch')->__('#%s %s at product %s[%s] in store [%s]', $index, $e->getMessage() , $_product->getId(), $_product->getName(), $store->getName());
					$this->writeLog($message, $store->getId(), 'english', 0, true);
				}
				Mage::log($docData);
				$documents .= '"add": '.json_encode(array('doc'=>$docData)).",";
			}
			//Mage::log("Processing " . $_product->getId() . ": " . $this->_formatTime($this->getMicrotime()-$_time));
			$index++;
			$fetchedProducts++;
		}
		*/
		
		$jsonData = trim($documents,",").'}';
		
		$time = $this->getMicrotime()-$time;
		if($fetchedProducts){
			Mage::log($fetchedProducts. " products " . round($time) . " /  " .  $this->_formatTime($time/$fetchedProducts) . "per product");
		}
		return array('jsondata'=> $jsonData, 'fetchedProducts' => (int) $fetchedProducts);
	}
	
	protected function _formatTime($t) {
		return round($t,2) . "s";
	}
	
	protected function getMicrotime(){ 
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec); 
    } 


	/**
	 * Load all products with its needed attributes by paretn products ids
	 * and process its 
	 * 
	 * @param type $docData
	 * @param type $parentProduct
	 * @param type $store
	 */
	public function updateDocDataForConfigurableProducts(&$docData, $parentProduct, $store){

		if($parentProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
			
			$collection = $parentProduct->getTypeInstance()->getUsedProductCollection($parentProduct);

			foreach ($collection as $product) {
				$textSearch = array();

				$_product = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($product->getId());
				$atributes = $_product->getAttributes();
				foreach ($atributes as $key=>$atributeObj) {
					$backendType = $atributeObj->getBackendType();
					$frontEndInput = $atributeObj->getFrontendInput();
					$attributeCode = $atributeObj->getAttributeCode();
					$attributeData = $atributeObj->getData();

					if (!$atributeObj->getIsSearchable()) continue; // ignore fields which are not searchable

					if ($backendType == 'int') {
						$backendType = 'varchar';
					}

					$attributeKey = $key.'_'.$backendType;

					$attributeKeyFacets = $key.'_facet';

					if (!is_array($atributeObj->getFrontEnd()->getValue($_product))){
						$attributeVal = strip_tags($atributeObj->getFrontEnd()->getValue($_product));
					}else {
						$attributeVal = $atributeObj->getFrontEnd()->getValue($_product);
						$attributeVal = implode(' ', $attributeVal);
					}

					if ($_product->getData($key) == null)
					{
						$attributeVal = null;
					}
					$attributeValFacets = array();
					if (!empty($attributeVal) && $attributeVal != 'No') {
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

						if (!in_array($attributeVal, $textSearch) && $attributeVal != 'None' && $attributeCode != 'status' && $attributeCode != 'sku'){
							$textSearch[] = $attributeVal;
						}

						if (
						(isset($attributeData['is_filterable_in_search']) && !empty($attributeData['is_filterable_in_search']) && $attributeValFacets != 'No' && $attributeKey != 'price_decimal' && $attributeKey != 'special_price_decimal')
						) {
							if (isset($docData[$attributeKeyFacets])) {
								$docData[$attributeKeyFacets] = array_merge($docData[$attributeKeyFacets], $attributeValFacets);
							}else{
								$docData[$attributeKeyFacets] = $attributeValFacets;
							}
							if (is_array($docData[$attributeKeyFacets]) && count($docData[$attributeKeyFacets]) > 0) {
								$docData[$attributeKeyFacets] = array_unique($docData[$attributeKeyFacets]);
							}

						}
					}

				}
				$docData['textSearch'] = array_merge($docData['textSearch'], $textSearch);
			}
		}
	}
	
	

}
?>