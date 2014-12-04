<?php

class Zolago_Catalog_Model_Category extends Mage_Catalog_Model_Category
{
	/**
	 * Overload load method - load cached data if possible
	 * @param int $id
	 * @param string $field
	 * @return Zolago_Catalog_Model_Category
	 */
	public function load($id, $field=null) {
		
		// Skip cache in admin
		if(Mage::app()->getStore()->isAdmin()){
			return parent::load($id, $field);
		}
		
		Varien_Profiler::start("Loading category");
		$cacheKey = $this->_getCacheKey($id, $field, $this->getStoreId());
		
		if($cacheData = $this->_loadFromCache($cacheKey)){
			$this->_beforeLoad($id, $field);
			$this->setData(unserialize($cacheData));
			$this->_afterLoad();
			$this->setOrigData();
			$this->_hasDataChanges = false;
			Mage::log("From cache");
			Varien_Profiler::start("Loading category");
			return $this;
		}
		
		parent::load($id, $field);
		
		$this->_saveInCache($cacheKey, $this->getData());
		
		Mage::log("From DB");
		Varien_Profiler::start("Loading category");
		
		return $this;
	}
	
	/**
	 * @param string $key
	 * @return null | string
	 */
	protected function _loadFromCache($key){
		return Mage::app()->getCache()->load($key);
	}
	
	/**
	 * @param string $key
	 * @param array $data
	 */
	protected function _saveInCache($key, $data){
		$cache = Mage::app()->getCache();
		$oldSerialization = $cache->getOption("automatic_serialization");
		$cache->setOption("automatic_serialization", true);
		$cache->save($data, $key, array(), 600);
		$cache->setOption("automatic_serialization", $oldSerialization);
	}
	
	/**
	 * @param mixed $id
	 * @param string | null $field
	 * @param int $storeId
	 * @return string
	 */
	protected function _getCacheKey($id, $field, $storeId){
		if($field==null){
			$field = $this->getIdFieldName();
		}
		return "CATEGORY_" . $field . "_" . $id . "_" . $storeId;
	}
}