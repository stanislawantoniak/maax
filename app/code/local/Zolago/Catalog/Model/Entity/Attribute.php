<?php

/**
 * Class Zolago_Catalog_Model_Entity_Attribute
 */
class Zolago_Catalog_Model_Entity_Attribute extends Mage_Catalog_Model_Entity_Attribute {

    /**
     * Return store label of attribute
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($storeId = null) {
        /** @var Zolago_Catalog_Model_Resource_Eav_Attribute $model */
        $model = Mage::getResourceModel('zolagocatalog/eav_attribute');
        return $model->getStoreLabel($storeId);
    }
}
