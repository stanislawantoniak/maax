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
class Licentia_Fidelitas_Adminhtml_Fidelitas_FollowupController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/followup');
        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Follow Up'));

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_followup'));
        $this->renderLayout();
    }

    public function newAction() {
        $type = $this->getRequest()->getParam('type');

        if (!in_array($type, array('email', 'sms'))) {
            $this->_getSession()->addError($this->__('Invalid Follow Up Type'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Follow Up'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/followup')->load($id);

        if (!$model->getId() && $cid = $this->getRequest()->getParam('cid')) {
            $campaign = Mage::getModel('fidelitas/campaigns')->load($cid);

            if (!$campaign->getId()) {
                $this->_getSession()->addError($this->__('Campaign not found'));
                $this->_redirect('*/*/');
                return;
            }

            Mage::register('current_campaign', $campaign);
        }

        if ($model->getId() || $this->getRequest()->getParam('type')) {

            $data = $this->_getSession()->getFormData();

            if (!empty($data)) {
                $model->addData($data);
            }
            Mage::register('current_followup', $model);

            if ($model->getId()) {
                $campaign = Mage::getModel('fidelitas/campaigns')->load($model->getCampaignId());
                Mage::register('current_campaign', $campaign);
            } else {
                $data['campaign_id'] = $cid;
            }

            if ($model->getChannel() == 'sms') {
                $this->getRequest()->setParam('channel', 'sms');
            } else {
                $this->getRequest()->setParam('channel', 'email');
            }

            $model->setData('recipients_options', explode(',', $model->getData('recipients_options')));

            $this->_title($model->getId() ? $model->getName() : $this->__('New'));

            $this->loadLayout();
            $this->_setActiveMenu('fidelitas/followup');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_followup_edit'))
                    ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_followup_edit_tabs'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Follow Up does not exist'));
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
            $id = $this->getRequest()->getParam('id');
            $cid = $this->getRequest()->getParam('cid');
            $channel = strtolower($data['channel']);

            $model = Mage::getModel('fidelitas/followup');

            try {
                if ($id) {
                    $model->setId($id);
                }


                if (!$model->getId()) {
                    $campaign = Mage::getModel('fidelitas/campaigns')->load($cid);
                    $data['campaign_id'] = $cid;
                } else {
                    $tmpFollow = Mage::getModel('fidelitas/followup')->load($model->getId());
                    $campaign = Mage::getModel('fidelitas/campaigns')->load($tmpFollow->getCampaignId());
                }

                if (!$campaign->getId()) {
                    $this->_getSession()->addError($this->__('Campaign not found'));
                    $this->_redirectReferer();
                    return;
                }

                if ($campaign->getRecurring() != '0' && $data['active'] != '0') {
                    $data['active'] = '0';
                    $this->_getSession()->addNotice($this->__("You can't create follow ups for recurring campaigns. Follow Up is inactive."));
                }

                if (isset($data['segment_id']) && (int) $data['segment_id'] == 0) {
                    $data['segment_id'] = new Zend_Db_Expr('NULL');
                }

                $validator = new Zend_Validate_Date(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);
                if (!$validator->isValid($campaign->getData('deploy_at'))) {
                    throw new Mage_Core_Exception($this->__('Invalid Campaign deployment date'));
                }

                $date = Mage::app()
                        ->getLocale()
                        ->date()
                        ->setTime($campaign->getData('deploy_at'), Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME)
                        ->setDate($campaign->getData('deploy_at'), Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME)
                        ->addDay($data['days']);

                $data['send_at'] = $date->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);
                $data['recipients_options'] = implode(',', $data['recipients_options']);

                $model->addData($data);
                $model->save();

                $this->_getSession()->setFormData(false);
                $this->_getSession()->addSuccess($this->__('The Follow Up has been saved.'));

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
                $this->_getSession()->addError($this->__('An error occurred while saving the Follow Up data. Please review the log and try again.'));
                Mage::logException($e);
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/new', array('id' => $this->getRequest()->getParam('id'), 'type' => $channel));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {


        if ($id = $this->getRequest()->getParam('id')) {
            try {

                $model = Mage::getModel('fidelitas/followup')->load($id);
                $model->delete();

                $this->_getSession()->addSuccess($this->__('The Follow Up has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while deleting the Follow Up. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find a Follow Up to delete.'));
        $this->_redirect('*/*/');
    }

}
