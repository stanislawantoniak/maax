<?php

class GH_Dhl_Model_Source_Shipping extends Varien_Object
{
    const GH_SHIPPING_SOURCE_GALLERY = 1;
    const GH_SHIPPING_SOURCE_VENDOR = 0;

    /**
     * list of shipping sources
     *
     * @return array
     */

    public function toOptionHash()
    {
        $out = array(
            self::GH_SHIPPING_SOURCE_GALLERY => Mage::helper('ghstatements')->__('GALLERY'),
            self::GH_SHIPPING_SOURCE_VENDOR => Mage::helper('ghstatements')->__('PARTNER')
        );
        return $out;
    }
}