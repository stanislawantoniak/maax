<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_SubscriptionController extends Mage_Core_Controller_Front_Action
{
    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Customer Session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function unsubscribeAction()
    {
        if ($hash = $this->getRequest()->getParam('h')){

            /** @var $subscription Magpleasure_Blog_Model_Comment_Subscription */
            $subscription = Mage::getModel('mpblog/comment_subscription');

            $subscription->load($hash, 'hash');
            if ($subscription->getId()){

                $subscription->setStatus(Magpleasure_Blog_Model_Comment_Subscription::STATUS_UNSUBSCRIBED)->save();
                $this->_getCustomerSession()->addSuccess(
                    $this->__("Email %s was successfully unsubscribed for the post comments.", $subscription->getEmail())
                );
                $this->_redirectUrl(
                    $this->_helper()->_url()->getUrl($subscription->getPostId(), Magpleasure_Blog_Helper_Url::ROUTE_POST)
                );
                return ;
            }
        }

        $this->_getCoreSession()->addError($this->__("Can not find your subscription."));
        $this->_redirectUrl(Mage::getBaseUrl());
        return ;
    }
}