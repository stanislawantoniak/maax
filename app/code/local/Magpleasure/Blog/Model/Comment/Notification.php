<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Comment_Notification extends Magpleasure_Common_Model_Abstract
{
    const STATUS_WAIT = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_CANCELED = 3;
    const STATUS_FAILED = 4;

    protected $_subscription;
    protected $_comment;

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/comment_notification');
    }

    public function getOptionsArray()
    {
        return array(
            self::STATUS_WAIT => $this->_helper()->__("Wait"),
            self::STATUS_SUCCESS => $this->_helper()->__("Sent"),
            self::STATUS_CANCELED => $this->_helper()->__("Canceled"),
            self::STATUS_FAILED => $this->_helper()->__("Failed"),
        );
    }

    /**
     * Subscription
     *
     * @return Magpleasure_Blog_Model_Comment_Subscription
     */
    public function getSubscription()
    {
        if (!$this->_subscription){
            $this->_subscription = Mage::getModel('mpblog/comment_subscription')->load($this->getSubscriptionId());
        }
        return $this->_subscription;
    }

    /**
     * Comment
     *
     * @return Magpleasure_Blog_Model_Comment
     */
    public function getComment()
    {
        if (!$this->_comment){
            $this->_comment = Mage::getModel('mpblog/comment')->load($this->getCommentId());
        }
        return $this->_comment;
    }


    /**
     * Send Notification Email
     *
     * @return Magpleasure_Blog_Model_Comment_Notification
     */
    public function send($testEmail = false)
    {
        if ($this->_helper()->getCommentNotificationsEnabled() || !!$testEmail){

            $data = array();
            $data['post'] = $this->getComment()->getPost();
            $data['comment'] = $this->getComment();
            $data['subscription'] = $this->getSubscription();

            $storeId = $this->getStoreId();
            $data['store'] = Mage::app()->getStore($storeId);

            $template = Mage::getStoreConfig('mpblog/notify_customer_comment_replyed/email_template', $storeId);
            $sender = Mage::getStoreConfig('mpblog/notify_customer_comment_replyed/sender', $storeId);
            $receiver = $testEmail ? $testEmail : $this->getSubscription()->getEmail();

            if (trim($receiver)){
                /** @var Mage_Core_Model_Email_Template $mailTemplate  */
                $mailTemplate = Mage::getModel('core/email_template');
                try {
                    $mailTemplate
                        ->setDesignConfig(array('area' => 'frontend', 'store'=>$storeId))
                        ->sendTransactional(
                        $template,
                        $sender,
                        trim($receiver),
                        $this->getSubscription()->getCustomerName(),
                        $data,
                        $storeId
                    )
                    ;

                    if (!$testEmail){
                        $this->setStatus(self::STATUS_SUCCESS)->save();
                    }


                } catch (Exception $e) {
                    if (!$testEmail){
                        $this->setStatus(self::STATUS_FAILED)->save();
                    }

                    Mage::logException($e);
                }
            }
        }
        return $this;
    }

    public function canCancel()
    {
        return $this->getStatus() == self::STATUS_WAIT;
    }

    public function cancel()
    {
        if ($this->canCancel()){

            $this
                ->setStatus(self::STATUS_CANCELED)
                ->save()
            ;
        } else {

            Mage::throwException($this->_helper()->__("The notification can't be canceled."));
        }

        return $this;
    }

}