<?php
class Zolago_Sizetable_Helper_Data extends Mage_Core_Helper_Abstract{


    public function getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id)
    {
        /** @var Zolago_Sizetable_Model_Resource_Sizetable $model */
        $model = Mage::getModel('zolagosizetable/resource_sizetable');

        return $model->getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id);
    }

    /**
     * brand id
     * @return int
     */
    public function getBrandId() {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product','manufacturer');
        return $attribute->getId();
    }
} 