<?php

/**
 * Class Zolago_Dropship_Model_Vendor_Brandshop
 */
class Zolago_Dropship_Model_Vendor_Brandshop extends Mage_Core_Model_Abstract
{

    protected function _construct() {
        $this->_init('zolagodropship/vendor_brandshop');
    }
    
    /**
     * load data by params
     */

    public function loadByVendorBrandshop($vendorId,$brandshopId) {
        $collection = $this->getCollection();
        $select = $collection->getSelect();
        $adapter = $select->getAdapter();
        $select->where($adapter->quoteInto('main_table.vendor_id=?',$vendorId));
        $select->where($adapter->quoteInto('main_table.brandshop_id=?',$brandshopId));
        $all = $collection->getAllIds();
        if (!empty($all)) {
            reset($all);
            $this->load(current($all));
        } else {
            $this->setVendorId($vendorId);
            $this->setBrandshopId($brandshopId);
        }
        return $this;
    }    
}
