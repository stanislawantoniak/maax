<?php
/**
 * @category SolrBridge
 * @package SolrBridge_Solrsearch
 * @author	Hau Danh
 * @copyright	Copyright (c) 2011-2014 Solr Bridge (http://www.solrbridge.com)
 *
 */
class SolrBridge_Solrsearch_Model_Ultility
{
	public $logfile = null;

	public $logPath = '/solrbridge';

	public $allowCategoryIds = array();

	protected $logQuery = true;

	public $solrServerUrl = 'http://localhost:8080/solr/';

	public $itemsPerCommit = 100;

	public $writeLog = false;

	public $checkInStock = FALSE;

	public $productAttributes = array();

	public $threadEnable = false;

	public function __construct()
	{
		$solr_server_url = Mage::helper('solrsearch')->getSetting('solr_server_url');
		$this->solrServerUrl = $solr_server_url;

		$itemsPerCommitConfig = Mage::helper('solrsearch')->getSetting('items_per_commit');
		if( intval($itemsPerCommitConfig) > 0 )
		{
			$this->itemsPerCommit = $itemsPerCommitConfig;
		}

		$checkInstockConfig =  Mage::helper('solrsearch')->getSetting('check_instock');
		if( intval($checkInstockConfig) > 0 )
		{
			$this->checkInStock = $checkInstockConfig;
		}

		$writeLog =  Mage::helper('solrsearch')->getSetting('write_log');
		if( intval($writeLog) > 0 )
		{
			$this->writeLog = true;
		}
	}
    /**
     * Return the thread manager
     * @return SolrBridge_Solrsearch_Model_Resource_Thread
     */
	public function getThreadManager() {
	    $shellDir = '/' . trim ( Mage::getBaseDir ( 'base' ), '/' );
	    $config = array (
	            'timeout' => 10 * 60, // seconds
	            'maxProcess' => 5,
	            'scriptPath' => $shellDir . '/shell/solrbridge.php'  // path to worker script
	    );
	    return Mage::getResourceModel ( 'solrsearch/thread' )->init ( $config );
	}
    /**
     * Check to see if store view has solr core assigned
     */
	public function checkStoresSolrIndexAssign()
	{
	    $availableCores = $this->getAvailableCores();

	    $messages = array();

	    foreach ($availableCores as $solrcore => $infoArray)
	    {
	        $storeIds = $this->getMappedStoreIdsFromSolrCore($solrcore);

	        if ( !empty($storeIds) )
	        {
	            foreach ($storeIds as $storeid)
	            {
	                $storeObject = Mage::getModel('core/store')->load($storeid);
	                $solrCore = Mage::helper('solrsearch')->getSetting('solr_index', $storeid);

	                if (empty($solrCore) || !Mage::getResourceModel('solrsearch/solr')->pingSolrCore($solrCore)) {
	                    $warningMessage = 'It seems the store ['.$storeObject->getName().'] has no Solr Index assigned yet, please go to SolrBridge > General settings and select the configuration scrope on the top left and set the option Solr index';
	                    $messages[] = $warningMessage;
	                }
	            }
	        }
	    }

	    return $messages;
	}

    /**
     * Load the product attributes collection
     * @return multitype:
     */
	public function getProductAttributes()
	{
		if (empty($this->productAttributes))
		{
			$productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');

			foreach ($productAttrs as $attribute){
				$this->productAttributes[$attribute->getAttributeCode()] = $attribute;
			}
		}
		return $this->productAttributes;
	}

