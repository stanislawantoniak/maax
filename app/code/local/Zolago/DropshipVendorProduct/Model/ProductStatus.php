<?php

class Zolago_DropshipVendorProduct_Model_ProductStatus extends Mage_Catalog_Model_Product_Status
{
    const STATUS_INVALID   = 7;

    static public function getOptionArray()
    {

        return array(
            self::STATUS_ENABLED => Mage::helper('zolagovendorproduct')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('zolagovendorproduct')->__('New'),
            self::STATUS_INVALID => Mage::helper('zolagovendorproduct')->__('Invalid'),
        );
    }

    static public function getAllOptions()
    {
        $res = array(
            array(
                'value' => '',
                'label' => Mage::helper('catalog')->__('-- Please Select --')
            )
        );
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
                'value' => $index,
                'label' => $value
            );
        }
        return $res;
    }
}