<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Address_Shipping
	extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{
    public function getOrderSomeoneElseFlag()
    {
        $quote = $this->getQuote();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();
        $flag = false;
		
		// If address is not filled - not inited - same address data
		if($shipping->getFirstname()===null && 
		   $shipping->getLastname()===null && 
		   $shipping->getTelephone()===null){
			return false;
		}
		
        if ($quote->getCustomerFirstname() !== $shipping->getFirstname()
            || $quote->getCustomerLastname() !== $shipping->getLastname()
            || $billing->getTelephone() !== $shipping->getTelephone()) {
            $flag = true;
        }

        return $flag;
	}
	
} 