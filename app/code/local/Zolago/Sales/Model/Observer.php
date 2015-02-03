<?php


class Zolago_Sales_Model_Observer extends Mage_Sales_Model_Observer {
    
    /**
     * clear quote if new is not empty
     * @param 
     * @return 
     */

    public function loadCustomerQuoteBefore($observer) {
        $session = $observer->getEvent()->getCheckoutSession();
        $quote = $session->getQuote();        
        if ($quote->hasItems()) { // clear customer
            $customerQuote = Mage::getModel('sales/quote')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());
            $customerQuote->removeAllItems();
            $customerQuote->save();
        }
    }
}