<?php

class Zolago_Modago_Block_Checkout_Onepage_Logged_Addressbook
	extends Zolago_Modago_Block_Checkout_Onepage_Shared_Address
{
	protected $_agreements;

	public function getAgreements()
	{
		if (is_null($this->_agreements)) {
			$session =  Mage::getSingleton('checkout/session');
			$this->_agreements = $session->getAgreements();
		}
		return $this->_agreements;
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