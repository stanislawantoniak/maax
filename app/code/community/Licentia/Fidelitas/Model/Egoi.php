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
class Licentia_Fidelitas_Model_Egoi extends Varien_Object {

    const PLUGIN_KEY = 'e419a126e087bed65ad7fe8342f2f493';
    const API_URL = 'http://api.e-goi.com/v2/soap.php?wsdl';

    protected $_client;

    public function _construct() {
        parent::_construct();
        $this->_client = new Zend_Soap_Client(self::API_URL, array("user_agent" => "Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120403211507 Firefox/12.0"));
    }

    public function validateEgoiEnvironment() {
        $auth = Mage::getSingleton('admin/session')->getUser()->getData('fidelitasAuth');

        if ($auth === true) {
            return true;
        }

        $info = $this->getUserData()->getData();

        if (!isset($info[0]) || !isset($info[0]['user_id']) || (int) $info[0]['user_id'] == 0) {
            return false;
        }

        $account = Mage::getModel('fidelitas/account')->load(1);

        if ((int) $account->getData('cliente_id') == 0) {

            $n = Mage::getModel('fidelitas/egoi')->getAccountDetails()->getData();
            $account->addData($n[0])->save();

            $account = Mage::getModel('fidelitas/account')->load(1);

            if ((int) $account->getData('cliente_id') == 0) {
                return false;
            }
        }

        Mage::getSingleton('admin/session')->getUser()->setData('fidelitasAuth', true);

        return true;
    }

    public function validateEgoiSenders() {
        $auth = Mage::getSingleton('admin/session')->getUser()->getData('fidelitasAuthSenders');

        if ($auth === true) {
            return true;
        }
        $model = Mage::getModel('fidelitas/senders')->getCollection()->count();

        if ($model == 0) {
            return false;
        }

        Mage::getSingleton('admin/session')->getUser()->setData('fidelitasAuthSenders', true);

        return true;
    }

    public function formatFields($data) {

        if (!is_array($data)) {
            $data = array('RESULT' => $data);
        }

        if (count($data) == 1 && isset($data['ERROR'])) {
            Mage::log(serialize($data), 2, 'fidelitas-egoi.log');
            $data = array(0 => $data);
            $this->setData($data);
            return;
        }

        if (!array_key_exists(0, $data)) {
            $data = array(0 => $data);
        }

        foreach ($data as $key => $value) {
            $data[$key] = array_change_key_case($value, CASE_LOWER);
        }

        $this->setData($data);
        return $this;
    }

    public function getDataKey() {

        $data = $this->getData();
        $data['apikey'] = Mage::getStoreConfig('fidelitas/config/api_key');
        $data['plugin_key'] = self::PLUGIN_KEY;

        return $data;
    }

    public function processServiceResult($result) {

        if (!is_array($result)) {
            $result = array('result' => $result);
        }

        $result = array_change_key_case($result, CASE_LOWER);

        $this->setData($result);

        $additionalData = serialize(array('request' => $this->_client->getLastRequest(), 'response' => $this->_client->getLastResponse()));

        if (isset($result['error'])) {
            Mage::log(serialize($additionalData), 2, 'fidelitas-egoi.log');
            throw new Mage_Core_Exception(Mage::helper('fidelitas')->__($result['error']));
        }

        return $this;
    }

    public function getReports() {
        $this->formatFields($this->_client->getReport($this->getDataKey()));
        return $this;
    }

    public function getHeaderFooterTemplates() {
        $this->formatFields($this->_client->getHeaderFooterTemplates($this->getDataKey()));

        $data = array();
        foreach ($this->getData() as $info) {
            $data[$info['id']] = $info['name'];
        }

        $this->setData($data);
        return $this->getData();
    }

    public function getAccountDetails() {
        $this->formatFields($this->_client->getClientData($this->getDataKey()));
        return $this;
    }

    public function getUserData() {
        $this->formatFields($this->_client->getUserData($this->getDataKey()));
        return $this;
    }

    public function getCampaigns() {
        $this->setData('limit', 1000);
        $result = $this->_client->getCampaigns($this->getDataKey());
        $this->formatFields($result);
        return $this;
    }

    public function getLists() {

        $result = $this->_client->getLists($this->getDataKey());

        foreach ($result as $key => $value) {

            if (!is_array($value['extra_fields'])) {
                continue;
            }

            foreach ($value['extra_fields'] as $eKey => $eValue) {
                unset($result[$key]['extra_fields'][$eKey]['id']);
                unset($result[$key]['extra_fields'][$eKey]['listnum']);
                unset($result[$key]['extra_fields'][$eKey]['opcoes']);
            }
        }

        $this->formatFields($result);
        return $this;
    }

