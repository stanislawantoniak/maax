<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Adminhtml_Mpblog_DraftController extends Magpleasure_Blog_Controller_Adminhtml_Filterable
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
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $aclRoute = 'cms/mpblog/posts';
        $this
            ->_getSession()
            ->setControlRoutePath($aclRoute)
        ;
        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    /**
     * Initialize layout prefer any action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/mpblog/posts')
            ->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
        return $this;
    }


    public function indexAction()
    {
        $this->_prepareStoreFilter();

        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Delete slide
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id)
    {
        $post = Mage::getModel('mpblog/post')->load($id);
        if ($post->getId()) {
            try {
                $post->delete();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Duplicate form
     * @param int|string $id
     * @return boolean
     */
    protected function _duplicate($id)
    {
        /** @var Magpleasure_Blog_Model_Post $post */
        $post = Mage::getModel('mpblog/post')->load($id);
        if ($post->getId()) {
            try {
                $newPost = $post->duplicate();
                return $newPost;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }


    protected function _updateStatus($id, $status)
    {
        if ($id) {
            try {
                $post = Mage::getModel('mpblog/post')->load($id);
                $post->setStatus($status);
                $post->save();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    public function massCancelAction()
    {
        $posts = $this->getRequest()->getPost('posts');
        if ($posts) {
            $success = 0;
            $error = 0;
            foreach ($posts as $postId) {
                if ($this->_delete($postId)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_getSession()->addSuccess($this->_helper()->__("%s drafts successfully canceled.", $success));
            }
            if ($error) {
                $this->_getSession()->addError($this->_helper()->__("%s drafts was not canceled.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function cancelAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_delete($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Post was successfully canceled."));
            } catch (Exception $e) {
                $this->_getSession()->addError($this->_helper()->__("Post was not canceled (%s).", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
        $params = $this->_getCommonParams();
        $this->_redirect('*/*/index', $params);
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}