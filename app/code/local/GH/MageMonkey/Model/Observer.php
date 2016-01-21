<?php

/**
 * Class GH_MageMonkey_Model_Observer
 */
class GH_MageMonkey_Model_Observer extends Ebizmarts_MageMonkey_Model_Observer
{
    /**
     * Handle Subscriber object saving process
     *
     * @param Varien_Event_Observer $observer
     * @return void|Varien_Event_Observer
     */
    public function handleSubscriber(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('monkey')->canMonkey()) {
            return $observer;
        }

        if (TRUE === Mage::helper('monkey')->isWebhookRequest()) {
            return $observer;
        }

        $subscriber = $observer->getEvent()->getSubscriber();

        $defaultList = Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_LIST, $subscriber->getStoreId());

        if ($subscriber->getOrigData('subscriber_status') != 3 && $subscriber->getData('subscriber_status') == 3) {
            Mage::getSingleton('monkey/api', array('store' => $subscriber->getStoreId()))->listUnsubscribe($defaultList, $subscriber->getSubscriberEmail());
        }

        if ($subscriber->getBulksync()) {

            return $observer;
        }

        if (
            (Mage::getSingleton('core/session')->getIsOneStepCheckout() || Mage::getSingleton('core/session')->getMonkeyCheckout())
            && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CHECKOUT_SUBSCRIBE, $subscriber->getStoreId())
        ) {
            return $observer;
        }
        if (
            Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $subscriber->getStoreId())
            //&& Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $subscriber->getStoreId())
            && !Mage::getSingleton('customer/session')->isLoggedIn() && Mage::app()->getRequest()->getActionName() != 'createpost'
        ) {

            return $observer;
        }

        if (Mage::getSingleton('core/session')->getIsOneStepCheckout() && !Mage::getSingleton('core/session')->getMonkeyCheckout()) {

            return $observer;
        }
        if (TRUE === $subscriber->getIsStatusChanged()) {

            Mage::getSingleton('core/session')->setIsHandleSubscriber(TRUE);
            if (Mage::getSingleton('core/session')->getIsOneStepCheckout() || Mage::getSingleton('core/session')->getMonkeyCheckout()) {

                $saveOnDb = Mage::helper('monkey')->config('checkout_async');
                Mage::helper('monkey')->subscribeToList($subscriber, $saveOnDb);
            } else {
                $post = Mage::app()->getRequest()->getPost();
                if (isset($post['email']) || isset($post['magemonkey_subscribe']) && $post['magemonkey_subscribe'] || Mage::getSingleton('core/session')->getIsUpdateCustomer() || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    Mage::helper('monkey')->subscribeToList($subscriber, 0);
                }
            }

            Mage::getSingleton('core/session')->setIsHandleSubscriber(FALSE);
        }

        return $observer;
    }
}