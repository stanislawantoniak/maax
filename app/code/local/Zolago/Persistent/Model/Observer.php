<?php

class Zolago_Persistent_Model_Observer extends Mage_Persistent_Model_Observer
{
	/**
	 * @param Varien_Event_Observer $observer
	 * @return Zolago_Persistent_Model_Observer
	 */
	public function customerPrivacyChanged(Varien_Event_Observer $observer) {
		$customer = $observer->getEvent()->getCustomer();
		/* @var $customer Mage_Customer_Model_Customer */
		
		$currentPersistent = (int)$this->_getPersistentHelper()->isPersistent();
		$shouldPersistent = !(int)$customer->getForgetMe();
		
		// Customer value anf current persistance is the same - N/O
		if($shouldPersistent==$currentPersistent){
			return $this;
		}
		
		// synchronizePersistentOnLogin check current value of remember me 
		// and do right action (remeber/forget)
		Mage::getSingleton('persistent/observer_session')->
			synchronizePersistentOnLogin($observer);
		
		return $this;
	}
	
	/**
	 * Emulate quote override to set special flag that tells quote 
	 * to handle diffrent customer 
	 * 
	 * @param Varien_Event_Observer $observer
	 * @return Zolago_Persistent_Model_Observer
	 */
	public function emulateQuote($observer)
    {
        if (!Mage::helper('persistent')->canProcess($observer)
            || !$this->_getPersistentHelper()->isPersistent() 
			|| Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }
		
        /** @var $action Mage_Checkout_OnepageController */
        $action = $observer->getEvent()->getControllerAction();
        $actionName = $action->getFullActionName();

        $goActions = array(
            'orbacommon_ajax_customer_get_account_information'
        );

        if (!in_array($actionName, $goActions) && strpos($actionName,"checkout_") === false) {
            return;
        }
		Mage::log($actionName);

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
