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
	 * 
	 * @param int $storeId
	 * @param array $allIds
	 * @param Mage_Catalog_Model_Resource_Product_Attribute_Collection $attibutes
	 * @param Zolago_Solrsearch_Model_Improve_Collection $initedColelction
	 * @return \Varien_Data_Collection
	 */
	protected function _prepareFinalCollection($storeId, array $allIds = array(), 
			Mage_Catalog_Model_Resource_Product_Attribute_Collection $attibutes,
			Zolago_Solrsearch_Model_Improve_Collection $initedColelction = null) {
		
		/**
		 * Switch store ang lang settings to simulate
		 */
		$oldStore = Mage::app ()->getStore ();
	    $currentLocaleCode = Mage::app()->getLocale()->getLocaleCode();
	    $storeLocaleCode = Mage::getStoreConfig('general/locale/code', $storeId);
		
	    if ($storeLocaleCode) {
	        Mage::getSingleton('core/translate')->
				setLocale($storeLocaleCode)->
				init('frontend', true);
	    }
		
		
		$finalCollection = $initedColelction ? $initedColelction : 
				Mage::getModel("zolagosolrsearch/improve_collection");
		
		/* @var $finalCollection Zolago_Solrsearch_Model_Improve_Collection */
		
		////////////////////////////////////////////////////////////////////////
		// Load base entity and joins
		////////////////////////////////////////////////////////////////////////
		$time = $this->getMicrotime();
		$rows = Mage::getResourceSingleton('zolagosolrsearch/improve')->
			getFlatProducts($storeId, $allIds, array("stock"=>true, "price"=>true));

		
		foreach($rows as $row){
			$row['store_id'] = $storeId;
			$item = new Varien_Object;
			$item->setData($row);
			$item->setOrigData();
			$item->unsetData();
			$item->setId($row['entity_id']);
			$finalCollection->addItem($item);
		}
		Mage::log("Base collection " . $this->_formatTime($this->getMicrotime()-$time));
		

		////////////////////////////////////////////////////////////////////////
		// Load attributes data & process values
		////////////////////////////////////////////////////////////////////////
		$time = $this->getMicrotime();
		
		Mage::getResourceSingleton('zolagosolrsearch/improve')->
				loadAttributesData($finalCollection, $attibutes, $allIds, $storeId);
		
		Mage::log("Attributes load " . $this->_formatTime($this->getMicrotime()-$time));
		
		
		////////////////////////////////////////////////////////////////////////
		// Add category data
		////////////////////////////////////////////////////////////////////////
		$time = $this->getMicrotime();
		Mage::getResourceSingleton('zolagosolrsearch/improve')->
				loadCategoryData($finalCollection, $storeId);
		
		Mage::log("Categories load " . $this->_formatTime($this->getMicrotime()-$time));
		
		
		////////////////////////////////////////////////////////////////////////
		// Extend configurable product with child data
		////////////////////////////////////////////////////////////////////////
		$time = $this->getMicrotime();
		
		foreach($finalCollection->getParentIds() as $id=>$childs){
			if(($item = $finalCollection->getItemById($id)) && $finalCollection->isParentItem($item)){
				foreach($childs as $childId){
					if($childItem = $finalCollection->getItemById($childId)){
						Mage::getSingleton("zolagosolrsearch/data")->
							extendConfigurable($item, $childItem, $attibutes);
					}
				}
			}
		}
		
		Mage::log("Extending configurable with child data " . $this->_formatTime($this->getMicrotime()-$time));
		
		
		////////////////////////////////////////////////////////////////////////
		// Post process loaded data
		////////////////////////////////////////////////////////////////////////
		$time = $this->getMicrotime();
		$regularIds =  $finalCollection->getFlag("regular_ids") ?
				$finalCollection->getFlag("regular_ids") : $allIds;
		
		foreach($finalCollection->getRegularIds() as $id){
			if($item = $finalCollection->getItemById($id)){
				Mage::getSingleton("zolagosolrsearch/data")->processFinalItemData($item);
			}
		}
		Mage::log("Processing final values for regular " . $this->_formatTime($this->getMicrotime()-$time));
		
		
		/**
		 * Restore old store & lang settings
		 */
		Mage::app ()->setCurrentStore ($oldStore);
		Mage::getSingleton('core/translate')->
			setLocale($currentLocaleCode)->
			init('frontend', true);

		return $finalCollection;
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
	 * Returns used by Solr regular attributes
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	public function getSolrUsedAttributes() {
		if(!$this->_solrAttributesCollection){
			
			$codes = array("name", "status", "visibility", "sku");
			
			//display brand suggestion attribute code
			$brandAttributeCode = $this->getBrandAttributeCode();
			
			if ($this->useInSugestions() > 0 && !empty($brandAttributeCode)) {
				$codes[] = $brandAttributeCode;
			}

			//Product search weight attribute code
			if ($this->getWeightAttributeCode()) {
				$codes[] = $this->getWeightAttributeCode();
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
			/*foreach($collection as $attr){
				Mage::log($attr->getAttributeCode());
			}*/
			//Mage::log($collection->getSelect()."");
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
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		
		$mainTime = $this->getMicrotime();
		$ignoreFields = array('sku', 'price', 'status');
		$storeId = $collection->getStoreId();
		Mage::log("Start");
		
	    $fetchedProducts = 0;
		$index = 1;
		$collecitonIds = array_keys($collection->getItems());
		
		//included sub products for search
		$included_subproduct = (int)Mage::helper('solrsearch')->getSetting('included_subproduct');
		
		
		
		////////////////////////////////////////////////////////////////////////
		// Start collectiong relative products
		////////////////////////////////////////////////////////////////////////
		$time = $this->getMicrotime();
		
		// Collect grouped child ids
		if($included_subproduct){
			$this->_collectGroupedChildIds($collection);
		}
		
		// Collect configuration children
		$this->_collectConfigurableChildIds($collection);
		
		$allIds = array_unique(array_merge(
				$collecitonIds,
				$this->_configurableChildIdsFlat, 
				$this->_groupedChildIdsFlat
		));
		Mage::log("Collecting childs " . $this->_formatTime($this->getMicrotime()-$time));
		
		// Final collection is a set with all types of products
		$finalCollection = Mage::getModel("zolagosolrsearch/improve_collection");
		/* @var $finalCollection Zolago_Solrsearch_Model_Improve_Collection */
		
		// Do not index category for configurable childs
		$finalCollection->setCategoryIds(array_unique(array_merge(
				$collecitonIds,
				$this->_groupedChildIdsFlat
		)));
		$finalCollection->setParentIds($this->_configurableChildIds);
		$finalCollection->setRegularIds($collecitonIds);
		
		$this->_prepareFinalCollection($storeId, $allIds, 
				$this->getSolrUsedAttributes(), $finalCollection);
				
		
		Mage::log("Stop " . $finalCollection->count() . " = " . count($allIds));
		
		// DEV: log 10 item
		$i = 0;
		foreach($collecitonIds as $id){
			/* @var $item Varien_Object */
			$item = $finalCollection->getItemById($id);
			if($item && $item->getOrigData('type_id')=="configurable"){
				Mage::log($item->getOrigData());
				Mage::log($item->getData());
				if($i++>9) break;
			}
		}
		
		Mage::log("Time processed:" . $this->_formatTime($this->getMicrotime()-$mainTime));
		Mage::log("Inout collection prods: " . count($collecitonIds));
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

}
?>