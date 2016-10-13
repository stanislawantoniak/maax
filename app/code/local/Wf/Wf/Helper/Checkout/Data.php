<?php

/**
 * Class Wf_Wf_Helper_Data
 */
class Wf_Wf_Helper_Checkout_Data extends Zolago_Checkout_Helper_Data
{
    /**
     * Get carrier logo for checkout
     *
     * @param $deliveryType
     * @return string
     */
    public function getCarrierLogo($deliveryType)
    {

        $carrierLogo = parent::getCarrierLogo($deliveryType);
        switch ($deliveryType) {
            case Orba_Shipping_Model_Carrier_Default::CODE: // carrier
                $carrierLogo = '<img class="checkout-logo"  src="' . Mage::getDesign()->getSkinUrl('images/dhl/checkout-logo.png') . '" />';
                break;
            case Orba_Shipping_Model_Post::CODE: //zolagopp
                $carrierLogo = '<img class="checkout-logo"  src="' . Mage::getDesign()->getSkinUrl('images/poczta_polska/poczta_polska.png') . '" />';
                break;
        }

        return $carrierLogo;
    }
}