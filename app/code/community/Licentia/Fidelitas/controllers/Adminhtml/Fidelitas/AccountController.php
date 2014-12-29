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
class Licentia_Fidelitas_Adminhtml_Fidelitas_AccountController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/account');
        return $this;
    }

    public function clearAction() {

        $core = Mage::getModel('core/config');
        $core->saveConfig('fidelitas/config/api_key', "0", 'default', 0);

        $data = array('campaigns', 'segments', 'autoresponders', 'splits', 'senders', 'followups', 'reports', 'account', 'lists', 'subscribers');

        foreach ($data as $delete) {
            try {
                $model = Mage::getModel('fidelitas/' . $delete);

                if (!$model) {
                    continue;
                }

                $collection = $model->getCollection();
                foreach ($collection as $item) {
                    $item->delete();
                }
            } catch (Exception $e) {

            }
        }

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        $this->_redirect('*/*/');
    }

    public function supportAction() {

        $version = version_compare(Mage::helper('fidelitas')->getCurrentVersion(), Mage::helper('fidelitas')->getLastestVersion());

        if ($version == -1) {
            $this->_getSession()->addError('You are running an outdated version of this extension. Please consider updating before submiting an error report.');
        }

        $info = Mage::getModel('fidelitas/egoi')->getUserData()->getData();

        Mage::register('current_account', $info[0]);

        if ($this->getRequest()->isPost()) {

            $params = array_merge($info[0], $this->getRequest()->getPost());

            unset($params['form_key']);
            unset($params['usernname']);
            unset($params['credits']);
            unset($params['user_level']);
            unset($params['fax']);
            unset($params['gender']);
            unset($params['apikey']);

            $email = 'support@licentia.pt';

            $msg = '';
            $params['date'] = now();
            $params['fidelitas_version'] = Mage::helper('fidelitas')->getCurrentVersion();

            foreach ($params as $key => $value) {
                $msg .="$key : $value <br>";
            }

            $mail = Mage::getModel('core/email');
            $mail->setToName('Support');
            $mail->setToEmail($email);
            $mail->setBody($msg);
            $mail->setSubject('Contacto - Magento Extension');
            $mail->setFromEmail($params['email']);
            $mail->setFromName($params['first_name'] . ' ' . $params['last_name']);
            $mail->setType('html');

            try {
                $t = $mail->send();

                if ($t === false) {
                    throw new Exception('Unable to send. Please send an email to support@licentia.pt');
                }

                $this->_getSession()->addSuccess($this->__('Your request has been sent'));
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/support');
            }

            $this->_redirectReferer();
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('fidelitas/account');

        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_account_support_edit'))
                ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_account_support_edit_tabs'));

        $this->renderLayout();
    }

    public function newAction() {
        $this->getRequest()->setParam('op', 'api');
        $op = $this->getRequest()->getParam('op');

        if ($op == 'api') {
            $this->_getSession()->addNotice($this->__("If you don't have an E-Goi account please %s. If you want to know more about E-Goi %s", '<a target="_blank" href="http://bo.e-goi.com/?action=registo&aff=fadb7a3c20">click here</a>', '<a target="_blank" href="http://www.e-goi.com/index.php?aff=fadb7a3c20">click here</a>'));
        } else {
            $this->_getSession()->addNotice($this->__('If you already have an E-Goi account please %s', '<a href="' . $this->getUrl('*/*/*/op/api') . '">click here</a>'));
        }

        $model = new Varien_Object();

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('current_account', $model);

        $this->loadLayout();
        $this->_setActiveMenu('fidelitas/account');

        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_account_new_edit'))
                ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_account_new_edit_tabs'));

        $this->renderLayout();
    }

    public function indexAction() {
        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_account'));
        $this->renderLayout();
    }

    public function sendersAction() {

        if ($this->getRequest()->getParam('op') == 'refresh') {
            $total = Mage::getModel('fidelitas/senders')
                    ->cron()
                    ->getCollection()
                    ->count();

            if ($total > 0) {
                Mage::getSingleton('admin/session')->getUser()->setData('fidelitasAuthSenders', true);
                $this->_getSession()->addSuccess($this->__('Senders Imported Successfully!!!'));
                $this->_redirect('*/fidelitas_campaigns/');
                return;
            } else {

                $this->_getSession()->addError($this->__("We where sorry, but we didn't find any new senders in your account."));
                $this->_redirect('*/*/*');
                return;
            }
        }

        $this->_getSession()->addNotice($this->__("You don't have any verified sender in you account."));

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_account_senders'));
        $this->renderLayout();
    }

    public function mapAction() {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            foreach ($data['store'] as $key => $value) {
                if ($value == 0)
                    unset($data['store'][$key]);
            }

            $map = $data['store'];

            if (count($map) != count(array_unique($map))) {
                $this->_getSession()->addError($this->__("You can not assign the same List to different stores"));
                $this->_redirectReferer();
                return;
            }
            $this->_getSession()->addSuccess($this->__('Success!!! Please wait while we setup the environment. Don\'t close or refresh this page.'));
            $this->_getSession()->setFidelitasMap($map);
            $this->_redirectReferer();
            return;
        }
        $this->_redirect('*/*/*');
    }

    public function firstAction() {
        $this->_initAction();

        $op = $this->getRequest()->getParam('op');

        $map = $this->_getSession()->getFidelitasMap();

        if ($op == 'ok') {
            $lists = Mage::getModel('fidelitas/egoi')->getLists();
            Mage::register('egoi_lists', $lists);

            if (count($lists->getData()) > 0 && !is_array($map)) {
                $this->_setActiveMenu('fidelitas/account');
                $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_account_sync_edit'))
                        ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_account_sync_edit_tabs'));
            } else {
                $block = $this->getLayout()
                        ->createBlock('core/text', 'first-run')
                        ->setText('<META HTTP-EQUIV=Refresh CONTENT="3; URL=' . $this->getUrl('*/*/first') . '">');

                $this->_addContent($block);
            }
        } else {
            $this->firstRun();
            #$this->importSubscribers();
            $this->_redirect('*/*/sync');
            return;
        }
        $this->renderLayout();
    }

    public function firstRun() {

        $this->_getSession()->setData('fidelitas_first_run', true);

        $stores = Mage::getModel('adminhtml/system_store')->getStoreOptionHash();

        //lets create the admin list
        Mage::getModel('fidelitas/lists')->getAdminList();

        $map = $this->_getSession()->getFidelitasMap();

        //Now each store view has it's own list
        foreach ($stores as $storeId => $storeName) {
            $data = array();
            $data['store_id'] = $storeId;

            if ($map && isset($map[$storeId])) {
                $data['listnum'] = $map[$storeId];
            }

            $data['nome'] = $this->__('General');
            $data['internal_name'] = '[Mag] ' . Mage::getModel('adminhtml/system_store')->getStoreNameWithWebsite($storeId);
            Mage::getModel('fidelitas/lists')->setData($data)->save();
        }

        $this->_getSession()->setFidelitasMap(false);

        Mage::getModel('fidelitas/account')->setData(array('credits' => '0'))->save();
    }

    public function syncAction() {

        $admin = Mage::getSingleton('admin/session')->getUser();
        $user = $admin->getId();

        if ($this->_getSession()->getData('fidelitas_first_run') === true) {
            Mage::getModel('fidelitas/account')->load(1)->setData('cron', 3)->setData('notify_user', $user)->save();
        } else {
            Mage::getModel('fidelitas/account')->load(1)->setData('cron', 1)->setData('notify_user', $user)->save();
        }

        $cron = Mage::getModel('cron/schedule')->getCollection()
                ->addFieldToFilter('job_code', 'fidelitas_sync_manually')
                ->addFieldToFilter('status', 'pending');

        if ($cron->getSize() > 0) {
            $this->_getSession()->addError($this->__('Please wait until previous cron ends'));
        } else {
            $cron = Mage::getModel('cron/schedule');
            $data['status'] = 'pending';
            $data['job_code'] = 'fidelitas_sync_manually';
            $data['scheduled_at'] = now();
            $data['created_at'] = now();
            $cron->setData($data)->save();
            $this->_getSession()->addSuccess($this->__('Data will be synced next time cron runs'));
        }

        $this->_redirect('*/*/');
        return;
    }

    public function saveAction() {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            $model = Mage::getModel('fidelitas/egoi')->setData('apikey', $data['apikey'])->checkLogin($data['apikey']);
            if ($model->getData('user_id')) {
                Mage::getConfig()->saveConfig('fidelitas/config/api_key', $data['apikey']);
                Mage::getConfig()->cleanCache();

                $lists = Mage::getModel('fidelitas/egoi')->getLists();
                if (count($lists->getData()) == 0) {
                    $this->_getSession()->addSuccess($this->__('Success!!! Please wait while we setup the environment. Don\'t close or refresh this page.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('Success!!!'));
                }
                $this->_redirect('*/*/first/op/ok');
                return;
            } else {
                $this->_getSession()->addError($this->__('Apikey invalid'));
                $this->_redirect('*/*/new/op/api');
                return;
            }

            $this->_redirect('*/*/');
        }
    }

}
