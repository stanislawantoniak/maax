<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Comment_Subscription extends Mage_Core_Model_Abstract
{
    const STATUS_SUBSCRIBED = 2;
    const STATUS_UNSUBSCRIBED = 3;

    protected $_post;

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * URL Instance
     *
     * @return Mage_Core_Model_Url
     */
    protected function _getUrlInstance()
    {
        return Mage::getSingleton('core/url');
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/comment_subscription');
    }

    public function getOptionsArray()
    {
        return array(
            self::STATUS_SUBSCRIBED => $this->_helper()->__("Subscribed"),
            self::STATUS_UNSUBSCRIBED => $this->_helper()->__("Unsubscribed"),
        );
    }

    public function toOptionArray()
    {
        $result = array();
        foreach ($this->getOptionsArray() as $value=>$label){
            $result[] = array('value'=>$value, 'label'=>$label);
        }
        return $result;
    }

    public function loadBySessionId($postId, $sessionId)
    {
        $this->getResource()->loadBySessionId($this, $postId, $sessionId);
        return $this;
    }

    public function loadByEmail($postId, $email)
    {
        $this->getResource()->loadByEmail($this, $postId, $email);
        return $this;
    }

    public function loadByCustomerId($postId, $customerId)
    {
        $this->getResource()->loadByCustomerId($this, $postId, $customerId);
        return $this;
    }

    public function generateHash()
    {
        if (!$this->getHash()){
            $hash = md5(microtime());
            $this->setHash($hash);
        } else {
            Mage::throwException($this->_helper()->__("Can't generate Subscription Hash twice."));
        }

        return $this;
    }

    public function notifyAboutComment(Magpleasure_Blog_Model_Comment $comment)
    {
        if ($comment->getEmail() != $this->getEmail()){

            /** @var $notification Magpleasure_Blog_Model_Comment_Notification */
            $notification = Mage::getModel('mpblog/comment_notification');

            $notification
                ->setPostId($this->getPostId())
                ->setSubscriptionId($this->getId())
                ->setCommentId($comment->getId())
                ->setStatus(Magpleasure_Blog_Model_Comment_Notification::STATUS_WAIT)
                ->setStoreId($this->getStoreId())
                ->save()
                ;

        }
        return $this;
    }

    /**
     * Retrieves unsubscribe URL
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return $this->_getUrlInstance()->getUrl('mpblog/subscription/unsubscribe', array('h'=> $this->getHash()));
    }

    public function mapCheckbox($value)
    {
        return $value ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED;
    }
}