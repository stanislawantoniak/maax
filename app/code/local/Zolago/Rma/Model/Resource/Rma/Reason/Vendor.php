<?php
class Zolago_Rma_Model_Resource_Rma_Reason_Vendor extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/rma_reason_vendor', "vendor_return_reason_id");
    }
	
	/**
	 * Return all vendors with no pointed reason bind
	 * @param Zolago_Rma_Model_Rma_Reason $reason
	 * @param ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection $collection
	 * @return Zolago_Rma_Model_Resource_Rma_Reason_Vendor
	 */
	public function addUnbindRmaReasonFilterToVendorCollection(
			Zolago_Rma_Model_Rma_Reason $reason, 
			ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection $collection) {
		
		$subselect = $this->getReadConnection()->select();
	
		$subselect->from(array("vr"=>$this->getTable('zolagorma/rma_reason_vendor')), "vr.vendor_id");
		$subselect->where("vr.return_reason_id=?", $reason->getId());
		
		$collection->
			getSelect()->
			where("vendor_id NOT IN(?)", $subselect);
		
		return $this;
		
	}
	
	/**
     * Prepare data for save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object){
		
		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew())) {
			
			if(!$object->getCreatedAt()) $object->setCreatedAt($currentTime);
			if($object->getUseDefault() !== 1) $object->setUseDefault(0);
		}
		$object->setUpdatedAt($currentTime);
		
		($object->getUseDefault() == "1") ? $object->setUseDefault(1) : $object->setUseDefault(0); 
		
		return parent::_prepareDataForSave($object);
		
	}
}