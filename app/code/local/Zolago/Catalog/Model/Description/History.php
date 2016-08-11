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
            $oldValue = $changedCollectionItem->getData($attributeCode);

            if (!empty($oldValue) && !empty($attributeValue))
                $oldValues[$changedCollectionItem->getId()] = $changedCollectionItem->getData($attributeCode);

        }

        //Do not save if change was from empty value
        if(empty($oldValues))
            return;


        $changesData = array(
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

    /**
     * @param Zolago_Catalog_Model_Description_History $changesHistoryItem
     * @throws Exception
     */
    public function revertChangesHistory(Zolago_Catalog_Model_Description_History $changesHistoryItem)
    {
        $changesData = unserialize($changesHistoryItem->getData("changes_data"));

        $attributeCode = $changesData["attribute_code"];


        $store = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');

        foreach ($changesData["old_value"] as $productsAffectedId => $oldValue) {
            //nie można tą funkcją przywrócić pustego opisu
            if (!empty($oldValue)) {
                $aM->updateAttributesPure(
                    array($productsAffectedId),
                    array($attributeCode => $oldValue),
                    $store
                );
            }

        }

        $changesHistoryItem->delete();
    }

}