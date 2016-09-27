<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Comments_Message extends Magpleasure_Blog_Block_Comments_Abstract
{
    protected $_collection;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/comments/list/message.phtml");
    }

    /**
     * Comment
     *
     * @return Magpleasure_Blog_Model_Comment
     */
    public function getMessage()
    {
        return $this->getData('message');
    }

    public function getContent()
    {
        return $this
            ->_helper()
            ->_render()
            ->render(
                $this->getMessage()->getMessage()
            );
    }

    public function getMessageId()
    {
        return $this->getMessage()->getId();
    }

    public function getAuthor()
    {
        return $this->getMessage()->getName();
    }

    public function getDate()
    {
        return $this->_helper()->_date()->renderDate($this->getMessage()->getCreatedAt());
    }

    public function getTime()
    {
        return $this->_helper()->_date()->renderTime($this->getMessage()->getCreatedAt());
    }

    public function getRepliesCollection()
    {
        if (!$this->_collection){
            /** @var Magpleasure_Blog_Model_Mysql4_Comment_Collection $comments  */
            $comments = Mage::getModel('mpblog/comment')->getCollection();

            if (!Mage::app()->isSingleStoreMode()){
                $comments->addStoreFilter(Mage::app()->getStore()->getId());
            }

            $comments
                ->addActiveFilter($this->_helper()->getCommentsAutoapprove() ? null : $this->getCustomerSession()->getSessionId() )
            ;

            $comments
                ->setDateOrder(Varien_Db_Select::SQL_ASC)
                ->setReplyToFilter($this->getMessage()->getId())
            ;

            $this->_collection = $comments;
        }
        return $this->_collection;
    }

    /**
     * Replies Html
     *
     * @return string
     */
    public function getRepliesHtml()
    {
        $html = "";
        foreach ($this->getRepliesCollection() as $message){
            $messageBlock = $this->getLayout()->createBlock('mpblog/comments_message');
            if ($messageBlock){
                $messageBlock->setMessage($message);
                $html .= $messageBlock->toHtml();
            }
        }
        return $html;
    }

    public function isReply()
    {
        if ($this->getIsAjax()){
            return false;
        }
        if ($this->getMessage()->getReplyTo()){
            $flag = 'mpblog_reply_'.$this->getMessage()->getReplyTo();
            if (!Mage::registry($flag)){
                Mage::register($flag, true);
                return true;
            }
        }
        return false;
    }

    public function getCountCode()
    {
        return $this->getCommentsCount() ? $this->__("%s comments", $this->getCommentsCount()) : $this->__("No comments");
    }

    public function getNeedApproveMessage()
    {
        return ($this->getMessage()->getStatus() == Magpleasure_Blog_Model_Comment::STATUS_PENDING);
    }

    public function isMyComment()
    {
        if ($this->getMessage()){

            $message = $this->getMessage();
            if ($this->getCustomerSession()->isLoggedIn()){
                $result = $this->getCustomerSession()->getCustomerId() == $message->getCustomerId();
            } else {
                $result = $this->getCustomerSession()->getSessionId() == $message->getSessionId();
            }

            return $this->getIsAjax() || $result;
        }
    }
}