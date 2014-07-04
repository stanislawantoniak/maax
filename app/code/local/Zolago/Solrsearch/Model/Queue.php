<?php

class Zolago_Solrsearch_Model_Queue extends Varien_Data_Collection{
	
	protected $_limit = 150;
	protected $_toProcessing = 0;
	protected $_resourceCollection;
	protected $_processedCores = 0;
	protected $_processedItems = 0;
	
	
	public function process() {
		
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
	protected function getResourceCollection() {
		if(!$this->_resourceCollection){
			$this->_resourceCollection = Mage::getResourceModel("zolagosolrsearch/queue_item_collection");
		}
		return $this->_resourceCollection ;
	}

	/**
	 * @param int $storeId
	 * @return boolean|int
	 */
	public function processByCore($storeId) {
		$this->_toProcessing = 0;
		while(!$this->_processByStore($storeId));
		return $this->_toProcessing;
	}
	
	/**
	 * 
	 * @param string $core
	 * @return boolean
	 */
	protected function _processByCore($core) {
		$collection = $this->getResourceCollection();
		
		$collection->clear();
		$collection->addFieldToFilter("core_name", $core);
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		$collection->setOrder("delete_only", "desc");
		$collection->setOrder("created_at", "asc");
		$collection->setOrder("product_id", "asc");
		$collection->getSelect()->limit($this->_limit);
		$itemsToProcess = 0;
		foreach($collection as $item){
			/* @var $item Zolago_Solrsearch_Model_Queue_Item */
			$item->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_PROCESSING);
			$itemsToProcess++;
		}
		
		// Is sth to process
		$this->_toProcessing += $itemsToProcess;
		if(!$itemsToProcess){
			return $this->_toProcessing;
		}
		// Save processing status of items
		$collection->save();
		
		// Make processing
		$afterStatus = Zolago_Solrsearch_Model_Queue_Item::STATUS_DONE;
		
		try{
			// 1. Delete item
			// 2. Make reindex if nessery
			// Sth
		} catch (Exception $ex) {
			$afterStatus = Zolago_Solrsearch_Model_Queue_Item::STATUS_FAIL;
			Mage::logException($ex);
		}
		
		// Make after processign confirms
		foreach($this as $item){
			/* @var $item Zolago_Solrsearch_Model_Queue_Item */
			$item->setStatus($afterStatus);
			$item->setProcessedAt(Varien_Date::now());
		}
		$collection->save();
		
		return false;
		
	}
}