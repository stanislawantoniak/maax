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
class Licentia_Fidelitas_Adminhtml_Fidelitas_AbandonedController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/abandoned');
        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }

        return $this;
    }

    public function indexAction() {

        if (!Mage::getStoreConfig('fidelitas/config/customer_list')) {
            #$this->_getSession()->addError($this->__('To use Abandoned Cart Reminders, you need to enable the usage of the "Customer List"'));
            #$this->_redirect('*/system_config/edit', array('section' => 'fidelitas'));
            #return;
        }

        $this->_title($this->__('E-Goi'))->_title($this->__('Abandoned Cart Reminders'));

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_abandoned'));
        $this->renderLayout();
    }

    public function newAction() {
        $type = $this->getRequest()->getParam('type');

        if (!in_array($type, array('email', 'sms'))) {
            $this->_getSession()->addError($this->__('Invalid Abandoned Cart Reminder Type'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Abandoned Cart Reminders'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/abandoned')->load($id);

        if ($model->getId() || $this->getRequest()->getParam('type')) {

            $data = $this->_getSession()->getFormData();

            if (!empty($data)) {
                $model->addData($data);
            }
            Mage::register('current_abandoned', $model);

            if ($model->getChannel() == 'sms') {
                $this->getRequest()->setParam('channel', 'sms');
            } else {
                $this->getRequest()->setParam('channel', 'email');
            }

            $model->setData("groups", explode(',', $model->getData('groups')));
            $model->setData("stores", explode(',', $model->getData('stores')));

            $this->_title($model->getId() ? $model->getName() : $this->__('New'));

            $this->loadLayout();
            $this->_setActiveMenu('fidelitas/abandoned');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_abandoned_edit'))
                    ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_abandoned_edit_tabs'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Abandoned Cart Reminder does not exist'));
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

        if (!$template->getId()) {
            return;
        }

        $template->setData(array('message' => $template->getMessage()));

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($template->getData()));
    }

    public function saveAction() {

        if ($this->getRequest()->getPost()) {

            $data = $this->getRequest()->getPost();
            $channel = strtolower($data['channel']);
            $data = $this->_filterDates($data, array('from_date', 'to_date'));

            $data['stores'] = implode(',', $data['stores']);
            $data['groups'] = implode(',', $data['groups']);

            $id = $this->getRequest()->getParam('id');

            $model = Mage::getModel('fidelitas/abandoned');

            try {
                if ($id) {
                    $model->setId($id);
                }

                $model->addData($data);
                $model->save();

                if ($data['days'] == 0 && $data['hours'] == 0 && $data['minutes'] == 0) {
                    throw new Mage_Core_Exception($this->__('Invalid Send Time'));
                }

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess($this->__('The Abandoned Cart Reminder has been saved.'));

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
                $this->_getSession()->addError($this->__('An error occurred while saving the Abandoned Cart Reminder data. Please review the log and try again.'));
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

                $model = Mage::getModel('fidelitas/abandoned');
                $model->load($id);
                $model->delete();

                $this->_getSession()->addSuccess($this->__('The Abandoned Cart Reminder has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while deleting the Abandoned Cart Reminder. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find a Abandoned Cart Reminder to delete.'));
        $this->_redirect('*/*/');
    }

}
