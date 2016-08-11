<?php

class Zolago_Banner_Model_Resource_Banner_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagobanner/banner');
    }
    /**
     * @return Zolago_Banner_Model_Resource_Banner_Collection
     */
    public function addActiveFilter() {
        $this->addFieldToFilter("is_active", 1);
        return $this;
    }

    /**
     * @param ZolagoOs_OmniChannel_Model_Vendor | ini $vendor
     * @return Zolago_Banner_Model_Resource_Banner_Collection
     */
    public function addVendorFilter($vendor) {
        if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
            $vendor = $vendor->getId();
        }
        $this->addFieldToFilter('vendor_id',(int)$vendor);
        return $this;
    }
}
