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
            case 'std': // carrier
                $carrierLogo = '<img class="checkout-logo"  src="' . Mage::getDesign()->getSkinUrl('images/dhl/checkout-logo.png') . '" />';
                break;
        }

        return $carrierLogo;
    }
}