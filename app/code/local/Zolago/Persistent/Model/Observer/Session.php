<?php

class Zolago_Persistent_Model_Observer_Session extends Mage_Persistent_Model_Observer_Session
{
    
    /**
     * Create/Update and Load session when customer log in
     *
     * @param Varien_Event_Observer $observer
     */
    public function synchronizePersistentOnLogin(Varien_Event_Observer $observer)
    {
		
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
		
		if($customer instanceof Mage_Customer_Model_Customer && $customer->getId()){
			
			// @todo Add from customer attribute forget_me
			$rememberMeCheckbox = 0; // !(int)$custmer->getForgetMe()

			// Override request param setting saved in session by customer account setting
			Mage::helper('persistent/session')->setRememberMeChecked((bool)$rememberMeCheckbox);
		}
		
        return parent::synchronizePersistentOnLogin($observer);
    }

}
