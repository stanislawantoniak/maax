<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Shippingpayment_Payment 
	extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{

    /**
     * Getter
     *
     * @return float
     */
    public function getQuoteBaseGrandTotal()
    {
        return (float)$this->getQuote()->getBaseGrandTotal();
    }

} 