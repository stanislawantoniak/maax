<?php
class Zolago_Solrsearch_Model_Resource_Queue_Item extends Mage_Core_Model_Resource_Db_Abstract {
    protected function _construct() {
        $this->_init('zolagosolrsearch/queue_item','queue_id');
    }
	
	/**
	 * @param Varien_Data_Collection_Db $itemCollection
	 * @param type $status
	 * @return type
	 */
	public function updateStatus(Varien_Data_Collection_Db $itemCollection, $status) {
		$itemIds = array();
		foreach($itemCollection as $id=>$item){
			$itemIds[] = $id;
			$item->setStatus($status);
		}
		if(!$itemIds){
			return;
		}
		$data = array("status"=>$status);
		if($status==Zolago_Solrsearch_Model_Queue_Item::STATUS_DONE){
			$data['processed_at'] = Varien_Date::now();
		}
		$where = $this->_getWriteAdapter()->quoteInto("queue_id IN (?)", $itemCollection->getAllIds());
		$this->_getWriteAdapter()->update($this->getMainTable(), $data , $where);
	}
	
	
	/**
	 * @param Varien_Object $item
	 * @return boolean
	 */
	public function fetchProductId(Varien_Object $item) {
		if($item->getProductId() && $item->getStoreId() && $item->getStatus()){
			$select = $this->getReadConnection()->select();
			$select->from($this->getMainTable(), array("queue_id"));
			$select->where("product_id=?", $item->getProductId());
			$select->where("core_name=?", $item->getCoreName());
			$select->where("status=?", $item->getStatus());
			$select->where("delete_only=?", $item->getDeleteOnly());
		    if($result=$this->getReadConnection()->fetchOne($select)){
				$item->setId($result);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Set times
	 * @param Mage_Core_Model_Abstract $object
	 * @return ?
	 */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
			$object->setCreatedAt($currentTime);
		}
		// wait for new records
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getStatus()) {
			$object->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
		}
		return parent::_prepareDataForSave($object);
	}
}

