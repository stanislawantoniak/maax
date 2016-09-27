<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Adminhtml_Mpblog_CommentController extends Magpleasure_Blog_Controller_Adminhtml_Filterable
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Initialize layout prefer any action
     * @return Magpleasure_Activecontent_Admin_BlockController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/mpblog/comments')
            ->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
        return $this;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $aclRoute = 'cms/mpblog/comments';
        $this
            ->_getSession()
            ->setControlRoutePath($aclRoute)
        ;
        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    public function indexAction()
    {
        $this->_prepareStoreFilter();

        $this->_initAction()
            ->renderLayout();
    }

    public function replyAction()
    {
        $id = $this->getRequest()->getParam('id_to_answer');
        $comment = Mage::getModel('mpblog/comment');
        if ($id){
            $comment->load($id);
        }

        $this->loadLayout();
        $this->_setActiveMenu('cms/mpblog/comments');

        if ($comment->getId()){
            Mage::register('comment_for_answer', $comment);


            $this->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
            $this->_addBreadcrumb($this->_helper()->__('Comment'), $this->_helper()->__('Comment'));

        } else {
            $this->_getSession()->addError($this->_helper()->__("Comment isn't exists."));
            $this->_redirect('*/*/index', $this->_getCommonParams());
        }

        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $comment = Mage::getModel('mpblog/comment');
        if ($id){
            $comment->load($id);
        }

        $this->loadLayout();
        $this->_setActiveMenu('cms/mpblog/comments');

        if ($comment->getId()){

            Mage::register('current_comment', $comment);
            $data = Mage::getSingleton('adminhtml/session')->getCommentData(true);
            if (!empty($data)) {
                $comment->setData($data);
            }

            $this->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
            $this->_addBreadcrumb($this->_helper()->__('Comment'), $this->_helper()->__('Comment'));

        } else {
            $this->_getSession()->addError($this->_helper()->__("Comment isn't exists."));
            $this->_redirect('*/*/index', $this->_getCommonParams());
        }

        $this->renderLayout();
    }

    public function saveAction()
    {
        $requestPost = $this->getRequest()->getPost();
        /** @var Magpleasure_Blog_Model_Comment $comment  */
        $comment = Mage::getModel('mpblog/comment');
        if ($id = $this->getRequest()->getParam('id')){
            $comment->load($id);
        }

        try {
            $comment->addData($requestPost);
            $comment->save();
            $this->_getSession()->addSuccess($this->_helper()->__("Comment was successfully saved."));


            if ($this->getRequest()->getParam('back')){
                $params = $this->_getCommonParams();
                $params['id'] = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : $comment->getId();

                $this->_redirect('*/*/edit', $params);
            } else {
                $this->_redirect('*/*/index', $this->_getCommonParams());
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->setPostData($requestPost);
            $this->_getSession()->addError($this->_helper()->__("Error while saving the comment. %s", $e->getMessage()));
            $this->_redirectReferer();
        }

    }

    /**
     * Delete comment
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id)
    {
        $comment = Mage::getModel('mpblog/comment')->load($id);
        if ($comment->getId()){
            try{
                $comment->delete();
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Approve comment
     * @param int|string $id
     * @return boolean
     */
    protected function _approve($id)
    {
        $comment = Mage::getModel('mpblog/comment')->load($id);
        if ($comment->getId()){
            try{
                $comment->approve();
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Reject comment
     * @param int|string $id
     * @return boolean
     */
    protected function _reject($id)
    {
        $comment = Mage::getModel('mpblog/comment')->load($id);
        if ($comment->getId()){
            try{
                $comment->reject();
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    protected function _updateStatus($id, $status)
    {
        if ($id){
            try {
                $comment = Mage::getModel('mpblog/comment')->load($id);
                $comment->setStatus($status);
                $comment->save();
                return true;
            } catch (Exception $e){
                return false;
            }
        }
    }

    public function massStatusAction()
    {
        $comments = $this->getRequest()->getPost('comments');
        $status = $this->getRequest()->getPost('status');
        if ($comments){
            $success = 0;
            $error = 0;
            foreach ($comments as $commentId){
                if ($this->_updateStatus($commentId, $status)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s comments were successfully updated.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s comments weren't updated.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $comments = $this->getRequest()->getPost('comments');
        if ($comments){
            $success = 0;
            $error = 0;
            foreach ($comments as $commentId){
                if ($this->_delete($commentId)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s comments were successfully deleted.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s comments weren't deleted.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function approveAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $this->_approve($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Comment was successfully approved."));
            } catch (Exception $e){
                $this->_getSession()->addError($this->_helper()->__("Comment wasn't approved. %s", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
        $this->_redirect('*/*/index', $this->_getCommonParams());
    }

    public function rejectAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $this->_reject($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Comment was successfully rejected."));
            } catch (Exception $e){
                $this->_getSession()->addError($this->_helper()->__("Comment wasn't rejected. %s", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
        $this->_redirect('*/*/index', $this->_getCommonParams());
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $this->_delete($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Comment was successfully deleted."));
            } catch (Exception $e){
                $this->_getSession()->addError($this->_helper()->__("Comment wasn't deleted. %s", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
        $this->_redirect('*/*/index', $this->_getCommonParams());
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }


}