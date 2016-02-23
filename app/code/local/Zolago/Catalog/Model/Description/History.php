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

    protected function _construct()
    {
        $this->_init('zolagocatalog/description_history');
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
     * @param $attributeCode
     * @param $attributeValue
     * @param $collection
     * @param $attributeMode
     */
    public function updateChangesHistory($vendorId, $ids, $attributeCode, $attributeValue, $collection, $attributeMode = ""){

        $oldValues = array();
        $changedCollection = $collection->addFieldToFilter("entity_id", array("in" => $ids));
        foreach($changedCollection as $changedCollectionItem){
            $oldValues[$changedCollectionItem->getId()] = $changedCollectionItem->getData($attributeCode);
        }

        //Mage::log($ids, null, "ids.log");
        //Mage::log($attributeCode, null, "attribute_code.log");
        //Mage::log($attributeMode[$attributeCode], null, "attribute_mode.log");

        //Mage::log($attributeValue, null, "attribute_value.log");

        $changesData = array(
            "ids" => $ids,
            "attribute_code" => $attributeCode,
            "attribute_mode" => $attributeMode,
            "old_value" => $oldValues,
            "new_value" => $attributeValue

        );
        $data = array(
            "vendor_id" => $vendorId,
            "changes_date" => date('Y-m-d H:i:s', time()),
            "changes_data" => serialize($changesData)
        );
        $this->addData($data);
        $this->save();

    }

}