	public function getWriteConnection()
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		return $writeConnection;
	}

	public function getReadConnection()
	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		return $readConnection;
	}

	public function getLogTable()
	{
		$resource = Mage::getSingleton('core/resource');
		$logtable = $resource->getTableName('solrsearch/logs');
		return $logtable;
	}

	/**
	 * Write log message to file
	 * @param string $message
	 * @param number $logType
	 */
	public function writeLog($message = '', $store_id = 0, $solrcore="", $percent=0, $db = false) {
		if ($db) {
			$logTable = $this->getLogTable();
			/*
			$logSqlQuery = "REPLACE INTO {$logTable} (`logs_id`, `store_id`, `solrcore`, `percent`, `message`) VALUES
			(NULL, '{$store_id}', '{$solrcore}', '{$percent}', '{$message}');";
			*/
			$data = array(
				'store_id' => $store_id,
				'solrcore' => $solrcore,
				'percent' => $percent,
				'message' => $message
			);

			try{
				$this->getWriteConnection()->insertOnDuplicate($logTable, $data);
			}catch (Exception $e){
				echo $e->getMessage();
			}
		}

		return $message."\n";
	}

	public function getThreadPath()
	{
		$baseDir = '/'.trim(Mage::getBaseDir('var'), '/');
		$threadPath = $baseDir.$this->logPath.'/threads';
		if (!file_exists($threadPath)) {
			mkdir($threadPath);
		}
		return $threadPath;
	}

	public function saveFileJsonData($jsonData, $threadId)
	{
		$threadPath = $this->getThreadPath();
		$jsonDataFile = $threadPath.'/'.$threadId;
		$fileHandler = fopen($jsonDataFile, 'w');
		fwrite($fileHandler, $jsonData);
		fclose($fileHandler);
	}

	/**
	 * Log successed indexed product id into table
	 * @param array $infoArray
	 * @param string $solrcore
	 */
	public function logProductId($infoArray, $solrcore){
		//Log index fields

		$writeConnection = $this->getWriteConnection();
		$logtable = $this->getLogTable();

		$indexTableName =  Mage::getResourceModel('solrsearch/solr')->getIndexTableName($logtable, $solrcore, $writeConnection);

		if (Mage::getResourceModel('solrsearch/solr')->isIndexTableNameExist($indexTableName)) {
			$indexSql = "";
			if ( is_array($infoArray) ) {
				$indexSql = "REPLACE INTO {$indexTableName} (`store_id`, `value`) VALUES ";
				foreach ($infoArray as $logInfo){
					if ( isset($logInfo['productid']) && is_numeric($logInfo['productid']) && isset($logInfo['storeid']) && is_numeric($logInfo['storeid'])) {
						$storeid = $logInfo['storeid'];
						$productid = $logInfo['productid'];
						$indexSql .= "({$storeid}, {$productid}),";
					}
				}
				$indexSql = trim($indexSql, ',');
				$indexSql .= ";";
			}

			if(!empty($indexSql)){
				$writeConnection->query($indexSql);
			}
		}else{
			return false;
		}
	}
	/**
	 * Log search terms for statistic
	 * @param string $searchText
	 * @param int $numberResults
	 * @param int $storeId
	 */
	public function logSearchTerm($searchText, $numberResults, $storeId)
	{
		if (empty($searchText) || empty($storeId) || $searchText == '**' || $searchText == '*' || $searchText == '*:*') {
			return false;
		}

		if ($this->threadEnable)
		{
		    $this->getThreadManager()
		    ->addThread(array('savesearchterm' => $searchText, 'resultnum' => $numberResults, 'storeid' => $storeId))
		    ->run();
		    return true;
		}else{
		    $this->saveSearchTerm($searchText, $numberResults, $storeId);
		    return true;
		}
	}
	/**
	 * Save search term
	 * @param string $searchText
	 * @param number $numberResults
	 * @param number $storeId
	 */
	protected function saveSearchTerm($searchText, $numberResults, $storeId)
	{
	    $query = Mage::getModel('catalogsearch/query')->loadByQuery($searchText);
	    //Search term not exist
	    if (!$query->getId()) {
	        $query->setQueryText($searchText);
	    }
	    $query->setStoreId($storeId);

	    if ($query->getQueryText() != '')
	    {
	        if ($query->getId()) {
	            $query->setPopularity($query->getPopularity()+1);
	        }
	        else {
	            $query->setPopularity(1);
	        }
	        $query->setIsActive(1)->setIsProcessed(1)->setDisplayInTerms(1);
	        $query->setNumResults($numberResults);
	        $query->save();
	    }
	}

	public function removeLogProductId($id, $solrcore) {

		$writeConnection = $this->getWriteConnection();
		$logtable = $this->getLogTable();

		$indexTableName =  Mage::getResourceModel('solrsearch/solr')->getIndexTableName($logtable, $solrcore, $writeConnection);

		if (Mage::getResourceModel('solrsearch/solr')->isIndexTableNameExist($indexTableName)) {
			if (is_array($id) && !empty($id)) {
				$productIdsString = implode(',', $id);
				$writeConnection->query("DELETE FROM {$indexTableName} WHERE `value` IN (".$productIdsString.");");
			}else if( is_numeric($id) ){
				$writeConnection->query("DELETE FROM {$indexTableName} WHERE `value`=".$id.";");
			}
		}
	}

	public function getMinimalProductCollection()
	{
		$collection = Mage::getResourceModel('catalog/product_collection');

		return $collection;
	}

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
	 * Update collection
	 * @param int $store_id
	 * @param int $page
	 * @param int $itemsPerPage
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	public function getProductCollectionForUpdate($collection, $solrcore)
	{
		$collection->clear();

		$logTable = $this->getLogTable();
		$indexTableName = Mage::getResourceModel('solrsearch/solr')->getIndexTableName($logTable, $solrcore);

		$collection->getSelect()
				   ->joinLeft(
				   		array('wbmsslogs' => $indexTableName),
				   		'wbmsslogs.value = e.entity_id AND wbmsslogs.store_id = cat_index.store_id',
						array('wbmsslogs_store_id' => 'wbmsslogs.store_id', 'wbmsslogs_entity_id' => 'wbmsslogs.value')
					)
				   ->where('wbmsslogs.value IS NULL AND wbmsslogs.store_id IS NULL');

		if ($this->writeLog) {
			$this->writeLog($collection->getSelect());
		}

		return $collection;
	}

	/**
	 * Get product collection for only one product
	 * @param unknown $product
	 * @param int $store_id
	 */
	public function getProductCollectionByProduct($product, $store_id){

		$collection = $this->getProductCollectionByStoreId($store_id);

		$collection->addAttributeToFilter('entity_id', array('in' => array( $product->getId() )));

		return $collection;
	}

	/**
	 * Get product collection metadata
	 * @param int $store_id
	 * @return array
	 */
	function getProductCollectionMetaData($solrcore)
	{
		$storeIds = $this->getMappedStoreIdsFromSolrCore($solrcore);
		$metaDataArray = array();
		if (is_array($storeIds)) {
			$itemsPerCommit = $this->itemsPerCommit;
			$totalProductCount = 0;

			$loadedStores = array();
			$loadedStoresName = array();

			foreach ($storeIds as $storeId) {
				$storeProductCollection = $this->getProductCollectionByStoreId($storeId);
				$storeProductCount = $storeProductCollection->getSize();
				$totalProductCount += $storeProductCount;
				$metaDataArray['stores'][$storeId]['productCount'] = $storeProductCount;

				$totalPages = ceil($storeProductCount/$itemsPerCommit);
				$metaDataArray['stores'][$storeId]['totalPages'] = $totalPages;
				$metaDataArray['stores'][$storeId]['collection'] = $storeProductCollection;

				$store = Mage::getModel('core/store')->load($storeId);
				$loadedStores[$storeId] = $store;
				$loadedStoresName[$storeId] = $store->getName();
			}
			$metaDataArray['totalProductCount'] = $totalProductCount;
			$metaDataArray['loadedStores'] = $loadedStores;
			$metaDataArray['loadedStoresName'] = $loadedStoresName;
		}
		return $metaDataArray;
	}

	/**
	 * Get product collection metadata
	 * @param int $store_id
	 * @return array
	 */
	function getProductCollectionMetaDataForUpdate($collection, $solrcore)
	{
		$itemsPerCommit = $this->itemsPerCommit;
		$collection = $this->getProductCollectionForUpdate($collection, $solrcore);

		$sql = $collection->getSelectCountSql();

		$productCount = $collection->getConnection()->fetchOne($sql);

		$metaDataArray = array();
		//$productCount = $collection->getSize();
		$metaDataArray['productCount'] = $productCount;
		$totalPages = ceil($productCount/$itemsPerCommit);
		$metaDataArray['totalPages'] = $totalPages;
		$metaDataArray['collection'] = $collection;

		if ($this->writeLog) {
			$this->writeLog(print_r($metaDataArray, true));
		}

		return $metaDataArray;
	}

	/**
	 * Parse product collection into json
	 * @param Mage_Catalog_Model_Product_Collection $collection
	 * @param Mage_Core_Model_Store $store
	 * @return array
	 */
	public function parseJsonData($collection, $store, $onlyprice = false)
	{
		$ignoreFields = array('sku', 'price', 'status');

	    $fetchedProducts = 0;

		//included sub products for search
		$included_subproduct = (int)Mage::helper('solrsearch')->getSetting('included_subproduct');

		//display brand suggestion
		$display_brand_suggestion = Mage::helper('solrsearch')->getSetting('display_brand_suggestion');
		//display brand suggestion attribute code
		$brand_attribute_code = Mage::helper('solrsearch')->getSetting('brand_attribute_code');
		$brand_attribute_code = trim($brand_attribute_code);
		$includedBrandAttributeCodes = array();
		if ($display_brand_suggestion > 0 && !empty($brand_attribute_code)) {
			$includedBrandAttributeCodes[] = $brand_attribute_code;
		}

		//Product search weight attribute code
		$includedSearchWeightAttributeCodes = array();
		$search_weight_attribute_code = Mage::helper('solrsearch')->getSetting('search_weight_attribute_code');
		$search_weight_attribute_code = trim($search_weight_attribute_code);
		if (!empty($search_weight_attribute_code)) {
			$includedSearchWeightAttributeCodes[] = $search_weight_attribute_code;
		}

		$index = 1;
		$documents = "{";

		//loop products
		//$collection->load();
		foreach ($collection as $_product) {
			$textSearch = array();
			$textSearchText = array();
			$docData = array();
			//$_product = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($product->getId());


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
				$documents .= '"add": '.json_encode(array('doc'=>$docData)).",";
			}

			$index++;
			$fetchedProducts++;
		}

		$jsonData = trim($documents,",").'}';

		return array('jsondata'=> $jsonData, 'fetchedProducts' => (int) $fetchedProducts);
	}

	public function updatePriceData(&$data, $product, $store)
	{
		$customerGroupCollection = Mage::getResourceModel('customer/group_collection')
		->addTaxClass();

		$storeObject = $store;
		$currenciesCode = $storeObject->getAvailableCurrencyCodes(true);
		foreach ($currenciesCode as $currencycode)
		{
			$currency  = Mage::getModel('directory/currency')->load($currencycode);
			$storeObject->setData('current_currency', $currency);

			foreach ($customerGroupCollection as $group){

				$price = 0;//including tax
				$specialPrice = 0;//including tax
				$sortSpecialPrice = 0;

				$returnData = Mage::getModel('solrsearch/price')->getProductPrice($product, $storeObject, $group->getId());

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
	}

	/**
	 * Generate product thumbnails
	 * @param unknown_type $product
	 */
	public function generateThumb($product, $debug = false){

		$thumsize = Mage::helper('solrsearch')->getSetting('autocomplete_thumb_size');
		$width = 32;
		$height = 32;
		if (!empty($thumsize)) {
			$thumbSizeArray = explode('x', $thumsize);
			if (isset($thumbSizeArray[0]) && is_numeric($thumbSizeArray[0])) {
				if (isset($thumbSizeArray[1]) && is_numeric($thumbSizeArray[1])) {
					$width = trim($thumbSizeArray[0]);
					$height = trim($thumbSizeArray[1]);
				}
			}
		}

		$productId = $product->getId();

		$image = trim($product->getSmallImage());
		if (empty($image) || $image == 'no_selection') {
			$image = trim($product->getImage());
		}

		if (empty($image) || $image == 'no_selection'){
			$productImagePath = Mage::helper('solrsearch/image')->getImagePlaceHolder();
		}else{
			$productImagePath = Mage::getBaseDir("media").DS.'catalog'.DS.'product'.DS.$image;
		}

		if (!file_exists($productImagePath)){
			if ($product->getImage() != 'no_selection' && $product->getImage()){
				$productImagePath = Mage::helper('solrsearch/image')->init($product, 'image')->resize($width, $height)->getImagePath();

				if (!file_exists($productImagePath)){
				    $productImagePath = Mage::helper('solrsearch/image')->getImagePlaceHolder();
				}
			}
		}

		$productImageThumbPath = Mage::getBaseDir('media').DS."catalog".DS."product".DS."sb_thumb".DS.$productId.'.jpg';
		if (file_exists($productImageThumbPath)) {
			unlink($productImageThumbPath);
		}
		$imageResizedUrl = Mage::getBaseUrl("media").DS."catalog".DS."product".DS."sb_thumb".DS.$productId.'.jpg';
		try{
			$imageObj = new Varien_Image($productImagePath);
			$imageObj->constrainOnly(FALSE);
			$imageObj->keepAspectRatio(TRUE);
			$imageObj->keepFrame(FALSE);
			$imageObj->backgroundColor(array(255,255,255));
			//$imageObj->keepTransparency(TRUE);
			$imageObj->resize($width, $height);
			$imageObj->save($productImageThumbPath);
		}catch (Exception $e){
			echo 'Exception at product['.$productId.']['.$productImageThumbPath.']: ',  $e->getMessage(), "\n";
		}
		if (file_exists($productImageThumbPath)) {
			return true;
		}
		return false;
	}

	/**
	 * Get product attribute collection
	 */
	public function getProductAttributeCollection()
	{
		$cachedKey = 'solrbridge_solrsearch_product_attribute_'.Mage::app()->getStore()->getId().'_'.Mage::app()->getStore()->getWebsiteId();

		if (false !== ($attributesInfo = Mage::app()->getCache()->load($cachedKey)))
		{
			return unserialize($attributesInfo);
		}
		else
		{
			$entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
			$catalogProductEntityTypeId = $entityType->getEntityTypeId();
			$attributesInfo = Mage::getResourceModel('eav/entity_attribute_collection')
			->setEntityTypeFilter($catalogProductEntityTypeId)
			->addSetInfo()
			->setOrder('position', 'ASC')
			->getData();

			Mage::app()->getCache()->save(serialize($attributesInfo), $cachedKey, array('solrbridge_solrsearch'));
		}

		return $attributesInfo;
	}

	/**
	 * Retrive available stores
	 * @return array
	 */
	public function getAvailableCores() {
		return (array) Mage::getStoreConfig('solrbridgeindices', 0);
	}
	/**
	 * Get store ids which mapped to Solr core
	 * @param string $solrcore
	 * @return array
	 */
	public function getMappedStoreIdsFromSolrCore($solrcore)
	{
		$storeIdArray = array();

		if (isset($solrcore) && !empty($solrcore)) {
			$availableCores = $this->getAvailableCores();
			if ( isset($availableCores[$solrcore]) && isset($availableCores[$solrcore]['stores']) )
			{
				if ( strlen( trim($availableCores[$solrcore]['stores'], ',') ) > 0 )
				{
					$storeIdArray = trim($availableCores[$solrcore]['stores'], ',');
					$storeIdArray = array_map('intval', explode(',', $storeIdArray));
				}
			}
		}
		return $storeIdArray;
	}

	public function getRootCategoryIds($storeIds = array())
	{
		$rootCatIds = array();
		if (isset($storeIds) && !empty($storeIds))
		{
			foreach ($storeIds as $storeId){
				$catId = Mage::getModel('core/store')->load($storeId)->getRootCategoryId();
				if (!empty($catId)) {
					$rootCatIds[] = $catId;
				}
			}
		}
		return array_map('intval', array_unique($rootCatIds));
	}

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