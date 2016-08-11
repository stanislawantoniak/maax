<?php

/**
 * Class GH_Statements_Model_Resource_Vendor_Balance_Collection
 */
class GH_Statements_Model_Resource_Vendor_Balance_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/vendor_balance');
    }

    /**
     * @param Zolago_Dropship_Model_Vendor|int $vendor
     * @return $this
     */
    public function addVendorFilter($vendor) {
        if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
            $vendor = $vendor->getId();
        }
        $this->addFieldToFilter('main_table.vendor_id',(int)$vendor);
        return $this;
    }
}