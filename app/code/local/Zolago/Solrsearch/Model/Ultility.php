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
     * @return Mage_Catalog_Model_Resource_Product_Collection|Object
     */
	function getProductCollectionByStoreId($store_id)
	{
		$oldStore = Mage::app ()->getStore ();

		Mage::app ()->setCurrentStore ( $store_id );

		$collection = Mage::getResourceModel ( 'solrsearch/product_collection' );

		//$collection->addAttributeToSelect ( '*' )
		$collection
			->addMinimalPrice ()
			->addFinalPrice ()
			->addTaxPercents ()
			->addUrlRewrite ();

		Mage::getSingleton ( 'catalog/product_status' )->addVisibleFilterToCollection ( $collection );
		Mage::getSingleton ( 'catalog/product_visibility' )->addVisibleInSearchFilterToCollection ( $collection );

//		Mage::helper ( 'solrsearch' )->applyInstockCheck ( $collection );

		Mage::app ()->setCurrentStore ( $oldStore );

		return $collection;
	}

    /**
     * Collect grouped data
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return $this
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
		$resourceModel = Mage::getResourceModel("zolagosolrsearch/improve");
		/* @var $resourceModel Zolago_Solrsearch_Model_Resource_Improve */
		$dataModel = Mage::getSingleton("zolagosolrsearch/data");
		/* @var $dataModel Zolago_Solrsearch_Model_Data */
		
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
		// $time = $this->getMicrotime();
		
		$rows = $resourceModel->getFlatProducts($storeId, $allIds, array(
			Zolago_Solrsearch_Model_Resource_Improve::JOIN_PRICE => true,
			Zolago_Solrsearch_Model_Resource_Improve::JOIN_STOCK => true,
			Zolago_Solrsearch_Model_Resource_Improve::JOIN_URL => true,
		));
		
		foreach($rows as $row){
			$row['store_id'] = $storeId;
			$item = new Varien_Object;
			$item->setData($row);
			$item->setOrigData();
			$item->unsetData();
			$item->setId($row['entity_id']);
			$finalCollection->addItem($item);
		}
		//Mage::log("Base collection " . $this->_formatTime($this->getMicrotime()-$time));
		

		
		
		////////////////////////////////////////////////////////////////////////
		// Load attributes data from source EAV
		////////////////////////////////////////////////////////////////////////
		// $time = $this->getMicrotime();
		
		$resourceModel->loadAttributesData($finalCollection, $attibutes, $finalCollection->getAllIds(), $storeId);
		
		//Mage::log("Attributes load from EAV " . $this->_formatTime($this->getMicrotime()-$time));
		
		////////////////////////////////////////////////////////////////////////
		// Add tax percents
		////////////////////////////////////////////////////////////////////////
		// $time = $this->getMicrotime();
		
		$dataModel->addTaxPercents($finalCollection, $storeId);
		
		//Mage::log("Add tax percents " . $this->_formatTime($this->getMicrotime()-$time));
		
		
		////////////////////////////////////////////////////////////////////////
		// Add category data
		////////////////////////////////////////////////////////////////////////
		// $time = $this->getMicrotime();
		$resourceModel->loadCategoryData($finalCollection, $storeId);
		
		//Mage::log("Categories load " . $this->_formatTime($this->getMicrotime()-$time));
		
		
		
		////////////////////////////////////////////////////////////////////////
		// Extend configurable product with child data
		// Load attributes data from index EAV
		////////////////////////////////////////////////////////////////////////
		// $time = $this->getMicrotime();
		
		$resourceModel->loadAttributesDataFromIndex(
				$finalCollection, 
				$attibutes, 
				array_keys($finalCollection->getParentIds()), 
				$storeId
		);
		
		
		//Mage::log("Extending configurable with child data " . $this->_formatTime($this->getMicrotime()-$time));
		
		// add orders data (popularity)
		$popularity = $resourceModel->loadOrdersData($finalCollection);
		
		////////////////////////////////////////////////////////////////////////
		// Post process loaded data
		////////////////////////////////////////////////////////////////////////
		// $time = $this->getMicrotime();
		// $regularIds =  $finalCollection->getFlag("regular_ids") ?	$finalCollection->getFlag("regular_ids") : $allIds;
		


		foreach($finalCollection->getRegularIds() as $id){
			if($item = $finalCollection->getItemById($id)){
				$dataModel->processPriceData($item);
				$dataModel->processFinalItemData($item);
				$popularity = (empty($popularity[$id])? 0:$popularity[$id]);
				$item->addData(array(
					'popularity_int' => $popularity,
					'sort_popularity_int' => $popularity,
				));
			}
		}
		//Mage::log("Processing final values for regular " . $this->_formatTime($this->getMicrotime()-$time));
		
		
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
	 * 
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 * @todo add file cache
	 * 
	 */
	public function getSolrUsedAttributes() {
		if(!$this->_solrAttributesCollection){
			
			$codes = array("name", "tax_class_id", "status", "visibility", "sku", 
				'is_new', 'is_bestseller', 'product_flag', "image",
				"special_price", "special_from_date", "special_to_date", "msrp", "brandshop",
                "campaign_regular_id", "campaign_info_id","campaign_strikeout_price_type","wishlist_count");
			
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
			//foreach($collection as $attr){ 
			//	Mage::log("Used attribs: " . $attr->getAttributeCode());
			//}
			//Mage::log($collection->getSelect()."");
			$this->_solrAttributesCollection=$collection;
		}
		return $this->_solrAttributesCollection;
	}
	
	
	/**
	 * The scenario:
	 * 
	 * 1. Collect grouped products if needed
	 * 2. Collect child products if needed
	 * 3. Init abstract objects collection - setup entries depend product ids (for category, parents, childs etc)
	 * 4. Fill collection with data:
	 *		4.1 Primary entity and some joins (prices, stock, url etc)
	 *		4.2 Loadd attributes data via one query
	 *		4.3 Process attributes data (labels, values)
	 *		4.4 Load category data
	 *		4.5 Process final data (textSearch, skus, prices etc...)
	 * 6. Extend With configurable
	 * 7. Build json string via Varien_Object flatten
	 * 8. Process request
	 * 
	 * Time ~0.05s per congigurable with 10 childs (150 in 7s)
	 * 
	 * After processing produc clear it's instance
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
		//  Price - use original method
		if($onlyprice){
			$collection->addAttributeToSelect ( '*' ); // trivk
			return parent::parseJsonData($collection, $store, $onlyprice);
		}
		
		$mainTime = $this->getMicrotime();
		$ignoreFields = array('sku', 'price', 'status');
		
		$resourceModel = Mage::getResourceModel("zolagosolrsearch/improve");
		/* @var $resourceModel Zolago_Solrsearch_Model_Resource_Improve */
		
		$storeId = $collection->getStoreId();
		//Mage::log("Start");
		
	   
		//$collecitonIds = array_keys($collection->getItems());
		$collecitonIds = $resourceModel->getAllIdsToSolveAsianTricks($collection);
		
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
		
		//$allIds = array_unique(array_merge(
		//		$collecitonIds,
		//		$this->_configurableChildIdsFlat, 
		//		$this->_groupedChildIdsFlat
		//));
		
		$allIds = $collecitonIds;
		//Mage::log("Collecting childs " . $this->_formatTime($this->getMicrotime()-$time));
		
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
				
		
		//Mage::log("Stop " . $finalCollection->count() . " = " . count($allIds));
		
		// DEV: log 10 item
		$fetchedProducts = 0;
		$index = 1;
		$documents = "{";
		
		//Mage::log("Collection " . count($collecitonIds));
		foreach($collecitonIds as $id){
			/* @var $item Varien_Object */
			
			if($item = $finalCollection->getItemById($id)){
				//Mage::log("Product " . $id . " added");
				$documents .= '"add": '.json_encode(array('doc'=>$item->getData())).",";
				//if($index==3){
				//	Mage::log(var_export($item->getData(),1));
				//}
				
			}
			
			$index++;
			$fetchedProducts++;
		}
		
		$jsonData = trim($documents,",").'}';
		
		
		//Mage::log("Time processed:" . $this->_formatTime($this->getMicrotime()-$mainTime));
		//Mage::log("Inout collection prods: " . count($collecitonIds));
		//Mage::log("Count configurable childs: " . count($this->_configurableChildIdsFlat));
		//Mage::log("Count grouped childs: " . count($this->_groupedChildIdsFlat));
		
		
		
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
     * cleanup log table
     */
     public function cleanupLogTable() {
         $len = Mage::helper('solrsearch')->getSetting('logs_length_hours');
         if ($len) {
             $date = date('Y-m-d H:i:s',Mage::getModel('core/date')->timestamp(time()) - $len*3600);
             $connection = $this->getWriteConnection();
             $condition = array($connection->quoteInto('update_at < ?',$date));
             $connection->delete($this->getLogTable(),$condition);
         }
     }

}