    public function getSegments() {
        $this->formatFields($this->_client->getSegments($this->getDataKey()));
        return $this;
    }

    public function getSubscriberData() {
        $this->formatFields($this->_client->subscriberData($this->getDataKey()));
        return $this;
    }

    public function editApiCallback() {
        return $this->processServiceResult($this->_client->editApiCallback($this->getDataKey()));
    }

    public function createList() {
        return $this->processServiceResult($this->_client->createList($this->getDataKey()));
    }

    public function updateList() {
        return $this->processServiceResult($this->_client->updateList($this->getDataKey()));
    }

    public function addSubscriber() {
        $result = Mage::getModel('fidelitas/egoi')
                ->setData('listID', $this->getData('list'))
                ->setData('subscriber', $this->getEmail())
                ->getSubscriberData()
                ->getData();


        if (is_array($result) && $result[0]['ERROR'] == 'SUBSCRIBER_NOT_FOUND') {
            return $this->processServiceResult($this->_client->addSubscriber($this->getDataKey()));
        }

        if ($this->getData('inCron')) {
            return;
        }

        return $this->processServiceResult(array('error' => Mage::helper('fidelitas')->__('Subscriber already exists in your E-Goi account with a status that does no allow you to add it again')));
    }

    public function editSubscriber() {
        $result = Mage::getModel('fidelitas/egoi')
                ->setData('listID', $this->getData('list'))
                ->setData('subscriber', $this->getEmail())
                ->getSubscriberData()
                ->getData();

        if (is_array($result) && $result[0]['subscriber']['STATUS'] == 1) {
            return $this->processServiceResult($this->_client->editSubscriber($this->getDataKey()));
        }

        if ($this->getData('inCron')) {
            return;
        }
        return $this->processServiceResult(array('error' => Mage::helper('fidelitas')->__('You can only edit a subscriber with the status "active".')));
    }

    public function removeSubscriber() {
        $result = Mage::getModel('fidelitas/egoi')
                ->setData('listID', $this->getData('listID'))
                ->setData('subscriber', $this->getSubscriber())
                ->getSubscriberData()
                ->getData();

        if (is_array($result) && $result[0]['subscriber']['STATUS'] != 2) {
            return $this->processServiceResult($this->_client->removeSubscriber($this->getDataKey()));
        }

        if ($this->getData('inCron')) {
            return;
        }
        return array('error' => Mage::helper('fidelitas')->__('Subscriber not found or action not allowed'));
    }

    public function createCampaignEmail() {
        $this->processServiceResult($this->_client->createCampaignEmail($this->getDataKey()));
        return $this;
    }

    public function createCampaignSms() {
        $this->processServiceResult($this->_client->createCampaignSMS($this->getDataKey()));
        return $this;
    }

    public function editCampaignEmail() {
        $this->processServiceResult($this->_client->editCampaignEmail($this->getDataKey()));
        return $this;
    }

    public function editCampaignSms() {
        $this->processServiceResult($this->_client->editCampaignSMS($this->getDataKey()));
        return $this;
    }

    public function deleteCampaign() {
        $this->processServiceResult($this->_client->deleteCampaign($this->getDataKey()));
        return $this;
    }

    public function getSenders() {
        $this->formatFields($this->_client->getSenders($this->getDataKey()));
        return $this;
    }

    public function checkLogin($apiKey = null) {

        $data = $this->getDataKey();
        if ($apiKey) {
            $data['apikey'] = $apiKey;
        }
        $this->processServiceResult($this->_client->checklogin($data));
        return $this;
    }

    public function sendSMS() {
        $this->processServiceResult($this->_client->sendSms($this->getDataKey()));
        return $this;
    }

    public function sendEmail() {
        $this->processServiceResult($this->_client->sendEmail($this->getDataKey()));
        return $this;
    }

    public function sync() {
        $account = Mage::getModel('fidelitas/account')->load(1);
        $key = Mage::getStoreConfig('fidelitas/config/api_key');

        if (!$key) {
            return;
        }

        $models = array('lists', 'subscribers', 'campaigns', 'account', 'senders');
        foreach ($models as $sync) {
            Mage::getModel('fidelitas/' . $sync)->cron();
        }

        $account->setData('cron', 0)->save();
    }

    public function syncm() {
        $account = Mage::getModel('fidelitas/account')->load(1);

        if ($account->getCron() == 3) {
            Mage::registry('fidelitas_first_run', true);
        }
        if ($account->getCron() == 1) {
            $account->setData('cron', 0)->save();
            Mage::getModel('fidelitas/egoi')->sync();
            Mage::getModel('fidelitas/lists')->getAdminList();
        }
    }

    public function createAccount() {
        $this->processServiceResult($this->_client->createAccount($this->getData()));
        return $this;
    }

}
