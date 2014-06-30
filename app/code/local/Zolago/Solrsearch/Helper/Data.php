<?php
class Zolago_Solrsearch_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * @var array
	 */
	protected $_cores;
	
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
}