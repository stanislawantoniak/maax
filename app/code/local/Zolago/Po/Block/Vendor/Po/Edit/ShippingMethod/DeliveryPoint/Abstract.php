<?php

/**
 * Class Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod_DeliveryPoint_Abstract
 */
abstract class Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod_DeliveryPoint_Abstract
    extends Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod
{

    /**
     * @return array
     */
    public function getAvailableShippingMethodCodes()
    {
        return array_keys($this->getAvailableShippingMethods());
    }


    /**
     * @return bool
     */
    public function isShippingMethodAvailable()
    {
        return (bool)in_array($this->getShippingMethodRelationCode(), $this->getAvailableShippingMethodCodes());
    }
}
