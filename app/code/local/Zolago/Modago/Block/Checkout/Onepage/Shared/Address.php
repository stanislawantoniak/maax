<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Address
	extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{
	protected $_agreements;

	public function getStep1Sidebar()
    {
        return $this->getLayout()->createBlock("cms/block")->setBlockId("checkout-right-column-step-1")->toHtml();
    }

	public function getAgreements()
	{
		if (is_null($this->_agreements)) {
			$session =  Mage::getSingleton('checkout/session');
			$this->_agreements = $session->getAgreements();
		}
		return $this->_agreements;
	}

    public function getSaveUrl()
    {
        return Mage::getUrl("*/*/saveAddresses");
    }

    public function getPreviousStepUrl()
    {
        return Mage::getUrl("checkout/cart");
    }
} 