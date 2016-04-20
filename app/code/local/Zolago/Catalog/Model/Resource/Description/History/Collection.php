<?php

/**
 * Class Zolago_Catalog_Model_Resource_Description_History_Collection
 */
class Zolago_Catalog_Model_Resource_Description_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocatalog/description_history');
    }

    /**
     * @param ZolagoOs_OmniChannel_Model_Vendor|int $vendor
     * @return Zolago_Banner_Model_Resource_Banner_Collection
     */
    public function addVendorFilter($vendor)
    {
        if ($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor) {
            $vendor = $vendor->getId();
        }
        $this->addFieldToFilter('main_table.vendor_id', (int)$vendor);
        return $this;
    }
}