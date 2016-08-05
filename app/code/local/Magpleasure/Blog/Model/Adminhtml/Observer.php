<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Adminhtml_Observer
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _isMessageInSession($messageText)
    {
        $messages = Mage::getSingleton('adminhtml/session')->getMessages();
        foreach ($messages->getItems() as $message){

            /** @var Mage_Core_Model_Message_Notice $message */
            if ($message->getCode() == $messageText){
                return true;
            }
        }

        return false;
    }

    public function preDispatch()
    {
        $commentsAllowed = Mage::getSingleton('admin/session')->isAllowed('cms/mpblog/comments');
        if ($commentsAllowed){



            $actionName = Mage::app()->getRequest()->getActionName();

            # Display notification on INDEX actions only.
            if ($actionName != 'index'){
                return $this;
            }

            $moduleName = Mage::app()->getRequest()->getModuleName();
            $controllerName = Mage::app()->getRequest()->getControllerName();

            if (($moduleName == 'adminhtml') && ($controllerName == 'mpblog_comment')){
                return $this;
            }

            # Looking for pending comments in the blog

            $storeIds = $this->_helper()->getCommon()->getStore()->getFrontendStoreIds();
            $comments = Mage::getModel('mpblog/comment')->getCollection();

            $comments
                ->addStatusFilter(Magpleasure_Blog_Model_Comment::STATUS_PENDING)
                ->addStoreFilter($storeIds)
            ;

            if ($qty = $comments->getSize()){

                $commentsUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/mpblog_comment/index');

                if ($qty == 1){
                    $message = $this->_helper()->__("You have %s pending comment in the blog - ", $qty);
                } else {
                    $message = $this->_helper()->__("You have %s pending comments in the blog - ", $qty);
                }

                $message .= "<a href=\"".$commentsUrl."\">";
                $message .= $this->_helper()->__("Fix that");
                $message .= "</a>";

                if (!$this->_isMessageInSession($message)){
                    Mage::getSingleton('adminhtml/session')->addNotice($message);
                }

                $this->_isMessageInSession($message);
            }
        }

        return $this;
    }

    public function generateBlockAfter($event)
    {
        $block = $event->getBlock();

        if ($block && ($block instanceof Mage_Adminhtml_Block_System_Account_Edit)) {


            /** @var $root Magpleasure_Common_Block_Adminhtml_Widget_Ajax_Form_Root */
            $root = Mage::app()->getLayout()->createBlock('magpleasure/adminhtml_widget_ajax_form_root');
            $root->createForm('mpblogAuthorInfoForm', array(
                'container' => 'mpblog/adminhtml_author_edit',
                'width' => '700',
                'height' => '500',
                'default_entity_id' => 1,
            ));

            try {
                $block->addButton('smart_button', array(
                    'label' => $this->_helper()->__("Blog Author Info"),
                    'title' => $this->_helper()->__("Default Author Info for Blog Pro"),
                    'onclick' => $root->getJsObjectName().'.open()',
                    'class' => 'go',
                    'after_html' => $root->toHtml(),
                ), null, -10000);
            } catch (Exception $e) {
                $this->_helper()->getCommon()->getException()->logException($e);
            }
        }
    }

}