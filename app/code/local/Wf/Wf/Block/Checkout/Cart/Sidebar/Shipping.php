<?php

/**
 * Class Wf_Wf_Block_Checkout_Cart_Sidebar_Shipping
 */
class Wf_Wf_Block_Checkout_Cart_Sidebar_Shipping
    extends Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
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
                $carrierLogo = '<img class="checkout-logo"  src="' . $this->getSkinUrl('images/inpost/checkout-logo.png') . '" />';
                break;
            case 'std': // carrier
                $carrierLogo = '<img class="checkout-logo"  src="' . $this->getSkinUrl('images/dhl/checkout-logo.png') . '" />';
                break;
            default:
                //Truck icon
                $carrierLogo = '<figure class="logo-courier pull-right"><div class="shipment-icon"><i class="fa fa-truck fa-3x"></i></div></figure>';
        }

        return $carrierLogo;
    }

}