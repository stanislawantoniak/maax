<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Adminhtml_Fidelitas_TemplatesController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/templates');

        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }
        return $this;
    }

    public function indexAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Templates'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_templates'));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Templates'))->_title($this->__('Edit'));
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('fidelitas/templates')->load($id);

        $this->_title($model->getId() ? $model->getName() : $this->__('New'));

        if ($model->getId() || $id == 0) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $model->addData($data);
            }
            Mage::register('current_template', $model);


            $this->loadLayout();
            $this->_setActiveMenu('fidelitas/templates');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Templates Manager'), Mage::helper('adminhtml')->__('Templates Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Templates'), Mage::helper('adminhtml')->__('Templates'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_templates_edit'))
                    ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_templates_edit_tabs'));

            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Template does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        $data = $this->getRequest()->getPost();


        if ($data) {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('fidelitas/templates');

            try {

                $model->setData($data);

                if ($id) {
                    $model->setId($id);
                }

                $model->save();
                $this->_getSession()->addSuccess($this->__('Template was successfully saved'));
                $this->_getSession()->setFormData(false);


                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }


                $this->_redirect('*/*/index/');
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit/id/' . $id);
                return;
            }
        }


        $this->_getSession()->addError($this->__('Unable to find template to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {

        if ($this->getRequest()->getParam('id')) {

            $id = $this->getRequest()->getParam('id');

            try {
                $model = Mage::getModel('fidelitas/templates');
                $model->setId($id)->delete();

                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('Template was successfully deleted'));

                $this->_redirect('*/*/edit/id/' . $id);
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/index');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

}
