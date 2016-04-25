<?php

class Zolago_Sizetable_Model_Resource_Sizetable_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagosizetable/sizetable');
    }
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor|int $vendor
	 * @return Zolago_Sizetable_Model_Resource_Sizetable_Collection
	 */
	public function addVendorFilter($vendor){
		if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
			$vendor = $vendor->getId();
		}
		$this->addFieldToFilter(
			"main_table.vendor_id",
			$vendor
		);
		return $this;
	}

}
