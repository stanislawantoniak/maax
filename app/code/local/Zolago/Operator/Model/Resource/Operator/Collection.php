<?php

class Zolago_Operator_Model_Resource_Operator_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagooperator/operator');
    }
	
	/**
	 * @return Zolago_Operator_Model_Resource_Operator_Collection
	 */
	public function addActiveFilter() {
		$this->addFieldToFilter("is_active", 1);
		return $this;
	}
	
    /**
     * vendor filter
     */
    public function addVendorFilter($vendor) {
		if($vendor instanceof Unirgy_Dropship_Model_Vendor){
			$vendor = $vendor->getId();
		}
		$this->addFieldToFilter('vendor_id',$vendor);
		return $this;
    }
    
}
