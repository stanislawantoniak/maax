<?php

class Zolago_Solrsearch_Model_Queue extends Varien_Data_Collection{
	
	/**
	 * @var int
	 */
	protected $_limit = 150;
	
	/**
	 * @var int
	 */
	protected $_toProcessing = 0;
	
	/**
	 * @var int
	 */
	protected $_processedCores = 0;
	
	/**
	 * @var array
	 */
	protected $_exceptions = array();
	
	/**
	 * @var Exception
	 */
	protected $_hardException;
	
	/**
	 * @var Zolago_Solrsearch_Model_Resource_Solr
	 */
	protected $_solr;
	
	/**
	 * @var int
	 */
	protected $_processingTime;
	
	
	/**
	 * @return int
	 */
	public function getProcessedCount() {
		$collection = $this->getResourceCollection();
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_DONE);
		return $collection->getSize();
	}
	
	/**
	 * @return int
	 */
	public function getToDeleteCount() {
		$collection = $this->getResourceCollection();
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		$collection->addFieldToFilter("delete_only", 1);
		return $collection->getSize();
	}
	
	
	/**
	 * @return int
	 */
	public function getToReindexCount() {
		$collection = $this->getResourceCollection();
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		$collection->addFieldToFilter("delete_only", 0);
		return $collection->getSize();
	}
	
	
	/**
	 * @return bool
	 */
	public function isEmpty() {
		$collection = $this->getResourceCollection();
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		return !(bool)$collection->getSize();
	}
	
	/**
	 * Cleanup processed entries
	 * @return Zolago_Solrsearch_Model_Queue
	 */
	public function cleanup() {
		$this->getResource()->cleanup();
		return $this;
	}
	
	/**
	 * @param array $items
	 * @return Zolago_Solrsearch_Model_Queue
	 * @todo Impelemnt
	 */
	public function pushMultiple(array $items) {
		$resource = Mage::getResourceModel("zolagosolrsearch/queue_item");
		$item->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		
		// Do insert on 
		return $this;
	}
	
	/**
	 * @param Zolago_Solrsearch_Model_Queue_Item $item
	 * @return Zolago_Solrsearch_Model_Queue
	 */
	public function push(Zolago_Solrsearch_Model_Queue_Item $item) {

        /** @var Zolago_Solrsearch_Model_Resource_Queue_Item $resource */
		$resource = Mage::getResourceModel("zolagosolrsearch/queue_item");
		$item->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		// Skup double items
		if(!$item->getId()){
			$resource->fetchItemId($item);
			//$this->_log("Single product {$item->getProductId()} added to queue with store {$item->getStoreId()}");
			$item->save();
		}
		return $this;
	}
	
	/**
	 * @return Zolago_Solrsearch_Model_Resource_Queue_Item_Collection
	 */
	protected function getResourceCollection($fresh=false) {
		return Mage::getResourceModel("zolagosolrsearch/queue_item_collection");
	}
	
	/**
	 * 
	 * @return type
	 */
	public function getHardException() {
		return $this->_hardException;
	}
	
	/**
	 * @return array
	 */
	public function getExceptions() {
		return $this->_exceptions;
	}
	
	/**
	 * @return int
	 */
	public function getProcessedItems() {
		return $this->_processedItems;
	}
	
	
	/**
	 * @return int
	 */
	public function getProcessedCores() {
		return $this->_processedCores;
	}
	
	public function getProcessingTime() {
		return $this->_processingTime;
	}
	
	
	/**
	 * @return boolean
	 */
	public function process() {
		$helepr = Mage::helper("zolagosolrsearch");
		$time = time();
		$processedCores = array();
		/* @var $helepr Zolago_Solrsearch_Helper_Data */
		$this->prepareToProcessing();
		foreach($helepr->getAvailableCores() as $core){
			$coreCount = $this->processByCore($core);
			if($coreCount){
				$this->_processedItems += $coreCount;
				$this->_processedCores++;
				$processedCores[] = $core;
			}
			if($this->getHardException()){
				return false;
			}
		}
		
		foreach($processedCores as $core){
			$this->_getSolr()->sendCommit($core);
		}
		
		$this->_processingTime = time()-$time; 
		return true;
	}
	
	public function prepareToProcessing() {
		$this->_processedCores = 0;
		$this->_processedItems = 0;
		$this->_processingTime = 0;
		$this->_hardException = null;
	}

	/**
	 * @param string $core
	 * @return boolean|int
	 */
	public function processByCore($core) {
		$this->_toProcessing = 0;
		try{
			while($this->_processByCore($core)===false);
		} catch (Exception $ex) {
			$this->_hardException = $ex;
			Mage::logException($ex);
		}
		return $this->_toProcessing;
	}
	
	/**
	 * @return Zolago_Solrsearch_Model_Resource_Queue_Item
	 */
	public function getResource() {
		return Mage::getResourceSingleton("zolagosolrsearch/queue_item");
	}
	
	/**
	 * 
	 * @param string $core
	 * @return boolean
	 */
	protected function _processByCore($core) {
		$collection = $this->getResourceCollection();
		$resource = $this->getResource();
		
		$collection->addFieldToFilter("core_name", $core);
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		
		// Sort by inset 
		$collection->setOrder("created_at", "asc");
		$collection->setOrder("product_id", "asc");
		$collection->setOrder("delete_only", "desc");
		
		$collection->getSelect()->limit($this->_limit);
		
		// Load and count
		$itemsToProcess = $collection->count();

		
		// Is sth to process
		if(!$itemsToProcess){
			return $this->_toProcessing;
		}
		
		$toReindex = array();
		$toDelete = array();
		
		// Save status as processign
		$resource->updateStatus($collection, 
					Zolago_Solrsearch_Model_Queue_Item::STATUS_PROCESSING);
		
		$this->_toProcessing += $itemsToProcess;
		
		// Make processing
		$afterStatus = Zolago_Solrsearch_Model_Queue_Item::STATUS_DONE;
		
		try{
			// 1. Collect data
			foreach($collection as $item){
				
				// Collect delete products
				if(!isset($toDelete[$item->getStoreId()])){
					$toDelete[$item->getStoreId()] = array();
				}
				$toDelete[$item->getStoreId()][$item->getProductId()] = true;
				
				if(!$item->getDeleteOnly()){
					if(!isset($toReindex[$item->getStoreId()])){
						$toReindex[$item->getStoreId()] = array();
					}
					$toReindex[$item->getStoreId()][$item->getProductId()] = true;
				}
			}
			
			// 1. Delete item form solr
			$this->_delteSolrDocs($toDelete, $core);
			
			// 2. Make reindex if nessery
			if($toReindex){
				$this->_reindexSolrDocs($toReindex, $core);
			}
			
		} catch (Exception $ex) {
			$afterStatus = Zolago_Solrsearch_Model_Queue_Item::STATUS_FAIL;
			$this->_exceptions[] = $ex;
			// Log only first exception
			if(count($this->_exceptions)==1){
				Mage::logException($ex);
			}
		}
		
		// Make after processign confirms
		$resource->updateStatus($collection, $afterStatus);
		
		// Clear data
		$collection->clear();
		unset($collection);
		unset($toDelete);
		unset($toReindex);
		
		return false;
	}
	
	/**
	 * @param array $storeProductsArray
	 */
	protected function _delteSolrDocs(array $storeProductsArray, $core) {
		return $this->_getSolr()->deleteSolrDocumentByProductIds($storeProductsArray, $core);
	}
	
	
	/**
	 * @param array $storeProductsArray
	 */
	protected function _reindexSolrDocs(array $storeProductsArray, $core) {
		return $this->_getSolr()->reindexByProductIds($storeProductsArray, $core);
	}
	
	
	/**
	 * @return Zolago_Solrsearch_Model_Resource_Solr
	 */
	protected function _getSolr() {
		if(!$this->_solr){
			$this->_solr = Mage::getResourceModel('zolagosolrsearch/solr');
		}
		return $this->_solr;
	}
	
	
}