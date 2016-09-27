<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Adminhtml_Mpblog_CategoryController extends Magpleasure_Blog_Controller_Adminhtml_Filterable
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
        $aclRoute = 'cms/mpblog/categories';
        $this
            ->_getSession()
            ->setControlRoutePath($aclRoute)
        ;
        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    /**
     * Initialize layout prefer any action
     * @return Magpleasure_Activecontent_Admin_BlockController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/mpblog/categories')
            ->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Category'));
        return $this;
    }


    public function indexAction()
    {
        $this->_prepareStoreFilter();

        $this
            ->_initAction()
            ->renderLayout()
        ;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $post = Mage::getModel('mpblog/category');
        if ($id){
            $post->load($id);
        }

        $this->loadLayout();
        $this->_setActiveMenu('cms/mpblog/categories');

        if ($post->getId() || !$id){

            Mage::register('current_category', $post);
            $data = Mage::getSingleton('adminhtml/session')->getPostData(true);
            if (!empty($data)) {
                $post->setData($data);
            }

            $this->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
            $this->_addBreadcrumb($this->_helper()->__('Category'), $this->_helper()->__('Category'));



        } else {
            $this->_getSession()->addError($this->_helper()->__('Category is not exists.'));
            $this->_redirect('*/*/index');
        }

        $this->renderLayout();
    }

    public function saveAction()
    {
        $requestPost = $this->getRequest()->getPost();
        /** @var Magpleasure_Blog_Model_Post $category  */
        $category = Mage::getModel('mpblog/category');
        if ($id = $this->getRequest()->getParam('id')){
            $category->load($id);
        }

        try {
            $category->addData($requestPost);
            $category->save();
            $this->_getSession()->addSuccess($this->_helper()->__("Category weren't successfully saved."));

            $params = $this->_getCommonParams();
            $params['id'] = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : $category->getId();

            if ($this->getRequest()->getParam('back')){
                $this->_redirect('*/*/edit', $params);
            } elseif ($backTo = $this->getRequest()->getParam('back_to')) {
                $backTo = $this->_commonHelper()->getCore()->urlDecode($backTo);
                $this->_redirectUrl($backTo);
            } else {
                $this->_redirect('*/*/index', $this->_getCommonParams());
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->setPostData($requestPost);
            $this->_getSession()->addError($this->_helper()->__("Error while saving the category (%s).", $e->getMessage()));
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
        $category = Mage::getModel('mpblog/category')->load($id);
        if ($category->getId()){
            try{
                $category->delete();
                return true;
            } catch(Exception $e) {
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
        /** @var $category Magpleasure_Blog_Model_Category */
        $category = Mage::getModel('mpblog/category')->load($id);
        if ($category->getId()){
            try{
                return $category->duplicate();
            } catch(Exception $e) {
                return false;
            }
        }
    }

    protected function _updateStatus($id, $status)
    {
        if ($id){
            try {
                $category = Mage::getModel('mpblog/category')->load($id);
                $category->setStatus($status);
                $category->save();
                return true;
            } catch (Exception $e){
                return false;
            }
        }
        return false;
    }

    public function massStatusAction()
    {
        $categories = $this->getRequest()->getPost('categories');
        $status = $this->getRequest()->getPost('status');
        if ($categories){
            $success = 0;
            $error = 0;
            foreach ($categories as $categoryId){
                if ($this->_updateStatus($categoryId, $status)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s categories were successfully updated.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s categories weren't updated.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $categories = $this->getRequest()->getPost('categories');
        if ($categories){
            $success = 0;
            $error = 0;
            foreach ($categories as $categoryId){
                if ($this->_delete($categoryId)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s categories were successfully deleted.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s categories weren't deleted.", $error));
            }
        }
        $this->_redirectReferer();
    }


    public function duplicateAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $newCategory = $this->_duplicate($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Category were successfully duplicated."));

                $params = $this->_getCommonParams();
                $params['id'] = $newCategory->getId();
                $this->_redirect('*/*/edit', $params);

            } catch (Exception $e){
                $this->_getSession()->addError($this->_helper()->__("Category weren't duplicated. %s", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
    }

    public function massDuplicateAction()
    {
        $categories = $this->getRequest()->getPost('categories');
        if ($categories){
            $success = 0;
            $error = 0;
            foreach ($categories as $categoryId){
                if ($this->_duplicate($categoryId)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s categories were successfully duplicated.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s categories weren't duplicated.", $error));
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
                $this->_getSession()->addSuccess($this->_helper()->__("Category was successfully deleted."));
            } catch (Exception $e){
                $this->_getSession()->addError($this->_helper()->__("Category wasn't deleted. %s", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
        $this->_redirect('*/*/index');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massUpdateStoreViewGoAction()
    {
        $post = $this->getRequest()->getPost();
        $post['add_store_view'] = isset($post['add_store_view']) ? 1 : 0;
        $post['remove_store_view'] = isset($post['remove_store_view']) ? 1 : 0;
        $data = new Varien_Object($post);

        if ($categories = $data->getCategoryIds()){

            $categories = explode(",", $categories);
            if (is_array($categories) && count($categories) && ($data->getAddStoreView() || $data->getRemoveStoreView())){

                $success = 0;
                $errors = 0;
                foreach ($categories as $categoryId){

                    try {
                        /** @var $post Magpleasure_Blog_Model_Post */
                        $category = Mage::getModel('mpblog/category')->load($categoryId);
                        $stores = $category->getStores();
                        if ($data->getRemoveStoreView()){
                            $stores = array_diff($stores, $data->getRemoveStore());
                        }
                        if ($data->getAddStoreView()){
                            $stores = array_merge($stores, $data->getAddStore());
                            $stores = array_unique($stores);
                        }
                        $category->setStores($stores)->save();

                        $success++;
                    } catch (Exception $e) {
                        $errors++;
                    }
                }

                if ($success){
                    $this->_getSession()->addSuccess($this->_helper()->__("%s categories were successfully updated.", $success));
                }
                if ($errors){
                    $this->_getSession()->addError($this->_helper()->__("%s categories weren't updated.", $errors));
                }
            }
        }
        $this->_redirect('*/*/index');

    }

    public function massUpdateStoreViewAction()
    {
        $categories = $this->getRequest()->getPost('categories');
        if ($categories && is_array($categories)){

            $this->loadLayout();
            $this->_setActiveMenu('cms/mpblog/category');

            /** @var $edit Magpleasure_Blog_Block_Adminhtml_Post_Update_Edit */
            $edit = $this->getLayout()->createBlock('mpblog/adminhtml_category_update_edit');
            if ($edit){
                Mage::register(Magpleasure_Blog_Block_Adminhtml_Category_Update_Edit_Form::CATEGORIES_KEY, $categories, true);
                $this->_addContent($edit);
            }
            $this->renderLayout();

        } else {
            $this->_getSession()->addError($this->_helper()->__("Anyone category is required."));
            $this->_redirectReferer();
        }
    }
}