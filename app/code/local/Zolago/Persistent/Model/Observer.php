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
     * create new quest quote and merge items from old
     */
	protected function _clearPersistent() {
	    // user not logged in
	    $checkoutSession = Mage::getSingleton('checkout/session');
		/** @var Zolago_Sales_Model_Quote $oldQuote */
	    $oldQuote = $checkoutSession->getQuote();

		$shippingPointCode = $checkoutSession->getData("delivery_point_name");

		$address = $oldQuote->getShippingAddress();
		$details = $address->getUdropshipShippingDetails();
		$details = $details ? Zend_Json::decode($details) : array();


	    $session = Mage::helper('persistent/session')->getSession();
	    $session->removePersistentCookie();
	    $checkoutSession->unsetAll();
	    $oldQuote
	        ->setCustomerId(null)
	        ->setCustomerEmail(null)
	        ->setCustomerFirstname(null)
	        ->setCustomerLastname(null)
	        ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
	        ->setIsPersistent(false);
		/** @var Zolago_Sales_Model_Quote $newQuote */
        $newQuote = Mage::getModel('sales/quote');
        $newQuote->merge($oldQuote);

        $newQuote
	        ->setStoreId(Mage::app()->getStore()->getId())
            ->setIsActive(true)
            ->setIsPersistent(false)
            ->collectTotals()
            ->save();
	    $newId = $newQuote->getId();
	    $checkoutSession->setQuoteId($newId);

		/*InPost should not be lost after persistent->guest checkout*/
		$checkoutSession->setData("delivery_point_name",$shippingPointCode);

		$shippingMethodPerVendor = array();
		if(isset($details["methods"])){
			foreach($details["methods"] as $_vendorId => $vendorRate){
				$shippingMethodPerVendor[$_vendorId] = $vendorRate["code"];
			}
			$checkoutSession->setShippingMethod($shippingMethodPerVendor);
		}


	    $quote = $checkoutSession->getQuote();

		$addressNew = $quote->getShippingAddress();
		$addressNew->setUdropshipShippingDetails(Zend_Json::encode($details));
		$addressNew->setDeliveryPointName($shippingPointCode);
		$quote->setTotalsCollectedFlag(false)->collectTotals()->save();

		/*InPost should not be lost after persistent->guest checkout*/

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
		
        /* @var $action Mage_Checkout_OnepageController */
        $action = $observer->getEvent()->getControllerAction();
        $actionName = $action->getFullActionName();

        $goActions = array(
            'orbacommon_ajax_customer_get_account_information'
        );
		
		$goModules = array(
			"persistent",
			"checkout"
		);

        if (!in_array($actionName, $goActions) && !in_array($action->getRequest()->getModuleName(), $goModules)) {
            return;
        }
        // clear persistent if guest
        if (!Mage::helper('persistent')->canProcess($observer)
            || !$this->_getPersistentHelper()->isPersistent() 
			|| Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }
        if($actionName == 'checkout_guest_continue') {
            $this->_clearPersistent();
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
    /**
     * Reset session data when customer re-authenticates
     * save old quote items
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerAuthenticatedEvent($observer)
    {
        /** @var $customerSession Mage_Customer_Model_Session */
        $customerSession = Mage::getSingleton('customer/session');
        $customerSession->setCustomerId(null)->setCustomerGroupId(null);

        if (Mage::app()->getRequest()->getParam('context') != 'checkout') {            
             $checkoutSession = Mage::getSingleton('checkout/session');

            $quote = $checkoutSession->setLoadInactive()->getQuote();
            if ($quote->getIsActive() && $quote->getCustomerId() && $quote->hasItems()) {
                $quoteItems = $quote->getAllVisibleItems();
                Mage::register('persistent_quote_item',$quoteItems);
            }
            $this->_expirePersistentSession();
            return;
        }

        $this->setQuoteGuest();
    }
	
}
