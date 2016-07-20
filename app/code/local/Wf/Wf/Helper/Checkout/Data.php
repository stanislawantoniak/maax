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
        $inpostCode = Mage::getModel("ghinpost/carrier")->getCarrierCode();
        switch ($deliveryType) {
            case $inpostCode: // Admin => System => Formy Dostawy => Tier Shipping => Delivery Types
                $carrierLogo = '<img class="checkout-logo"  src="' . Mage::getDesign()->getSkinUrl('images/inpost/checkout-logo.png') . '" />';
                break;
            case 'std': // carrier
                $carrierLogo = '<img class="checkout-logo"  src="' . Mage::getDesign()->getSkinUrl('images/dhl/checkout-logo.png') . '" />';
                break;
            default:
                //Truck icon
                $carrierLogo = '<figure class="truck"><i class="fa fa-truck fa-3x"></i></figure>';
        }

        return $carrierLogo;
    }
}