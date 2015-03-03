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
        if ($items = Mage::registry('persistent_quote_item')) {
            // overriding items
            $quote->removeAllItems();            
            $quote->save();
            foreach ($items as $item) {
                $newItem = clone $item;
                $quote->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $quote->addItem($newChild);
                    }
                }                                                                                                                                                                                                
            }
            $quote->collectTotals()
                 ->save();
                             
        } elseif ($quote->hasItems()) { // clear customer
            $customerQuote = Mage::getModel('sales/quote')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());
            if ($quote->getId() != $customerQuote->getId()) {
                $customerQuote->removeAllItems();
                $customerQuote->save();            
            }
        }
    }
}