<?php

class Zolago_Catalog_Model_Resource_Category_Filter extends Mage_Core_Model_Resource_Db_Abstract {
    
	protected $_serializableFields   = array(
        'option_ids' => array(null, array())
    );
	
	protected function _construct() {
		$this->_init('zolagocatalog/category_filter', "filter_id");
	}

	/**
	 * @param array $ids
	 * @return Zolago_Catalog_Model_Resource_Category_Filter
	 */
	public function deleteMultitply($ids) {
		$adapter = $this->_getWriteAdapter();
		$where = $adapter->quoteInto("filter_id IN (?)", $ids);
		$this->_getWriteAdapter()->delete($this->getMainTable(), $where);
		return $this;
	}
	
	/**
	 * Set times
	 * @param Mage_Core_Model_Abstract $object
	 * @return type
	 */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {

			$object->setCreatedAt($currentTime);
		}
		$object->setUpdatedAt($currentTime);
		return parent::_prepareDataForSave($object);
	}
	

}

