<?php

/**
 * Class Zolago_Catalog_Model_Description_History
 *
 *
 * @method int getVendorId()
 * @method string getChangesDate()
 * @method string getChangesData()
 *
 */
class Zolago_Catalog_Model_Description_History extends Mage_Core_Model_Abstract
{
    const DESCRIPTION_HISTORY_COUNT_LIMIT = "udprod/product_description_history_changes_config/max_changes_count";
    const DESCRIPTION_HISTORY_LIFETIME_LIMIT = "udprod/product_description_history_changes_config/history_expiration_time";

    protected function _construct()
    {
        $this->_init('zolagocatalog/description_history');
    }

    /**
     * @return mixed
     */
    public function getHistoryCountLimit()
    {
        return Mage::getStoreConfig(self::DESCRIPTION_HISTORY_COUNT_LIMIT);
    }

    /**
     * @return string
     */
    public function getHistoryLifetimeLimit()
    {
        return Mage::getStoreConfig(self::DESCRIPTION_HISTORY_LIFETIME_LIMIT);
    }

    /**
     *
     * Change attribute $attributeCode
     * to value $attributeValue
     * in the product collection $collection
     * $attributeMode (for multiselect attributes "add" - Dodaj, "set" - Ustaw or "sub" - Usuń)
     *
     *
     * @param $vendorId
     * @param $ids
     * @param $attributeCode
     * @param $attributeValue
     * @param $collection Mage_Catalog_Model_Resource_Product_Collection
     * @param $attributeMode
     */
    public function updateChangesHistory($vendorId, $ids, $attributeCode, $attributeValue, $collection, $attributeMode = "")
    {

        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());

        $oldValues = array();
        $changedCollection = $collection
            ->addFieldToFilter("entity_id", array("in" => $ids));
        foreach ($changedCollection as $changedCollectionItem) {
            $oldValues[$changedCollectionItem->getId()] = $changedCollectionItem->getData($attributeCode);
        }

        $changesData = array(
            "ids" => $ids,
            "attribute_code" => $attributeCode,
            "attribute_mode" => $attributeMode,
            "old_value" => $oldValues,
            "new_value" => $attributeValue

        );
        $data = array(
            "vendor_id" => $vendorId,
            "changes_date" => date('Y-m-d H:i:s', $currentTimestamp),
            "changes_data" => serialize($changesData)
        );
        $this->addData($data);
        $this->save();

    }

}