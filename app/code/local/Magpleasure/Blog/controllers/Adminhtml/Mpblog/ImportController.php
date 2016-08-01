<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Adminhtml_Mpblog_ImportController extends Magpleasure_Common_Controller_Adminhtml_Action
{
    protected function _isAllowed()
    {
        $aclRoute = 'system/mpblog/import';
        $this
            ->_getSession()
            ->setControlRoutePath($aclRoute)
        ;
        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/mpblog/import')
            ->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Import'));
        return $this;
    }

    public function wordpressAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function awblogAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function processAction()
    {
        $type = $this->getRequest()->getParam('type');
        $postData = $this->getRequest()->getPost();

        if ($type){

            $importer = $this->_helper()->_importer($type);

            try {

                $importer->import(false, $postData);
                $this->_getSession()->addSuccess($this->_helper()->__("Your data was successfully imported."));

            } catch (Exception $e){

                $this->_getSession()->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')
                    ->setData(Magpleasure_Blog_Block_Adminhtml_Import_Form::SESSION_KEY, $postData);
            }

        } else {

            $this->_getSession()->addError($this->_helper()->__("TYPE param is undefined."));
        }
        $this->_redirectReferer();
    }
}