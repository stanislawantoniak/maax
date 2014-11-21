<?php
class Zolago_Sizetable_Helper_Data extends Mage_Core_Helper_Abstract{


    public function getSizetableCMS($vendor_id = null, $store_id = null, $attribute = null)
    {
        /** @var Zolago_Sizetable_Model_Resource_Sizetable $model */
        $model = Mage::getModel('zolagosizetable/resource_sizetable');

        return $model->getSizetableCMS($vendor_id, $store_id, $attribute);
    }
} 