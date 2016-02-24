<?php

/**
 * Class Zolago_Catalog_Block_Product_Description_History
 */
class Zolago_Catalog_Block_Product_Description_History extends Mage_Core_Block_Template
{

    public function getChangesHistory()
    {

        /* @var $descriptionHistory Zolago_Catalog_Block_Product_Description_History */
        $descriptionHistory = Mage::getModel("zolagocatalog/description_history");

        $historyLifetimeLimit = $descriptionHistory->getHistoryLifetimeLimit();


        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $historyLifeTime = date("Y-m-d H:m:s", strtotime("-{$historyLifetimeLimit} hours", $currentTimestamp));

        $collection = $descriptionHistory
            ->getCollection()
            ->addFieldToFilter("changes_date", array("gteq" => $historyLifeTime))
            ->addVendorFilter($this->getVendor())
            ->addOrder("changes_date", Varien_Data_Collection::SORT_ORDER_DESC)
            ->setPageSize($descriptionHistory->getHistoryCountLimit());

        return $collection;
    }
    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {
        return Mage::getModel("udropship/session")->getVendor();
    }

}
