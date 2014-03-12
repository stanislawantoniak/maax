<?php
/**
 * @category SolrBridge
 * @package Solrbridge_Search
 * @author	Hau Danh
 * @copyright	Copyright (c) 2013 Solr Bridge (http://www.solrbridge.com)
 *
 */
class SolrBridge_Solrsearch_Model_Resource_Solr extends SolrBridge_Solrsearch_Model_Resource_Base
{
	/**
	 * Solr server url
	 * @var string
	 */
	public $_solrServerUrl = 'http://localhost:8080/solr/';
	/**
	 * Solr core, it is the language name like english, german, etc
	 * @var string
	 */
	public $core = 'english';
	/**
	 * Get Solr server url from Magento setting
	 * @return string
	 */
    public function getSolrServerUrl()
    {
    	return $this->getSetting('solr_server_url');
    }
    /**
     * Ping to see if solr server available or not
     * @param string $core
     * @return boolean
     */
    public function pingSolrCore($solrcore = 'english')
    {
    	$solrServerUrl = $this->getSolrServerUrl();
    	$pingUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/admin/ping?wt=json';
    	$result = $this->doRequest($pingUrl);
    	if (isset($result['status']) && $result['status'] == 'OK') {
    		return true;
    	}
    	return false;
    }
    /**
     * Get solr core statistic to find how many documents exist
     * @param string $solrcore
     * @return array
     */
    public function getSolrLuke($solrcore) {
    	$solrServerUrl =$this->getSolrServerUrl();
    	$queryUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/admin/luke?reportDocCount=true&fl=products_id&wt=json';
    	$result = $this->doRequest($queryUrl);
    	return $result;
    }
	/**
	 * Get all documents
	 * @param string $solrcore
	 * @return array
	 */
    public function getAllDocuments($solrcore)
    {
    	$total = Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);
    	$solrServerUrl =$this->getSolrServerUrl();
    	$queryUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/select/?q=*:*&fl=products_id,store_id&start=0&rows='.$total.'&wt=json';
    	return $this->doRequest($queryUrl);
    }

    /**
     * Request Solr Server by CURL
     * @param string $url
     * @param mixed $postFields
     * @param string $type
     * @return array
     */
    public function doRequest($url, $postFields = null, $type='array'){

    	$sh = curl_init($url);
    	curl_setopt($sh, CURLOPT_HEADER, 0);
    	if(is_array($postFields)) {
    		curl_setopt($sh, CURLOPT_POST, true);
    		curl_setopt($sh, CURLOPT_POSTFIELDS, $postFields);
    	}
    	curl_setopt($sh, CURLOPT_RETURNTRANSFER, 1);

    	curl_setopt( $sh, CURLOPT_FOLLOWLOCATION, true );
    	if ($type == 'json') {
    		curl_setopt( $sh, CURLOPT_HEADER, true );
    	}

    	if (isset($_GET['user_agent']) || isset($_SERVER['HTTP_USER_AGENT'])) {
    		curl_setopt( $sh, CURLOPT_USERAGENT, isset($_GET['user_agent']) ? $_GET['user_agent'] : $_SERVER['HTTP_USER_AGENT'] );
    	}

    	$this->setupSolrAuthenticate($sh);

    	if ($type == 'json') {
    		list( $header, $contents ) = @preg_split( '/([\r\n][\r\n])\\1/', curl_exec($sh), 2 );
    		$output = preg_split( '/[\r\n]+/', $contents );
    	}else{
    		$output = curl_exec($sh);
    		$output = json_decode($output,true);
    	}

    	curl_close($sh);
    	return $output;
    }

    /**
     * Setup Solr authentication user/pass if neccessary
     * @param resource $sh
     */
    public function setupSolrAuthenticate(&$sh)
    {
    	$isAuthentication = 0;
    	$authUser = '';
    	$authPass = '';

    	$isAuthenticationCache = Mage::app()->loadCache('solr_bridge_is_authentication');
    	if ( isset($isAuthenticationCache) && !empty($isAuthenticationCache) ) {
    		$isAuthentication = $isAuthenticationCache;
    		$authUser = Mage::app()->loadCache('solr_bridge_authentication_user');
    		$authPass = Mage::app()->loadCache('solr_bridge_authentication_pass');
    	}else {
    		// Save data to cache
    		$isAuthentication = $this->getSetting('solr_server_url_auth');
    		$authUser = $this->getSetting('solr_server_url_auth_username');
    		$authPass = $this->getSetting('solr_server_url_auth_password');

    		Mage::app()->saveCache($isAuthentication, 'solr_bridge_is_authentication', array('solrbridge_solrsearch'), 60*60*24);
    		Mage::app()->saveCache($authUser, 'solr_bridge_authentication_user', array('solrbridge_solrsearch'), 60*60*24);
    		Mage::app()->saveCache($authPass, 'solr_bridge_authentication_pass', array('solrbridge_solrsearch'), 60*60*24);
    	}

    	if (isset($isAuthentication) && $isAuthentication > 0 ) {
    		curl_setopt($sh, CURLOPT_USERPWD, $authUser.':'.$authPass);
    		curl_setopt($sh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    	}
    }
	/**
	 * Remove solrbridge indices tables when Solr index data empty
	 * @param array $storeids
	 */
    public function truncateIndexTables($solrcore)
    {
    	$resource = Mage::getSingleton('core/resource');
    	$connection = $resource->getConnection('core_write');
    	$logtable = $resource->getTableName('solrsearch/logs');

    	if (!empty($solrcore)) {
    		$indexTableName = $this->getIndexTableName($logtable, $solrcore);
    		if (!empty($indexTableName) && $this->isIndexTableNameExist($indexTableName)) {
    			$truncateSql = "TRUNCATE TABLE {$indexTableName}";
    			$connection->query($truncateSql);
    		}
    	}
    }
	/**
	 * Get solr bridge index table name
	 * @param string $logTableName
	 * @param number $storeId
	 * @return string
	 */
    public function getIndexTableName($logTableName, $solrCore)
    {
    	$indexedTableName = str_replace('_logs', '_index_'.$solrCore, $logTableName);

    	return $indexedTableName;
    }
    public function isIndexTableNameExist($indexTableName)
    {
    	return $this->isTableExists($indexTableName);
    }
	/**
	 * Create index table if not exist
	 * @param string $logTableName
	 * @param number $storeId
	 * @param string $writeConnection
	 * @return string|boolean
	 */
    public function prepareIndexTable($logTableName, $solrCore, $writeConnection = null)
    {
    	if (!empty($logTableName) && !empty($solrCore)){
    		if (!$writeConnection)
    		{
    			$resource = Mage::getSingleton('core/resource');
    			$writeConnection = $resource->getConnection('core_write');
    		}

    		$indexedTableName = $this->getIndexTableName($logTableName, $solrCore);

    		if (!$this->isIndexTableNameExist($indexedTableName))
    		{
    			$table = $writeConnection
    			->newTable($indexedTableName)
    			->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    					'unsigned'  => true,
    					'nullable'  => false,
    					'primary'   => true,
    					'default'   => '0',
    			), 'Store ID')
    			->addColumn('update_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    					'default'   => 'CURRENT_TIMESTAMP',
    			), 'Update Time')
    			->addColumn('value', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    					'unsigned'  => true,
    					'nullable'  => false,
    					'primary'   => true,
    					'default'   => '0',
    			), 'Product ID')
    			->addIndex("IDX_SOLRBRIDGE_SOLRSEARCH_INDEX_".strtoupper($solrCore)."_STORE_ID", array('store_id'))
    			->addIndex("IDX_SOLRBRIDGE_SOLRSEARCH_INDEX_".strtoupper($solrCore)."_VALUE", array('value'));

    			$writeConnection->createTable($table);
    			$writeConnection->modifyColumn($indexedTableName, 'update_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
    		}
    		return $indexedTableName;
    	}
    	return false;
    }

	/**
	 * Generate index tables
	 */
    public function generateIndexTables()
    {
    	//Get available solr cores
    	$availableCores = $this->ultility->getAvailableCores();

    	if ( isset($availableCores) && !empty($availableCores) )
    	{
    		foreach ($availableCores as $solrcore => $info)
    		{
    			if (isset($info['stores']) && strlen(trim($info['stores'], ',')) > 0) {

    				$logTableName = $this->ultility->getLogTable();

    				$this->prepareIndexTable($logTableName, $solrcore);
    			}
    		}
    	}
    }

    /**
     * Synchronize data between Magento database and Solr index
     * @param string $solrCore
     * @return boolean
     */
    public function synchronizeData($solrCore)
    {
    	//Check if index table exists
        $logtable = $this->ultility->getLogTable();
        $indexTableName =  $this->getIndexTableName($logtable, $solrCore, $this->writeConnection);

        if (!$this->isIndexTableNameExist($indexTableName))
        {
            $messge = Mage::helper('solrsearch')->__('The index table %s does not exists, please go to SolrBridge > Indices settings and click the button Save config and try again.', $indexTableName);
            throw new Exception($messge);
        }

    	//Get available solr cores
    	$availableCores = $this->ultility->getAvailableCores();

    	if ( isset($availableCores[$solrCore]) && isset($availableCores[$solrCore]['stores']) )
    	{
    		if ( strlen( trim($availableCores[$solrCore]['stores'], ',') ) > 0 )
    		{
    			$returnData = $this->getAllDocuments($solrCore);

    			if (isset($returnData['response']['numFound']) && intval($returnData['response']['numFound']) > 0)
    			{
    				if (is_array($returnData['response']['docs'])) {
    					$index = 0;
    					$logIds = array();
    					foreach ($returnData['response']['docs'] as $doc) {

    						if (isset($doc['products_id']) && $doc['store_id'] && is_numeric($doc['products_id']) && is_numeric($doc['store_id'])) {

    							$logIds[] = array('productid' => $doc['products_id'], 'storeid' => $doc['store_id']);
    						}
    						if ($index >= 20) {
    							$this->ultility->logProductId($logIds, $solrCore);
    							$logIds = array();
    							$index = 0;
    							continue;
    						}
    						$index++;
    					}

    					if (!empty($logIds)) {
    						$this->ultility->logProductId($logIds, $solrCore);
    					}
    				}
    			}
    		}
    	}else{
    		return false;
    	}
    }
    /**
     * Update single product which related in Solr index
     * @param int $productid
     */
    public function updateSingleProduct($productid, $store_id = 0)
    {
    	$availableCores = $this->ultility->getAvailableCores();

    	foreach ($availableCores as $solrcore => $infoArray)
    	{
    		if ($store_id > 0){
    			$this->updateSingleProductBySolrCore($solrcore, $productid, $store_id);
    		}else{
    			$storeIds = $this->ultility->getMappedStoreIdsFromSolrCore($solrcore);
    			if (is_array($storeIds) && !empty($storeIds)) {
    				foreach ($storeIds as $storeid) {
    					$this->updateSingleProductBySolrCore($solrcore, $productid, $storeid);
    				}
    			}
    		}
    	}
    }

    public function updateSingleProductBySolrCore($solrcore, $productid, $storeid)
    {
    	if (!empty($solrcore) && !empty($productid) && !empty($storeid)) {
    		$_product = Mage::getModel('catalog/product')->setStoreId($storeid)->load($productid);

    		if ( !$_product || (isset($_product) && !$_product->getId()) ) {//product deleted
    			$this->deleteSolrDocument($productid, $solrcore, $storeid);
    		}
    		else if($_product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) //Product disabled
    		{
    			$this->deleteSolrDocument($_product->getId(), $solrcore, $storeid);
    		}
    		else
    		{
    			if ($_product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
    			{
    				$this->deleteSolrDocument($_product->getId(), $solrcore, $storeid);
    			}
    			else
    			{
    				$this->updateSolrDocument($solrcore, $_product, $storeid);
    				$this->cleanUpIndexData($solrcore, $_product);
    			}
    		}
    	}
    }
    /**
     * Find and remove all solr documents which mapped to Magento product, but the magento product has no website assigned
     * @param string $solrcore
     * @param Mage_Catalog_Model_Product $_product
     */
    public function cleanUpIndexData($solrcore, $_product)
    {
        $productStoreIds = $_product->getStoreIds();
        $mappedStoreIds = $this->ultility->getMappedStoreIdsFromSolrCore($solrcore);

        if (is_array($productStoreIds) && is_array($mappedStoreIds) && $_product->getId() > 0) {
            $storeIds = array_diff($mappedStoreIds, $productStoreIds);

            if (is_array($storeIds)) {
                $queryString = '';
                foreach ($storeIds as $storeid) {
                    if ($storeid > 0) {
                        $queryString .= '<query>store_id:'.$storeid.'+AND+products_id:'.$_product->getId().'</query>';
                    }
                }
                if (!empty($queryString)) {
                    $solrServerUrl =$this->getSolrServerUrl();
                    $deleteDocumentUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/update?stream.body=<delete>'.$queryString.'</delete>&commit=true&json.nl=map&wt=json';
                    $this->doRequest($deleteDocumentUrl);
                }
            }
        }
    }

	/**
	 * Update solr document by product and store id
	 * @param string $solrcore
	 * @param Mage_Catalog_Model_Product $product
	 * @param int $storeid
	 */
    public function updateSolrDocument($solrcore, $product, $storeid)
    {
    	$solrServerUrl =$this->getSolrServerUrl();
    	$updateUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/update/json?commit=true&wt=json';
    	$storeObject = Mage::getModel('core/store')->load($storeid);
    	$collection = $this->ultility->getProductCollectionByProduct($product, $storeid);
    	$dataArray = $this->ultility->parseJsonData($collection, $storeObject);
    	$jsonData = $dataArray['jsondata'];
    	$returnNoOfDocuments = $this->postJsonData($jsonData, $updateUrl, $solrcore);
    	$msg = 'Updated solr document for the product ['.$product->getName.'] in store ['.$storeid.'] successfully';
    	$this->ultility->writeLog($msg, $storeid, $solrcore, 0, true);
    	Mage::app()->getCache()->clean('matchingAnyTag', array('solrbridge_solrsearch'));
    }
	/**
	 * Delete solr document by product id
	 * @param int $productid
	 * @param string $solrcore
	 * @param int $storeid
	 */
    public function deleteSolrDocument($productid, $solrcore, $storeid)
    {

    	$solrServerUrl =$this->getSolrServerUrl();
    	$existingSolrDocumentCount = Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);
    	if ($existingSolrDocumentCount > 0)
    	{
    		$deleteDocumentUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/update?stream.body=<delete><query>products_id:'.$productid.'</query></delete>&commit=true';
    		if ($storeid > 0) {
    			$deleteDocumentUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/update?stream.body=<delete><query>unique_id:'.$storeid.'P'.$productid.'</query></delete>&commit=true';
    		}
    		$this->doRequest($deleteDocumentUrl);
    		//Remove product id from log table
    		$this->ultility->removeLogProductId($productid, $solrcore);
    		$msg = 'Removed solr document for the product ['.$productid.'] in store ['.$storeid.'] successfully';
    		$this->ultility->writeLog($msg, $storeid, $solrcore, 0, true);
    	}

    	Mage::app()->getCache()->clean('matchingAnyTag', array('solrbridge_solrsearch'));
    }
    /**
     * Delete solr documents by category id
     * @param int $categoryid
     * @param string $solrcore
     * @param int $storeid
     */
    public function deleteSolrDocumentByCategory($categoryid, $solrcore, $storeid)
    {
    	$solrServerUrl =$this->getSolrServerUrl();
    	$existingSolrDocumentCount = Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);

    	if ( (int)$existingSolrDocumentCount > 0 ) {
    		try{
    			$currentCat = Mage::getModel('catalog/category')->load($categoryid);
    			$catIds = array($categoryid);
    			$childrenCatIds = Mage::getModel('catalog/category')->getResource()->getChildren($currentCat, true);
    			if (is_array($childrenCatIds) && !empty($childrenCatIds)){
    				$catIds = array_merge($catIds, $childrenCatIds);
    			}
    			$queryString = '';
    			$queryString2 = '';
    			if (is_array($catIds) && !empty($catIds)) {
    				$count = count($catIds);
    				$index = 1;
    				foreach ($catIds as $id) {
    					$queryString .= '<query>category_id:'.$id.'</query>';
    					if ($index == $count) {
    						$queryString2 .= 'category_id:'.$id;
    					}else{
    						$queryString2 .= 'category_id:'.$id.'+OR+';
    					}
    					$index++;
    				}
    			}

    			if (!empty($queryString) && !empty($queryString2)) {

    				//Get all documents which belong to categories to be removed
    				$queryUrl = trim($solr_server_url,'/').'/'.$solrcore.'/select/?q=*:*&fl=products_id&fq='.trim($queryString2,'+OR+').'&json.nl=map&wt=json';

    				$solrData = $this->doRequest($queryUrl);
    				$productIds = array();
    				if (is_array($solrData) && isset($solrData['response']['numFound'])) {
    					$numCount = $solrData['response']['numFound'];
    					if ( (int)$numCount > 0 ){
    						if ( isset($solrData['response']['docs']) && is_array($solrData['response']['docs']) ) {
    							foreach ($solrData['response']['docs'] as $doc){
    								if ( isset($doc['products_id']) && is_numeric(trim($doc['products_id'])) ) {
    									$productIds[] = $doc['products_id'];
    								}
    							}
    						}
    					}
    				}

    				$Url = trim($solr_server_url,'/').'/'.$solrcore.'/update?stream.body=<delete>'.$queryString.'</delete>&commit=true&json.nl=map&wt=json';
    				$data = $this->doRequest($Url);

    				if ( is_array($productIds) && !empty($productIds) ) {
    					$this->ultility->removeLogProductId($productIds, $solrcore);

    					$msg = 'Removed ['.count($productIds).'] solr documents which belong to category id ['.$categoryid.'] and it\'s children from solr core ['.$solrcore.']';
    					$msg = mysql_real_escape_string($msg);
    					$this->ultility->writeLog($msg, $storeid, $solrcore, 0, true);
    				}
    			}
    		}catch (Exception $e){
    			$msg = '[ERROR] - '.$e->getMessage();
    			$this->ultility->writeLog($msg, 0, '', 0, true);
    		}
    	}
    	Mage::app()->getCache()->clean('matchingAnyTag', array('solrbridge_solrsearch'));
    }
    /**
     * This function run to remove related documents when magento category got updated
     * @param int $categoryid
     */
    public function updateSingleCategory($categoryid)
    {
    	$availableCores = $this->ultility->getAvailableCores();

    	foreach ($availableCores as $solrcore => $infoArray)
    	{
    		if ( isset($infoArray['stores']) && strlen(trim($infoArray['stores'], ',')) > 0 )
    		{
    			$storeIdArray = trim($infoArray['stores'], ',');
    			$storeIdArray = explode(',', $storeIdArray);

    			foreach ($storeIdArray as $storeid)
    			{
    				try{
    					$category = Mage::getModel('catalog/category')->setStoreId($storeid)->load($categoryid);

    					if ( !$category || (isset($category) && !$category->getId()) )
    					{
    						$this->deleteSolrDocumentByCategory($categoryid, $solrcore, $storeid);
    					}
    					else if(!$category->getIsActive())
    					{
    						$this->deleteSolrDocumentByCategory($categoryid, $solrcore, $storeid);
    					}
    					else
    					{
    						$this->deleteSolrDocumentByCategory($categoryid, $solrcore, $storeid);
    					}
    				}
    				catch (Exception $e)
    				{
    					$msg = '[ERROR] - '.$e->getMessage();
    					$this->ultility->writeLog($msg, 0, '', 0, true);
    				}
    			}
    		}
    	}

    }
    /**
     * Truncate solr index by core
     * @param string $solrcore
     */
    public function truncateSolrCore($solrcore = 'english')
    {
    	$solrServerUrl =$this->getSolrServerUrl();

    	$storeMappingString = Mage::getStoreConfig('solrbridgeindices/'.$solrcore.'/stores', 0);

    	$totalSolrDocuments = Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);

    	//Solr delete all docs from index
    	$clearnSolrIndexUrl = trim($solrServerUrl,'/').'/'.$solrcore.'/update?stream.body=<delete><query>*:*</query></delete>&commit=true';

    	$this->doRequest($clearnSolrIndexUrl);

    	Mage::app()->getCache()->clean('matchingAnyTag', array('solrbridge_solrsearch'));
    }

    /**
     * Post json data to Solr Server for Indexing
     * @param string $jsonData
     * @param string $updateurl
     * @param string $solrcore
     * @return number
     */
    public function postJsonData($jsonData, $updateurl, $solrcore)
    {

    	if (!function_exists('curl_init')){
    		echo 'CURL have not installed yet in this server, this caused the Solr index data out of date.';
    		exit;
    	}else{
    		if(!isset($jsonData) && empty($jsonData)) {
    			return 0;
    		}

    		$postFields = array('stream.body'=>$jsonData);

    		$output = $this->doRequest($updateurl, $postFields);

    		if (isset($output['responseHeader']['QTime']) && intval($output['responseHeader']['QTime']) > 0)
    		{
    			return Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);
    		}else {
    			return 0;
    		}
    	}
    }
}