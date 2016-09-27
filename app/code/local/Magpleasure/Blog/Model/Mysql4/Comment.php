<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Comment extends Magpleasure_Common_Model_Resource_Abstract
{
    /**
     * Zend_Date date format for Mysql requests
     */
    const MYSQL_ZEND_DATE_FORMAT = 'yyyy-MM-dd HH:mm:ss';

    protected $_needToNotifyAdmin = false;
    protected $_needToNotifyCustomer = false;

    public function _construct()
    {    
        $this->_init('mpblog/comment', 'comment_id');
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

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()){
            if (Mage::app()->getLayout()->getArea() == 'frontend'){
                $this->_needToNotifyAdmin = true;
            }
        }

        if ($this->_helper()->getCommentNotificationsEnabled()){
            if (($object->getStatus() == Magpleasure_Blog_Model_Comment::STATUS_APPROVED) && !$object->getNotified()){
                $object->setNotified(1);
                $this->_needToNotifyCustomer = true;
            }
        }

        parent::_beforeSave($object);
    }


    protected function _prepareCache(Mage_Core_Model_Abstract $object)
    {
        # Clean cache for Posts and Routes
        $this->_helper()->getCommon()->getCache()->cleanCachedData(
            array(
                Magpleasure_Blog_Model_Comment::CACHE_TAG,
            )
        );

        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        # Clean Comments related cache
        $this->_prepareCache($object);

        if ($this->_needToNotifyAdmin){
            $this->_helper()->_notifier()->notifyAdminAboutCommentAdded($object);
        }

        if ($this->_needToNotifyCustomer){

            # All comments subscriptions

            /** @var $subscriptions Magpleasure_Blog_Model_Mysql4_Comment_Subscription_Collection */
            $subscriptions = Mage::getModel('mpblog/comment_subscription')->getCollection();

            $subscriptions
                ->addFieldToFilter('post_id', $object->getPostId())
                ->addFieldToFilter('email', array('neq' => $object->getEmail()))
                ->addFieldToFilter('store_id', $object->getStoreId())
                ->addFieldToFilter('status', Magpleasure_Blog_Model_Comment_Subscription::STATUS_SUBSCRIBED)
                ->getSelect()
                ->group('email')
            ;

            foreach ($subscriptions as $subscription){
                /** @var $subscription Magpleasure_Blog_Model_Comment_Subscription */
                $subscription->notifyAboutComment($object);
            }

        }

        return  $this;
    }

    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        parent::_afterDelete($object);
        $this->_prepareCache($object);

        return $this;
    }
}