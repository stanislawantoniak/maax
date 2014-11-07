<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Shippingpayment extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{
    protected $_rates;
    protected $_address;

    public function getSaveUrl()
    {
        return Mage::getUrl("*/*/saveShippingpayment");
    }
	
	public function getStep2Sidebar()
    {
        return $this->getLayout()->createBlock("cms/block")->
				setBlockId("checkout-right-column-step-2")->toHtml();
    }

} 