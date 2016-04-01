<?php

class Zolago_Solrsearch_Model_Resource_Solr extends SolrBridge_Solrsearch_Model_Resource_Solr
{
	
	/**
	 * @param array $storeProductsArray HISTOGRAM store1=>array(productId1=>true, ...), ...
	 * @param string $core
	 * @return boolean
	 */
	public function deleteSolrDocumentByProductIds(array $storeProductsArray, $core) {
		if(!$storeProductsArray || !$core){
			return;
		}
		// Build delete query
		$solrServerUrl =$this->getSolrServerUrl();
		$separator = "+OR+";
		$queryProduct = "";
		
		foreach($storeProductsArray as $storeId=>$products){
			foreach($products as $productId=>$true){
				$queryProduct .= "unique_id:" . $storeId. "P" . $productId . $separator;
			}
		}
		$queryProduct = "<query>" . trim($queryProduct, $separator) . "</query>";
		
//		$url = trim($solrServerUrl,'/').'/'.$core.
//			'/update?stream.body=<delete>'.$queryProduct.'</delete>&commit=true&json.nl=map&wt=json';
		$url = trim($solrServerUrl,'/').'/'.$core.
			'/update?stream.body=<delete>'.$queryProduct.'</delete>&json.nl=map&wt=json';
		//Mage::log("Delete start ($core) / connection start");
    	///Mage::log($url);
		Mage::log("Make push to slor DEV");
		$this->doRequest($url);
		Mage::log("Delete stop ($core) / connection end");
		return true;		
	}
	
	
	/**
	 * @param array $storeProductsArray HISTOGRAM store1=>array(productId1=>true, ...), ...
	 * @param string $core
	 * @return boolean
	 */
	public function reindexByProductIds(array $storeProductsArray, $core) {
		foreach($storeProductsArray as $storeId=>$products){
			$collection = $this->_prepareImprovedCollection($storeId);
			$collection->addIdFilter(array_keys($products));
			$this->_doReindex($collection, Mage::app()->getStore($storeId), $core);
		}
	}
	
	
	/**
	 * @param Mage_Catalog_Model_Resource_Product_Collection $collection
	 * @param Mage_Core_Model_Store $store
	 * @param string $core
	 */
	protected function _doReindex(Mage_Catalog_Model_Resource_Product_Collection $collection,
			Mage_Core_Model_Store $store, $core) {
		
		//Mage::log("Reindex ($core) start");
		$solrServerUrl = $this->getSolrServerUrl();
    	$updateUrl = trim($solrServerUrl,'/').'/'.$core.'/update/json?&wt=json';
    	//$updateUrl = trim($solrServerUrl,'/').'/'.$core.'/update/json?commit=true&wt=json';
		$dataArray = $this->ultility->parseJsonData($collection, $store);
		//Mage::log("Data prepare after / connection start ($core)");
		//Mage::log($updateUrl);
		//Mage::log("Data: " . strlen($dataArray['jsondata']));
		Mage::log("Make push to slor DEV");
		$returnNoOfDocuments = $this->postJsonData($dataArray['jsondata'], $updateUrl, $core);
		Mage::log("Reindex ($core) stop / connection end ($returnNoOfDocuments)");		
		
	}
	
	public function sendCommit($core) {
		$solrServerUrl = $this->getSolrServerUrl();
		$url = trim($solrServerUrl,'/').'/'.$core.'/update/json?commit=true&wt=json';
		Mage::log("Commit update start");
		$this->doRequest($url);
		Mage::log("Commit update end");
	}
	
	
	
	/**
	 * @param int $storeId
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	protected function _prepareImprovedCollection($storeId){
		return Mage::getResourceModel("catalog/product_collection")->
				setStoreId($storeId);
	}
}