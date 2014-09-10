<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Address
	extends Mage_Checkout_Block_Onepage_Abstract
{
	public function getStep1Sidebar()
    {
        return $this->getLayout()->createBlock("cms/block")->setBlockId("checkout-right-column-step-1")->toHtml();
    }
} 