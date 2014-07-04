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
	 * @return bool
	 */
	public function isEmpty() {
		$collection = $this->getResourceCollection();
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		return !(bool)$collection->getSize();
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
		
		$resource = Mage::getResourceModel("zolagosolrsearch/queue_item");
		$item->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		// Skup double items
		if(!$item->getId() && !$resource->fetchProductId($item)){
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
	
	
	/**
	 * @return boolean
	 */
	public function process() {
		$helepr = Mage::helper("zolagosolrsearch");
		/* @var $helepr Zolago_Solrsearch_Helper_Data */
		$this->_processedCores = 0;
		$this->_processedItems = 0;
		$this->_hardException = null;
		foreach($helepr->getAvailableCores() as $core){
			$coreCount = $this->processByCore($core);
			if($coreCount){
				$this->_processedItems += $coreCount;
				$this->_processedCores++;
			}
			if($this->getHardException()){
				return false;
			}
		}
		return true;
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
		$collection->setOrder("delete_only", "desc");
		$collection->setOrder("created_at", "asc");
		$collection->setOrder("product_id", "asc");
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
				$toDelete[$item->getProductId()] = true;
				if(!$item->getDeleteOnly()){
					$toReindex[$item->getProductId()] = true;
				}
			}
			
			// 1. Delete item form solr
			$this->_delteSolrDocs(array_keys($toDelete), $core);
			
			// 2. Make reindex if nessery
			if($toReindex){
				$this->_reindexSolrDocs(array_keys($toReindex), $core);
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
	 * @param Zolago_Solrsearch_Model_Queue_Item $productIds
	 */
	protected function _delteSolrDocs(array $productIds, $core) {
		$this->_getSolr()->deleteSolrDocumentByProductIds($productIds, $core);
	}
	
	
	/**
	 * @param Zolago_Solrsearch_Model_Queue_Item $items
	 */
	protected function _reindexSolrDocs(array $items, $core) {
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