<?php

class Zolago_DropshipVendorProduct_Model_ProductStatus extends Mage_Catalog_Model_Product_Status
{
    const STATUS_INVALID   = 7;

    static public function getOptionArray($formatter = false)
    {
        $res = array(
            self::STATUS_ENABLED => Mage::helper('zolagovendorproduct')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('zolagovendorproduct')->__('New'),
            self::STATUS_INVALID => Mage::helper('zolagovendorproduct')->__('Invalid'),
        );
        if($formatter) {
            unset($res[self::STATUS_DISABLED]);
        }
        return $res;
    }

    static public function getAllOptions($withEmpty = true, $defaultValues = false, $formatter = false)
    {
        if ($withEmpty) {
            $res = array(
                array(
                    'value' => '',
                    'label' => Mage::helper('catalog')->__('-- Please Select --')
                )
            );
        } else {
            $res = array();
        }
        foreach (self::getOptionArray($formatter) as $index => $value) {
            $res[] = array(
                'value' => $index,
                'label' => $value
            );
        }
        return $res;
    }

}