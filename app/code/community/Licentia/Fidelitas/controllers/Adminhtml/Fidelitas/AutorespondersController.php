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
class Licentia_Fidelitas_Adminhtml_Fidelitas_AutorespondersController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/autoresponders');
        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Autoresponders'));

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_autoresponders'));
        $this->renderLayout();
    }

    public function previewAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Autoresponders'));
        $this->_initAction();

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/autoresponders')->load($id);
        Mage::register('current_autoresponder', $model);

        $this->renderLayout();
    }

    public function newAction() {
        $type = $this->getRequest()->getParam('type');

        if (!in_array($type, array('email', 'sms'))) {
            $this->_getSession()->addError($this->__('Invalid Autoresponder Type'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Autoresponders'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/autoresponders')->load($id);

        if ($model->getId() || $this->getRequest()->getParam('type')) {

            $data = $this->_getSession()->getFormData();

            if (!empty($data)) {
                $model->addData($data);
            }
            Mage::register('current_autoresponder', $model);

            if ($model->getChannel() == 'sms') {
                $this->getRequest()->setParam('channel', 'sms');
            } else {
                $this->getRequest()->setParam('channel', 'email');
            }


            $this->_title($model->getId() ? $model->getName() : $this->__('New'));

            $this->loadLayout();
            $this->_setActiveMenu('fidelitas/autoresponders');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_autoresponders_edit'))
                    ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_autoresponders_edit_tabs'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Autoresponder does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function defaultTemplateAction() {
        $templateCode = $this->getRequest()->getParam('code');

        $template = Mage::getModel('fidelitas/templates')->load($templateCode);

        if (!$template->getId())
            return;

        $template->setData(array('message' => $template->getMessage()));

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($template->getData()));
    }

    public function saveAction() {

        if ($this->getRequest()->getPost()) {

            $data = $this->getRequest()->getPost();
            $data = $this->_filterDates($data, array('from_date', 'to_date'));

            $id = $this->getRequest()->getParam('id');

            $model = Mage::getModel('fidelitas/autoresponders');

            try {
                if ($id) {
                    $model->setId($id);
                }

                $channel = strtolower($data['channel']);

                $data['product'] = trim($data['product']);

                if (isset($data['segment_id']) && (int) $data['segment_id'] == 0) {
                    $data['segment_id'] = new Zend_Db_Expr('NULL');
                }

                $model->addData($data);



                if ($model->getData('event') == 'order_product') {
                    $product = Mage::getModel('catalog/product')->load($model->getData('product'));

                    if (!$product->getId()) {
                        throw new Mage_Core_Exception('Product Not Found');
                    }
                }

                $model->save();

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess($this->__('The Autoresponder has been saved.'));

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'type' => $channel));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);

                if ($this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'type' => $channel));
                } else {
                    $this->_redirect('*/*/new', array('type' => $channel));
                }

                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while saving the Autoresponders data. Please review the log and try again.'));
                Mage::logException($e);
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/new', array('id' => $this->getRequest()->getParam('id'), 'channel' => $channel));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {


        if ($id = $this->getRequest()->getParam('id')) {
            try {

                $model = Mage::getModel('fidelitas/autoresponders');
                $model->load($id);
                $model->delete();

                $this->_getSession()->addSuccess($this->__('The Autoresponder has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while deleting the Autoresponder. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find a Autoresponder to delete.'));
        $this->_redirect('*/*/');
    }

}
