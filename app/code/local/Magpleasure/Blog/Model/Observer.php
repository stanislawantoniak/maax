<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Model_Observer
{
    /**
     * Customer Session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _customerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function customerLoginAfter($event)
    {
        /** @var Mage_Customer_Model_Customer $customer  */
        $customer = $event->getCustomer();
        $sessionId = $this->_customerSession()->getSessionId();

        # Comments

        /** @var $comments Magpleasure_Blog_Model_Mysql4_Comment_Collection */
        $comments = Mage::getModel('mpblog/comment')->getCollection();
        $comments
            ->addFieldToFilter('session_id', $sessionId)
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->getSelect()
                ->where("main_table.customer_id IS NULL")
            ;

        foreach ($comments as $comment){
            $comment->setCustomerId($customer->getId())->save();
        }

        # Subscriptions

        /** @var $subscriptions Magpleasure_Blog_Model_Mysql4_Comment_Subscription_Collection */
        $subscriptions = Mage::getModel('mpblog/comment_subscription')->getCollection();
        $subscriptions
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('session_id', $sessionId)
            ->getSelect()
                ->where("main_table.customer_id IS NULL")
            ;

        foreach ($subscriptions as $subscription){
            $subscription->setCustomerId($customer->getId())->save();
        }

        # Views

        /** @var $views Magpleasure_Blog_Model_Mysql4_View_Collection */
        $views = Mage::getModel('mpblog/view')->getCollection();
        $views
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('session_id', $sessionId)
            ->getSelect()
                ->where("main_table.customer_id IS NULL")
            ;

        foreach ($views as $view){
            $view->setCustomerId($customer->getId())->save();
        }
    }

}