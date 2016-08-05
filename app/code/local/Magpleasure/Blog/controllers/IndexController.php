<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Core
     *
     * @return Mage_Core_Helper_Data
     */
    public function _core()
    {
        return $this->_helper()->_core();
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

    /**
     * Response for Ajax Request
     *
     * @param array $result
     */
    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Zend_Json::encode($result))
        ;
    }

    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function postAction()
    {
        $postId = $this->getRequest()->getParam("id");
        if ($postId){

            $post = Mage::getModel("mpblog/post")->load($postId);
            if ($post->getId()){

                Mage::register('current_post', $post, true);

                /** @var $view Magpleasure_Blog_Model_View */
                $view = Mage::getModel('mpblog/view');
                /** @var $request Mage_Core_Controller_Request_Http */
                $request = $this->getRequest();
                $view->registerMe($request, $this->_getRefererUrl());

                $this->loadLayout()->renderLayout();
            } else {

                $this->_redirectUrl($this->_helper()->_url()->getUrl());
            }

        } else {
            $this->_redirectUrl($this->_helper()->_url()->getUrl());
        }
    }

    public function categoryAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function tagAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function archiveAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function searchAction()
    {
        if ($q = $this->getRequest()->getParam('query')){

            /** @var Magpleasure_Searchcore_Model_Query $query */
            $query = Mage::getModel('searchcore/query');
            $query = $query->getQueryByQ($q);
            Mage::register(Magpleasure_Blog_Model_Search::SEARCH_QUERY_KEY, $query, true);
        }

        $this->loadLayout()->renderLayout();
    }

    public function temporaryAction()
    {
        if ($url = $this->getRequest()->getParam('url')){
            $this->getResponse()->setRedirect($url, 302)->sendHeaders();
        }
    }

    public function redirectAction()
    {
        if ($url = $this->getRequest()->getParam('url')){
            $this->getResponse()->setRedirect($url, 301)->sendHeaders();
        }
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

    protected function _getMessageBlockHtml()
    {
        return $this->getLayout()->getMessagesBlock()->addMessages($this->_getCustomerSession()->getMessages(true))->toHtml();
    }

    public function formAction()
    {
        $this->loadLayout();

        $result = array();
        $error = false;

        $postId = $this->getRequest()->getParam('post_id');
        $sessionId = $this->getRequest()->getParam('session_id');
        if ($postId){
            $postId = $this->_core()->decrypt( $this->_core()->urlDecode($postId));

            $post = Mage::getModel('mpblog/post')->load($postId);
            if ($postId){
                $replyTo = $this->getRequest()->getParam('reply_to');

                if (!is_null($replyTo)){
                    $comment = Mage::getModel('mpblog/comment')->load($replyTo);
                }

                /** @var Magpleasure_Blog_Block_Comments_Form $form  */
                $form = $this->getLayout()->getBlock('mpblog.form');
                if ($form){

                    $form->setPost($post)->setSessionId($sessionId);
                    if ($comment->getId()){
                        $form->setReplyTo($comment);
                    }

                    $form->setSecureCode($this->_helper()->_secure()->getSecureCode($postId, $replyTo));
                    $result['form'] = $form->toHtml();
                }

            } else {
                $this->_getCustomerSession()->addError($this->_helper()->__("Post is not found."));
                $error = true;
            }
        }

        if ($error){
            $result['error'] = 1;
            $result['message'] = $this->_getMessageBlockHtml();
        }
        $this->_ajaxResponse($result);
    }

    public function postFormAction()
    {
        $result = array();
        $error = false;

        $postData = $this->getRequest()->getPost();
        $postData['store_id'] = Mage::app()->getStore()->getId();

        $post = new Varien_Object($postData);
        $replyTo = $post->getReplyTo();
        $postId = $this->getRequest()->getParam('post_id');
        if ($postId){
            $postId = $this->_core()->decrypt( $this->_core()->urlDecode($postId));

            $postInstance = Mage::getModel('mpblog/post')->load($postId);
            if ($postInstance->getId()){
                Mage::register('current_post', $postInstance);

                $secureCode = $post->getSecureCode();
                $post
                    ->setPostId($postId)
                    ->setNotified('0')
                    ;

                if ($this->_helper()->_secure()->validate($secureCode, $postId, $replyTo)){

                    # Save Subscription
                    $sessionId = $post->getSessionId();

                    # Subscription logic start
                    if ($this->_helper()->getCommentNotificationsEnabled()){


                        $isSubscribed = $post->getData('subscribe_to_replies');

                        /** @var $subscription Magpleasure_Blog_Model_Comment_Subscription */
                        $subscription = Mage::getModel('mpblog/comment_subscription');
                        $subscription->loadByEmail($postId, $post->getEmail());

                        if ($subscription->getId()){

                            if ($this->_getCustomerSession()->isLoggedIn()){
                                if ($subscription->getCustomerId() != $this->_getCustomerSession()->getCustomerId()){
                                    $subscription->setCustomerId($this->_getCustomerSession()->getCustomerId());
                                }
                            } else {
                                $subscription->setSessionId($sessionId);
                            }

                            $subscription
                                ->setStatus($subscription->mapCheckbox($isSubscribed))
                                ->save()
                            ;

                        } else {

                            $subscription
                                ->setPostId($postId)
                                ->setCustomerName($post->getName())
                                ->setEmail($post->getEmail())
                                ->setStatus($subscription->mapCheckbox($isSubscribed))
                                ->setStoreId(Mage::app()->getStore()->getId())
                                ->generateHash()
                            ;

                            if ($this->_getCustomerSession()->isLoggedIn()){
                                $subscription->setCustomerId($this->_getCustomerSession()->getCustomerId());
                            } else {
                                $subscription->setSessionId($sessionId);
                            }

                            $subscription->save();
                        }
                    }
                    # Subscription logic end

                    # Save commenter details
                    $this->_helper()
                        ->saveCommentorName($post->getName())
                        ->saveCommentorEmail($post->getEmail())
                        ->saveIsSubscribed($isSubscribed)
                        ;

                    $newComment = null;

                    if ($replyTo){
                        /** @var Magpleasure_Blog_Model_Comment $comment  */
                        $comment = Mage::getModel('mpblog/comment')->load($replyTo);
                        if ($comment->getId()){
                            $newComment = $comment->reply($post->getData());
                        }

                    } else {
                        $post->unsetData('reply_to');
                        /** @var Magpleasure_Blog_Model_Comment $comment  */
                        $comment = Mage::getModel('mpblog/comment');
                        $newComment = $comment->comment($post->getData());
                    }

                    if ($newComment){
                        /** @var Magpleasure_Blog_Block_Comments_Message $message */
                        $message = $this->getLayout()->createBlock('mpblog/comments_message');
                        if ($message){
                            $message->setMessage($newComment);
                            $message->setIsAjax(true);
                            $result['message'] = $message->toHtml();
                            $result['comment_id'] = $newComment->getId();
                            $result['count_code'] = $message->getCountCode();
                        }
                    } else {
                        $error = 1;
                        $this->_getCustomerSession()->addError($this->_helper()->__("Can not create comment."));
                    }

                } else {
                    $error = 1;
                    $this->_getCustomerSession()->addError($this->_helper()->__("Your session was expired. Please refresh this page and try again."));
                }

            } else {
                $this->_getCustomerSession()->addError($this->_helper()->__("Post is not found."));
                $error = 1;
            }
        }

        if ($error){
            $result['error'] = 1;

            /** @var Magpleasure_Blog_Block_Comments_Form $form  */
            $form = $this->getLayout()->createBlock('mpblog/comments_form');
            if ($form){
                $form->setPost($postInstance);
                if ($replyTo){
                    /** @var Magpleasure_Blog_Model_Comment $comment  */
                    $replyTo = Mage::getModel('mpblog/comment')->load($replyTo);
                    $form->setReplyTo($replyTo);
                }
                $form->setIsAjax(true);
                $form->setFormData($post->getData());
                $form->setSecureCode($this->_helper()->_secure()->getSecureCode($postId, $replyTo));
                $result['form'] = $form->toHtml();
            }
        }

        $this->_ajaxResponse($result);
    }

    public function loginAction()
    {
        $postId = $this->getRequest()->getParam('post_id');
        $replyToId = $this->getRequest()->getParam('reply_to');

        $url = Mage::helper('mpblog/url')->getUrl($postId, Magpleasure_Blog_Helper_Url::ROUTE_POST);

        if ($replyToId){
            $url .= "#reply-to-".$replyToId;
        } else {
            $url .= "#add-comment";
        }

        Mage::getSingleton('customer/session')->setBeforeAuthUrl($url);

        $this->_redirect('customer/account/login');
    }
}