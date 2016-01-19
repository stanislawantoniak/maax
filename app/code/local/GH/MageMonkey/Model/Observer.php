<?php

class GH_MageMonkey_Model_Observer extends Ebizmarts_MageMonkey_Model_Observer {
    /**
     * Handle Subscriber object saving process
     *
     * @param Varien_Event_Observer $observer
     * @return void|Varien_Event_Observer
     */
    public function handleSubscriber(Varien_Event_Observer $observer)
    {
        Mage::log("GH_MageMonkey_Model_Observer 1",null,"handleSubscriber.log");
        if (!Mage::helper('monkey')->canMonkey()) {
            Mage::log("GH_MageMonkey_Model_Observer 2",null,"handleSubscriber.log");
            return $observer;
        }
        Mage::log("GH_MageMonkey_Model_Observer 3",null,"handleSubscriber.log");
        if (TRUE === Mage::helper('monkey')->isWebhookRequest()) {
            Mage::log("GH_MageMonkey_Model_Observer 4",null,"handleSubscriber.log");
            return $observer;
        }
        Mage::log("GH_MageMonkey_Model_Observer 4",null,"handleSubscriber.log");
        $subscriber = $observer->getEvent()->getSubscriber();
        Mage::log($subscriber->getData(),null,"handleSubscriber.log");
        Mage::log("GH_MageMonkey_Model_Observer 5",null,"handleSubscriber.log");
        $defaultList = Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_LIST, $subscriber->getStoreId());
        Mage::log($defaultList,null,"handleSubscriber.log");
        if($subscriber->getOrigData('subscriber_status') != 3 && $subscriber->getData('subscriber_status') == 3){
            Mage::log("GH_MageMonkey_Model_Observer 6",null,"handleSubscriber.log");
            Mage::getSingleton('monkey/api', array('store' => $subscriber->getStoreId()))->listUnsubscribe($defaultList, $subscriber->getSubscriberEmail());
            Mage::log("GH_MageMonkey_Model_Observer 6",null,"handleSubscriber.log");
        }
        Mage::log("GH_MageMonkey_Model_Observer 7",null,"handleSubscriber.log");
        if ($subscriber->getBulksync()) {
            Mage::log("GH_MageMonkey_Model_Observer 8",null,"handleSubscriber.log");
            return $observer;
        }
        Mage::log("GH_MageMonkey_Model_Observer 9",null,"handleSubscriber.log");
        if(
            (Mage::getSingleton('core/session')->getIsOneStepCheckout() || Mage::getSingleton('core/session')->getMonkeyCheckout())
            && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CHECKOUT_SUBSCRIBE, $subscriber->getStoreId())
        )
        {
            Mage::log("GH_MageMonkey_Model_Observer 10",null,"handleSubscriber.log");
            return $observer;
        }
        if(
            Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $subscriber->getStoreId())
            //&& Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $subscriber->getStoreId())
            && !Mage::getSingleton('customer/session')->isLoggedIn() && Mage::app()->getRequest()->getActionName() != 'createpost'
        ){
            Mage::log("GH_MageMonkey_Model_Observer 11",null,"handleSubscriber.log");
            return $observer;
        }

        if (Mage::getSingleton('core/session')->getIsOneStepCheckout() && !Mage::getSingleton('core/session')->getMonkeyCheckout()) {
            Mage::log("GH_MageMonkey_Model_Observer 12",null,"handleSubscriber.log");
            return $observer;
        }
        if (TRUE === $subscriber->getIsStatusChanged()) {
            Mage::log("GH_MageMonkey_Model_Observer 13",null,"handleSubscriber.log");
            Mage::getSingleton('core/session')->setIsHandleSubscriber(TRUE);
            if (Mage::getSingleton('core/session')->getIsOneStepCheckout() || Mage::getSingleton('core/session')->getMonkeyCheckout()) {
                Mage::log("GH_MageMonkey_Model_Observer 14",null,"handleSubscriber.log");
                $saveOnDb = Mage::helper('monkey')->config('checkout_async');
                Mage::helper('monkey')->subscribeToList($subscriber, $saveOnDb);
            } else {
                Mage::log("GH_MageMonkey_Model_Observer 15",null,"handleSubscriber.log");
                $post = Mage::app()->getRequest()->getPost();
                if (isset($post['email']) || isset($post['magemonkey_subscribe']) && $post['magemonkey_subscribe'] || Mage::getSingleton('core/session')->getIsUpdateCustomer() || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    Mage::helper('monkey')->subscribeToList($subscriber, 0);
                }
            }
            Mage::log("GH_MageMonkey_Model_Observer 16",null,"handleSubscriber.log");
            Mage::getSingleton('core/session')->setIsHandleSubscriber(FALSE);
        }
        Mage::log("GH_MageMonkey_Model_Observer 17",null,"handleSubscriber.log");
        return $observer;
    }
}