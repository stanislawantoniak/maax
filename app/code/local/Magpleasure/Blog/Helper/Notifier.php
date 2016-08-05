<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Notifier extends Mage_Core_Model_Abstract
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function notifyAdminAboutCommentAdded(Magpleasure_Blog_Model_Comment $comment)
    {
        $data = array();
        $data['post'] = $comment->getPost();
        $data['comment'] = $comment;
        $data['store'] = Mage::app()->getStore();
        $data['need_approval'] = !$this->_helper()->getCommentsAutoapprove();

        $storeId = Mage::app()->getStore()->getId();

        $template = Mage::getStoreConfig('mpblog/notify_admin_new_comment/email_template', $storeId);
        $sender = Mage::getStoreConfig('mpblog/notify_admin_new_comment/sender', $storeId);
        $receivers = explode(",", Mage::getStoreConfig('mpblog/notify_admin_new_comment/receiver', $storeId));

        foreach ($receivers as $receiver){
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
                        $this->_helper()->__('Administrator'),
                        $data,
                        $storeId
                    )
                    ;

                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return $this;
    }

    public function notifyAboutPostPublish(Magpleasure_Blog_Model_Post $post)
    {
        $storeId = $post->getDefaultStoreId();

        $data = array();
        $data['post'] = $post;
        $data['store'] = Mage::app()->getStore($storeId);

        $template = Mage::getStoreConfig('mpblog/notify_admin_scheduled_post/email_template', $storeId);
        $sender = Mage::getStoreConfig('mpblog/notify_admin_scheduled_post/sender', $storeId);
        $receivers = explode(",", Mage::getStoreConfig('mpblog/notify_admin_scheduled_post/receiver', $storeId));

        foreach ($receivers as $receiver){
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
                            $this->_helper()->__('Administrator'),
                            $data,
                            $storeId
                        )
                    ;

                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        return $this;
    }

}