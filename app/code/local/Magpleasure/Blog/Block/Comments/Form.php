<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Comments_Form extends Magpleasure_Blog_Block_Comments_Abstract
{
    protected $_collection;

    protected $_post;
    protected $_replyTo;
    protected $_formData = array();

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/comments/form.phtml");
    }

    public function setPost($value)
    {
        $this->_post = $value;
        return $this;
    }

    public function setReplyTo($value)
    {
        $this->_replyTo = $value;
    }

    public function canPostComments()
    {
        return $this->_helper()->getCommentsAllowGuests();
    }

    /**
     * Comment
     *
     * @return Magpleasure_Blog_Model_Comment
     */
    public function getReplyTo()
    {
        return $this->_replyTo ? $this->_replyTo->getId() : 0;
    }

    /**
     * Post
     * @return Magpleasure_Blog_Model_Post
     */
    public function getPost()
    {
        return $this->_post;
    }

    public function getPostId()
    {
        return $this->getPost()->getId();
    }

    public function isReply()
    {
        return !!$this->getReplyTo();
    }

    public function canPost()
    {
        return $this->_helper()->getCommentsAllowGuests() || $this->isLoggedId();
    }

    public function setFormData(array $data)
    {
        $this->_formData = $data;
    }

    public function getFormData()
    {
        return new Varien_Object($this->_formData);
    }

    public function getRegisterUrl()
    {
        return $this->getUrl('customer/account/create');
    }

    public function getLoginUrl()
    {
        $params = array('post_id' => $this->getPostId());
        if ($this->isReply()){
            $params['reply_to'] = $this->getReplyTo();
        }
        return $this->getUrl('*/*/login', $params);
    }

    public function isLoggedId()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    public function getCustomerId()
    {
        return $this->getCustomerSession()->getCustomerId();
    }

    public function getCustomerName()
    {
        return $this->isLoggedId() ? $this->getCustomerSession()->getCustomer()->getName() : $this->_helper()->loadCommentorName();
    }

    public function getCustomerEmail()
    {
        return $this->isLoggedId() ? $this->getCustomerSession()->getCustomer()->getEmail() : $this->_helper()->loadCommentorEmail();
    }

    public function getSessionId()
    {
        return $this->getData('session_id');
    }

    public function getMessageBlockHtml()
    {
        $block = $this->getMessagesBlock();
        if ($block){
            $block->setMessages($this->getCustomerSession()->getMessages(true));
        }
        return $block->toHtml();
    }

    public function getEmailsEnabled()
    {
        return $this->_helper()->getCommentNotificationsEnabled();
    }

    public function getReplyToCustomerName()
    {
        $comment = Mage::getModel('mpblog/comment')->load($this->getReplyTo());
        if ($comment->getId()){
            return $comment->getName();
        }

        return false;
    }

    public function isCustomerSubscribed()
    {
        $storedValue = $this->_helper()->loadIsSubscribed();
        if (!is_null($storedValue)){
            return $storedValue;
        }

        return true;
    }
}