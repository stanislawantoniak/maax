<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Adminhtml_Mpblog_TagController extends Magpleasure_Blog_Controller_Adminhtml_Filterable
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
        $aclRoute = 'cms/mpblog/tags';
        $this
            ->_getSession()
            ->setControlRoutePath($aclRoute)
        ;
        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/mpblog/tags')
            ->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Tag'));
        return $this;
    }

    public function indexAction()
    {
        $this->_prepareStoreFilter();

        $this->_initAction()
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $post = Mage::getModel('mpblog/tag');
        if ($id){
            $post->load($id);
        }

        $this->loadLayout();
        $this->_setActiveMenu('cms/mpblog/tags');

        if ($post->getId()){

            Mage::register('current_tag', $post);
            $data = Mage::getSingleton('adminhtml/session')->getPostData(true);
            if (!empty($data)) {
                $post->setData($data);
            }

            $this->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
            $this->_addBreadcrumb($this->_helper()->__('Tag'), $this->_helper()->__('Tag'));

        } else {
            $this->_getSession()->addError($this->_helper()->__("Tag isn't exists."));


            $this->_redirect('*/*/index', $this->_getCommonParams());
        }

        $this->renderLayout();
    }

    public function saveAction()
    {
        $requestPost = $this->getRequest()->getPost();

        /** @var Magpleasure_Blog_Model_Post $tag  */
        $tag = Mage::getModel('mpblog/tag');
        if ($id = $this->getRequest()->getParam('id')){
            $tag->load($id);
        }

        try {
            $tag->addData($requestPost);
            $tag->save();
            $this->_getSession()->addSuccess($this->_helper()->__("Tag was successfully saved."));

            if ($this->getRequest()->getParam('back')){

                $params = $this->_getCommonParams();
                $params['id'] = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : $tag->getId();
                $this->_redirect('*/*/edit', $params);
            } else {
                $this->_redirect('*/*/index', $this->_getCommonParams());
            }

        } catch (Exception $e) {

            Mage::getSingleton('adminhtml/session')->setTagData($requestPost);
            $this->_getSession()->addError($this->_helper()->__("Error while saving the tag. %s", $e->getMessage()));
            $this->_redirectReferer();
        }

    }

    /**
     * Delete slide
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id)
    {
        $tag = Mage::getModel('mpblog/tag')->load($id);
        if ($tag->getId()){
            try{
                $tag->delete();
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function massDeleteAction()
    {
        $tags = $this->getRequest()->getPost('tags');
        if ($tags){
            $success = 0;
            $error = 0;
            foreach ($tags as $tagId){
                if ($this->_delete($tagId)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s tags were successfully deleted.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s tags weren't deleted.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $this->_delete($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Tag was successfully deleted."));
            } catch (Exception $e){
                $this->_getSession()->addError($this->_helper()->__("Tag wasn't deleted. %s", $e->getMessage()));
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

    public function postsGridAction()
    {
        $grid = $this->getLayout()->createBlock('mpblog/adminhtml_tag_edit_tab_posts_grid');
        if ($grid){
            $this->getResponse()->setBody($grid->toHtml());
        }
    }
}