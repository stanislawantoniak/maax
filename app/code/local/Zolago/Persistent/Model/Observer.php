<?php

class Zolago_Persistent_Model_Observer extends Mage_Persistent_Model_Observer
{
	
	public function emulateQuote($observer)
    {
        $stopActions = array(
            'persistent_index_saveMethod',
            'customer_account_createpost'
        );

        if (!Mage::helper('persistent')->canProcess($observer)
            || !$this->_getPersistentHelper()->isPersistent() || Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }
		
        /** @var $action Mage_Checkout_OnepageController */
        $action = $observer->getEvent()->getControllerAction();
        $actionName = $action->getFullActionName();

        if (in_array($actionName, $stopActions)) {
            return;
        }
		

        /* @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');
        if ($this->_isShoppingCartPersist()) {
			$customer = $this->_getPersistentCustomer();
			// By setting this flag quote object knows should do not import 
			// personal data of customer
			$customer->setSkipCopyPersonalData(true);
            $checkoutSession->setCustomer($customer);
            if (!$checkoutSession->hasQuote()) {
                $checkoutSession->getQuote();
            }
			$customer->setSkipCopyPersonalData(false);
        }
    }
	
}
