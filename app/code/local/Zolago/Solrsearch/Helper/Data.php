<?php
class Zolago_Solrsearch_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * @var array
	 */
	protected $_cores;
	/**
	 * @var array
	 */
	protected $_availableStoreIds;
	
	/**
	 * 
	 * @param type $storeId
	 * @return type
	 */
	public function getCoresByStoreId($storeId) {
		$cores = array();
		foreach($this->getCores() as $core=>$data){
			if(isset($data['stores'])){
				$ids = explode(",", trim($data['stores'], ","));
				if(in_array($storeId,$ids)){
					$cores[] = $core;
				}
			}
		}
		return $cores;
	}
	
	/**
	 * @return array
	 */
	public function getCores() {
		if(!$this->_cores){
			$this->_cores  = (array) Mage::getStoreConfig('solrbridgeindices', 0);
		}
		return $this->_cores;
	}
	
	/**
	 * Returns vaialble stores (cores assigned to store)
	 * @return array
	 */
	public function getAvailableStores() {
		if(!is_array($this->_availableStoreIds)){
			$this->_availableStoreIds = array();
			foreach($this->getCores() as $core=>$data){
				if(isset($data['stores'])){
					$ids = explode(",", trim($data['stores'], ","));
					$this->_availableStoreIds = array_merge($this->_availableStoreIds, $ids);
				}
			}
			$this->_availableStoreIds = array_values(
					array_filter(array_unique($this->_availableStoreIds)));
		}
		return $this->_availableStoreIds;
	}
}