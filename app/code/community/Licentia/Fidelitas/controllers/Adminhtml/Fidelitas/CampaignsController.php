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
class Licentia_Fidelitas_Adminhtml_Fidelitas_CampaignsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/campaigns');
        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }

        $senders = Mage::getModel('fidelitas/egoi')->validateEgoiSenders();
        if (!$senders) {
            $this->_redirect('adminhtml/fidelitas_account/senders');
        }
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Campaigns'));
        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_campaigns'));

        $this->_addBreadcrumb($this->__('Campaigns'), $this->__('Campaigns'))
                ->renderLayout();
    }

    public function previewAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Campaigns'));
        $this->_initAction();

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/campaigns')->load($id);
        Mage::register('current_campaign', $model);

        $this->_addBreadcrumb($this->__('Campaigns'), $this->__('Campaigns'))
                ->renderLayout();
    }

    public function newAction() {

        $type = $this->getRequest()->getParam('type');

        if (!in_array($type, array('email', 'sms'))) {
            $this->_getSession()->addError($this->__('Invalid Newsletter Type'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Campaigns'));
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/campaigns')->load($id);

        $followups = Mage::getModel('fidelitas/followup')->getCollection()
                ->addFieldToFilter('campaign_id', $model->getId());

        Mage::register('current_followup', $followups);

        if ($this->getRequest()->getParam('listnum')) {
            $listnum = $this->getRequest()->getParam('listnum');

            $list = Mage::getModel('fidelitas/lists')->load($listnum, 'listnum');

            if (!$list->getId()) {
                throw new Mage_Core_Exception('List Not Found');
            }
        }

        if ($model->getId() || $this->getRequest()->getParam('type')) {

            $model->setData('specific_dates', explode(',', $model->getData('specific_dates')));

            if ($model->getLocalStatus() == 'finished') {
                $this->_getSession()->addNotice($this->__("This campaign is now closed. You can't modify it. Click on 'Duplicate & Save' to duplicate it and edit."));
            }

            $model->setData("segments_ids", explode(',', $model->getData('segments_ids')));
            $model->setData("egoi_segments", explode(',', $model->getData('egoi_segments')));
            $model->setData("recurring_daily", explode(',', $model->getData('recurring_daily')));

            if ($model->getChannel() == 'SMS') {
                $this->getRequest()->setParam('channel', 'sms');
            } else {
                $this->getRequest()->setParam('channel', 'email');
            }

            $channel = $this->getRequest()->getParam('type');
            if ($channel == 'sms') {
                $senders = Mage::getModel('fidelitas/senders')->getSenders('sms');
                if (count($senders) == 0) {
                    $this->_getSession()->addError($this->__("You don't have any SMS sender in your account. Please add one in your E-Goi.com panel and then sync your data"));
                    $this->_redirect('*/*/');
                    return;
                }
            }
            $data = $this->_getSession()->getFormData();

            if (!empty($data)) {
                $model->addData($data);
            }
            Mage::register('current_campaign', $model);

            $this->_title($model->getId() ? $model->getInternalName() : $this->__('New'));

            $this->loadLayout();
            $this->_setActiveMenu('fidelitas/campaigns');

            $this->_addBreadcrumb($this->__('Campaigns'), $this->__('Campaigns'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_campaigns_edit'))
                    ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_campaigns_edit_tabs'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Campaign does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridconvAction() {
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('fidelitas/campaigns')->load($id);

        if ($model->getId()) {
            Mage::register('current_campaign', $model);

            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function saveAction() {

        if ($this->getRequest()->getPost()) {

            $data = $this->getRequest()->getPost();

            $id = $this->getRequest()->getParam('id');
            $data = $this->_filterDateTime($data, array('recurring_first_run', 'deploy_at'));

            if ($this->getRequest()->getParam('op') == 'send') {
                $data['deploy_at'] = Mage::getSingleton('core/date')->gmtdate();
            }

            $data = $this->_filterDates($data, array('run_until'));

            if (!isset($data['recurring_daily'])) {
                $data['recurring_daily'] = range(0, 6);
            }

            $data['listnum'] = $data['listnum'];
            $data['recurring_daily'] = implode(',', $data['recurring_daily']);
            if (isset($data['segments_ids'])) {
                $data['segments_ids'] = implode(',', $data['segments_ids']);
            }
            if (isset($data['egoi_segments'])) {
                $data['egoi_segments'] = implode(',', $data['egoi_segments']);
            }


            $channel = strtolower($data['channel']);

            $model = Mage::getModel('fidelitas/campaigns');

            try {

                if ($id && $this->getRequest()->getParam('op') != 'duplicate') {
                    $model->setId($id);
                }
                if ($this->getRequest()->getParam('op') == 'duplicate') {
                    $data['deploy_at'] = Mage::app()->getLocale()->date()->addDay(6)->get('yyyy-MM-dd');
                }

                if ($model->getId()) {
                    if ($data['recurring'] != '0') {
                        $followupData['active'] = '0';
                    }
                    $followup = Mage::getModel('fidelitas/followup')->load($model->getId(), 'campaign_id');
                    if ($followup->getId()) {
                        $followup->setData($followupData)->save();
                    }
                }

                $model->addData($data);
                $model->save();

                $this->_getSession()->setFormData(false);

                if ($this->getRequest()->getParam('op') == 'send') {
                    $this->_redirect('*/*/send', array('id' => $model->getId(), 'channel' => $channel));
                    return;
                }

                if ($this->getRequest()->getParam('op') == 'duplicate') {
                    $this->_getSession()->addSuccess($this->__('The campaign has been duplicated. You are now working on the duplicated one'));
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                } else {

                    $this->_getSession()->addSuccess($this->__('The campaign has been saved.'));
                }

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
                $this->_getSession()->addError($this->__('An error occurred while saving the campaign data. Please review the log and try again.'));
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

                $model = Mage::getModel('fidelitas/campaigns');
                $model->load($id);

                $model->delete();

                $this->_getSession()->addSuccess($this->__('The campaign has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while deleting the campaign. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find a campaign to delete.'));
        $this->_redirect('*/*/');
    }

    public function defaultTemplateAction() {
        $templateCode = $this->getRequest()->getParam('code');

        $template = Mage::getModel('fidelitas/templates')->load($templateCode);

        if (!$template->getId())
            return;

        $template->setData(array('message' => $template->getMessage()));

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($template->getData()));
    }

    public function sendAction() {

        $id = $this->getRequest()->getParam('id');
        $campaign = Mage::getModel('fidelitas/campaigns')->load($id);

        if (!$campaign->getId()) {
            $this->_getSession()->addError($this->__('Campaign Not Found'));
            $this->_redirect('*/*/');
            return;
        }

        Mage::register('current_campaign', $campaign);

        if ($campaign->getRecurring() != '0') {

            $this->_getSession()->addNotice($this->__('This is a recurring campaign. It will only run on schedule'));
            $this->_redirect('*/*/index');
            return;
        }

        $channel = strtolower($campaign->getChannel());

        try {
            if ($channel == 'sms') {
                $result = Mage::getModel('fidelitas/campaigns')->sendSms($campaign);
            }
            if ($channel == 'email') {
                $result = Mage::getModel('fidelitas/campaigns')->sendEmail($campaign);
            }
            $this->_getSession()->addSuccess($this->__('Campaign Sent Successfully!'));
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
    }

    public function cancelAction() {

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('fidelitas/campaigns');
                $model->load($id)->setData('local_status', 'finished')->save();

                $this->_getSession()->addSuccess($this->__('The campaign has been canceled.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while canceling the campaign. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find a campaign to cancel.'));
        $this->_redirect('*/*/');
    }

    public function conversionsAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Campaigns'))->_title($this->__('Conversions'));

        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('fidelitas/campaigns')->load($id);

        if ($model->getId()) {
            Mage::register('current_campaign', $model);

            $this->loadLayout();
            $this->_setActiveMenu('fidelitas/campaigns');
            $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_campaigns_conversions'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Campaign does not exist'));
            $this->_redirect('*/*/');
        }
    }

}
