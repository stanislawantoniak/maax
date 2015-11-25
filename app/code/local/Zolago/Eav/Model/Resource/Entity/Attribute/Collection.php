<?php

/**
 * Class Zolago_Eav_Model_Resource_Entity_Attribute_Collection
 */
class Zolago_Eav_Model_Resource_Entity_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection {

    /**
     * Add store label to attribute by specified store id
     *
     * @param integer $storeId
     * @return $this
     */
    public function addStoreLabel($storeId) {
        /** @var Zolago_Catalog_Model_Resource_Product_Attribute_Collection $model */
        $model = Mage::getResourceModel('zolagocatalog/product_attribute_collection');
        return $model->addStoreLabel($storeId);
    }
}
