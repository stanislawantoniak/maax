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
class Licentia_Fidelitas_Adminhtml_Fidelitas_ListsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/lists');

        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }

        return $this;
    }

    public function indexAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Lists'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_lists'));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Lists'));
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/lists');
        Mage::register('current_list', $model);

        if ($id) {
            $model->load($id);
            if (!$model->getListId()) {
                $this->_getSession()->addError($this->__('This list no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        if ($model->getId() && $model->getData('purpose') == 'client') {
            $this->_getSession()->addError($this->__('You cannot edit the list with all your clients'));
            $this->_redirect('*/*');
            return;
        }


        $available = Mage::getModel('fidelitas/lists')->getAvailableStores($model->getId());
        if (count($available) == 0) {

            $this->_getSession()->addError($this->__('A Store View can only have one List. You do not have any Store View without a List assigned'));
            $this->_redirect('*/*');
            return;
        }

        $this->_title($model->getListId() ? $model->getTitle() : $this->__('New List'));

        // set entered data if was error when we do save
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_lists_edit'))
                ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_lists_edit_tabs'));

        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {

        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('id');

            $model = Mage::getModel('fidelitas/lists');

            try {
                if ($id) {
                    $model->setId($id);
                }

                $model->addData($data)->save();

                $this->_getSession()->addSuccess($this->__('List was successfully saved'));
                $this->_getSession()->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find List to save'));
        $this->_redirect('*/*/');
    }

}
