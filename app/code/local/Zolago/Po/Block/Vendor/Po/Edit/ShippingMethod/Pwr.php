<?php

/**
 * Class Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod_Pwr
 */
class Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod_Pwr
    extends Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod_DeliveryPoint_Abstract
    implements Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod_DeliveryPoint_interface
{

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        if ($this->isShippingMethodAvailable())
            Mage::app()->getLayout()
                ->getBlock('head')
                ->addItem("skin_js", 'js/po/edit/shipping_method/pwr/address.js');

        $this->setTemplate('zolagopo/vendor/po/edit/shipping_method/zospwr.phtml');


    }


    public function getShippingMethodRelationCode()
    {
        return Orba_Shipping_Model_Packstation_Pwr::CODE;
    }

}
