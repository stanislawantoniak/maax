<?php

/**
 * Class Zolago_Catalog_Model_Resource_Eav_Attribute
 */
class Zolago_Catalog_Model_Resource_Eav_Attribute extends Mage_Catalog_Model_Resource_Eav_Attribute {

    /**
     * Return store label of attribute
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($storeId = null) {
        if ($this->hasData('store_label')) {
            return $this->getData('store_label');
        }
        $store = Mage::app()->getStore($storeId);
        if (!$store->isAdmin()) {
            $labels = $this->getStoreLabels();
            if (isset($labels[$store->getId()])) {
                return $labels[$store->getId()];
            }
            if (isset($labels[$store->getAttributeBaseStore()])) {
                return $labels[$store->getAttributeBaseStore()];
            }
        }
        return $this->getFrontendLabel();
    }
}
