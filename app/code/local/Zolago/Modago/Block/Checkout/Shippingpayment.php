<?php

class Zolago_Modago_Block_Checkout_Shippingpayment extends Zolago_Modago_Block_Checkout_Abstract
{
    protected $_rates;
    protected $_address;

    public function getSaveUrl()
    {
        return Mage::getUrl("checkout/onepage/saveAddress");
    }

} 