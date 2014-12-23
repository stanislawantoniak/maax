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
class Licentia_Fidelitas_Adminhtml_Fidelitas_SplitsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/splits');

        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }
        return $this;
    }

    public function indexAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Splits A/B'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_splits'));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Splits'))->_title($this->__('Edit'));
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('fidelitas/splits')->load($id);

        $this->_title($model->getId() ? $model->getName() : $this->__('New'));

        if ($model->getId() || $id == 0) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $model->addData($data);
            }
            Mage::register('current_split', $model);


            $this->loadLayout();
            $this->_setActiveMenu('fidelitas/splits');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_splits_edit'))
                    ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_splits_edit_tabs'));

            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('A/B Campaign does not exist'));
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
            $model = Mage::getModel('fidelitas/splits');

            try {

                $data = $this->_filterDateTime($data, array('deploy_at'));
                if (isset($data['segment_id']) && (int) $data['segment_id'] == 0) {
                    $data['segment_id'] = new Zend_Db_Expr('NULL');
                }
                $date = Mage::app()
                        ->getLocale()
                        ->date();

                $dateDays = clone $date;
                $dateDays->setTime($data['deploy_at'], Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME)
                        ->setDate($data['deploy_at'], Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME)
                        ->addDay($data['days']);

                $data['send_at'] = $dateDays->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);

                $model->setData($data);
                if ($id) {
                    $model->setId($id);
                }


                if ($date->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME) > $data['deploy_at'] &&
                        $model->getSend() == 0) {
                    throw new Exception($this->__('Your deploy date cannot be earlier then &lt;now&gt;.'));
                }


                $model->save();
                $this->_getSession()->addSuccess($this->__('A/B Campaign was successfully saved'));
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


        $this->_getSession()->addError($this->__('Unable to find A/B Campaign to save'));
        $this->_redirect('*/*/');
    }

    public function sendAction() {
        $id = $this->getRequest()->getParam('id');
        $version = $this->getRequest()->getVersion();

        if (!in_array($version, array('a', 'b'))) {
            $this->_getSession()->addError($this->__('Invalid Version'));
            $this->_redirectReferer();
            return;
        }

        $split = Mage::getModel('fidelitas/splits')->load($id);

        if (!$split->getId()) {
            $this->_getSession()->addError($this->__('Unable to find A/B Campaign'));
            $this->_redirectReferer();
            return;
        }
        if (
                $split->getClosed() == 1 ||
                $split->getActive() == 0 ||
                $split->getWinner() != 'manually' ||
                $split->getSent() == 0
        ) {

            $this->_getSession()->addError($this->__('Unable to perform action. Please verify all requisites'));
            $this->_redirectReferer();
            return;
        }

        try {
            $split->sendManually($split, $version, true);
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/index');
        }
    }

    public function deleteAction() {

        if ($this->getRequest()->getParam('id')) {

            $id = $this->getRequest()->getParam('id');

            try {
                $model = Mage::getModel('fidelitas/splits');
                $model->setId($id)->delete();

                $this->_getSession()->addSuccess($this->__('A/B Campaign was successfully deleted'));

                $this->_redirect('*/*/index');
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/index');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function defaultTemplateAction() {
        $templateCode = $this->getRequest()->getParam('code');

        $template = Mage::getModel('fidelitas/templates')->load($templateCode);

        if (!$template->getId())
            return;

        $template->setData(array('message_a' => $template->getMessage()));

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($template->getData()));
    }

    public function defaultTemplatebAction() {
        $templateCode = $this->getRequest()->getParam('code');

        $template = Mage::getModel('fidelitas/templates')->load($templateCode);

        if (!$template->getId())
            return;

        $template->setData(array('message_b' => $template->getMessage()));

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($template->getData()));
    }

}
