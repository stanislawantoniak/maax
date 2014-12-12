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

	public function isCustomerSubscribed() {
		if(Mage::getSingleton('customer/session')->isLoggedIn()){
			$email = Mage::getSingleton('customer/session')->getCustomer()->getData('email');
			$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
			if($subscriber->getId())
			{
				return $subscriber->getData('subscriber_status') == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
			}
		}
		return false;
	}
} 