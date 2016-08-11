<?php

class Zolago_Persistent_Model_Observer_Session extends Mage_Persistent_Model_Observer_Session
{
    
    /**
     * Override remeber me desicison by customer preference attribute
     * @param Varien_Event_Observer $observer
     */
    public function synchronizePersistentOnLogin(Varien_Event_Observer $observer)
    {
		
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
		
		// Todo add skiping options if needed
		if($customer instanceof Mage_Customer_Model_Customer && $customer->getId()){
			
			// @todo Add from customer attribute forget_me
			$rememberMeCheckbox = !(int)$customer->getForgetMe();

			// Override request param setting saved in session by customer account setting
			Mage::helper('persistent/session')->setRememberMeChecked((bool)$rememberMeCheckbox);
		}
		
        parent::synchronizePersistentOnLogin($observer);
		Mage::dispatchEvent('persistent_synchronize_on_login_after',
			array('session' => Mage::helper('persistent/session')->getSession()));
    }

}
