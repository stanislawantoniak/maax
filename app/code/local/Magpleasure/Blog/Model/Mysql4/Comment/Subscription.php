<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Comment_Subscription extends Magpleasure_Common_Model_Resource_Abstract
{

    public function _construct()
    {    
        $this->_init('mpblog/comment_subscription', 'subscription_id');
        $this->setUseUpdateDatetimeHelper(true);
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function loadByEmail(Mage_Core_Model_Abstract $object, $postId, $email)
    {
        /** @var $collection Magpleasure_Blog_Model_Mysql4_Comment_Subscription_Collection */
        $collection = Mage::getModel('mpblog/comment_subscription')->getCollection();

        $collection
            ->addFieldToFilter('post_id', $postId)
            ->addFieldToFilter('email', $email)
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
        ;

        foreach ($collection as $item){
            $subscription = Mage::getModel('mpblog/comment_subscription')->load($item->getId());
            $object->addData($subscription->getData());
            return $this;
        }

        return $this;
    }

    public function loadBySessionId(Mage_Core_Model_Abstract $object, $postId, $sessionId)
    {
        /** @var $collection Magpleasure_Blog_Model_Mysql4_Comment_Subscription_Collection */
        $collection = Mage::getModel('mpblog/comment_subscription')->getCollection();

        $collection
            ->addFieldToFilter('post_id', $postId)
            ->addFieldToFilter('session_id', $sessionId)
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ;

        foreach ($collection as $item){
            $subscription = Mage::getModel('mpblog/comment_subscription')->load($item->getId());
            $object->addData($subscription->getData());
            return $this;
        }

        return $this;
    }


    public function loadByCustomerId(Mage_Core_Model_Abstract $object, $postId, $customerId)
    {
        /** @var $collection Magpleasure_Blog_Model_Mysql4_Comment_Subscription_Collection */
        $collection = Mage::getModel('mpblog/comment_subscription')->getCollection();

        $collection
            ->addFieldToFilter('post_id', $postId)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
        ;

        foreach ($collection as $item){
            $object = Mage::getModel('mpblog/comment_subscription')->load($item->getId());
            return $this;
        }

        return $this;
    }

}