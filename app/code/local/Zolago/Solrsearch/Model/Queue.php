<?php

class Zolago_Solrsearch_Model_Queue extends Varien_Data_Collection{

	protected $_justAdded = array();
	
	protected $_limit = 150;
	protected $_toProcessing = 0;
	protected $_resourceCollection;
	
	/**
	 * @param array $items
	 * @return Zolago_Solrsearch_Model_Queue
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
		
		if(isset($this->_justAdded[$item->getStoreId()][$item->getProductId()])){
			return $this;
		}
		
		$resource = Mage::getResourceModel("zolagosolrsearch/queue_item");
		$item->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		// Skup double items
		if(!$item->getId() && !$resource->fetchProductId($item)){
			//$this->_log("Single product {$item->getProductId()} added to queue with store {$item->getStoreId()}");
			if(!isset($this->_justAdded[$item->getStoreId()])){
				$this->_justAdded[$item->getStoreId()][$item->getProductId()] = true;
			}
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
	public function processByStore($storeId) {
		$this->_toProcessing = 0;
		try{
			while(!$this->_processByStore($storeId));
		}catch(Exception $e){
			Mage::logException($e);
			return false;
		}
		return $this->_toProcessing;
	}
	
	/**
	 * 
	 * @param type $storeId
	 * @return boolean
	 */
	protected function _processByStore($storeId) {
		$collection = $this->getResourceCollection();
		
		$collection->clear();
		$collection->addFieldToFilter("store_id", $storeId);
		$collection->addFieldToFilter("status", Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		$collection->setOrder("created_at", "asc");
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