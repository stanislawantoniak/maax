<?php

/**
 * Class Zolago_Catalog_Block_Product_Description_History
 */
class Zolago_Catalog_Block_Product_Description_History extends Mage_Core_Block_Template
{

    public function getChangesHistory()
    {
        $collection = Mage::getModel("zolagocatalog/description_history")->getCollection();
        $collection->addVendorFilter($this->getVendor());
        $collection->addOrder("changes_date", Varien_Data_Collection::SORT_ORDER_DESC);
        return $collection;
    }
    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {
        return Mage::getModel("udropship/session")->getVendor();
    }

}
