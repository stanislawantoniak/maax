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
class Licentia_Fidelitas_Model_Lists extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/lists');
    }

    /**
     * Syncs local data with remote E-Goi
     */
    function cron() {

        $fid = Mage::getModel('fidelitas/egoi');
        $lists = $fid->getLists()->getData();

        $locaLists = Mage::getModel('fidelitas/lists')->getCollection();


        $remoteListsIds = array();
        foreach ($lists as $list) {
            $jaExiste = Mage::getModel('fidelitas/lists')->load($list['listnum'], 'listnum');
            if (!$jaExiste->getId())
                continue;

            $list['list_id'] = $jaExiste->getId();
            $list['listID'] = $jaExiste->getId();
            $list['internal_name'] = $list['title_ref'];
            $list['nome'] = $list['title'];
            $list['subs_activos'] = $list['subs_activos'];
            $list['subs_total'] = $list['subs_total'];

            $remoteListsIds[] = $list['listnum'];

            Mage::getModel('fidelitas/lists')->setData($list)->setData('inCron', true)->save();
        }

        //Let's delete lists that where removed from the e-goi servers
        foreach ($locaLists as $list) {
            if (!in_array($list->getListnum(), $remoteListsIds)) {
                Mage::getModel('fidelitas/lists')->load($list->getId())->delete();
            }
        }
    }

    public function getOptionArray($storeId = null, $client = true) {

        $lists = Mage::getModel('fidelitas/lists')
                ->getCollection();

        if (!$client) {
            $lists->addFieldToFilter('purpose', array('neq' => array('client')));
        }

        if (!is_null($storeId))
            $lists->addFieldToFilter('store_id', $storeId);


        $lists->getData();

        $return = array();
        foreach ($lists as $list) {
            $return[$list['listnum']] = $list['title'];
        }

        return $return;
    }

    public function getOptionArrayId($storeId = null, $client = true) {

        $lists = Mage::getModel('fidelitas/lists')
                ->getCollection();

        if ($client) {
            $lists->addFieldToFilter('purpose', array('neq' => array('client')));
        }

        if (!is_null($storeId))
            $lists->addFieldToFilter('store_id', $storeId);


        $lists->getData();

        $return = array();
        foreach ($lists as $list) {
            $return[$list['list_id']] = $list['title'];
        }

        return $return;
    }

    public function getAllOptions($storeId = null, $all = false) {
        $res = array();
        if ($all) {
            $res[] = array(
                'value' => '',
                'label' => Mage::helper('fidelitas')->__('--' . $all . '--')
            );
        }
        foreach (self::getOptionArray($storeId) as $index => $value) {
            $res[] = array(
                'value' => $index,
                'label' => $value
            );
        }
        return $res;
    }

    public function save() {

        $storeIds = $this->getData('store_ids');

        $this->setData('store_ids', false);

        if ($this->getData('inCron') === true && !Mage::registry('fidelitas_first_run')) {
            return parent::save();
        }

        $model = Mage::getModel('fidelitas/egoi');
        $data = $this->getData();
        $id = $this->getId();

        $this->setData('canal_email', '1');
        $this->setData('canal_sms', '1');

        if (!$this->getData('listID') && $this->getData('listnum')) {
            $this->setData('listID', $this->getData('listnum'));
            $data['listID'] = $this->getData('listnum');
        }

        if ($id) {

            if (isset($data['nome'])) {
                $data['name'] = $data['nome'];
            }
            $data['title'] = $data['nome'];
            if (isset($data['nome'])) {
                $this->setData('title', $data['nome']);
            }
            $model->addData($data);
            $model->updateList($data);
        } else {

            if (!$this->getData('listnum')) {
                $model->setData($data);
                $model->createList();
                $this->setData('listnum', $model->getData('list_id'));
            }
            $this->setData('title', $data['nome']);
        }

        $parent = parent::save();


        foreach ($storeIds as $storeId) {

            $lData = array();
            $lData['list_id'] = $parent->getId();
            $lData['store_id'] = $storeId;

            $exists = Mage::getModel('fidelitas/lstores')->load($storeId, 'store_id');
            if ($exists->getId()) {
                continue;
            }

            Mage::getModel('fidelitas/lstores')->setData($lData)->save();
        }

        return $parent;
    }

    public function _afterSave() {

        $this->updateCallback();

        return parent::_afterSave();
    }

    public function updateCallback($id = null) {

        if ($id) {
            $list = $this->load($id);
        } else {
            $list = $this;
        }

        $store = Mage::app()->getStore();
        $url = $store->getBaseUrl() . 'fidelitas/callback/';

        $callback = array();
        $callback['listID'] = $list->hasListnum() ? $list->getData('listnum') : $list->getData('listID');
        $callback['callback_url'] = $url;

        $callback['notif_api_1'] = 1;
        $callback['notif_api_2'] = 1;
        $callback['notif_api_3'] = 1;
        $callback['notif_api_7'] = 1;
        $callback['notif_api_8'] = 1;
        $callback['notif_api_9'] = 1;
        $callback['notif_api_15'] = 1;

        Mage::getModel('fidelitas/egoi')->setData($callback)->editApiCallback();
    }

    /**
     * Returns the admin lists list
     *
     * @return $this
     */
    public function getAdminList() {
        $list = Mage::getModel('fidelitas/lists')->load('admin', 'purpose');
        if (!$list->getId()) {
            $data = array();
            $data['nome'] = 'Internal Notifications';
            $data['internal_name'] = 'Used For Configuration and Notifications';
            $data['purpose'] = 'admin';
            $data['store_id'] = '0';
            $list = Mage::getModel('fidelitas/lists')->setData($data)->save();
        }

        return $list;
    }

    /**
     * Returns the client list.
     *
     * @return $this
     */
    public function getClientList() {

        if(!Mage::getStoreConfig('fidelitas/config/customer_list')) {
            return false;
        }

        $list = Mage::getModel('fidelitas/lists')->load('client', 'purpose');

        if (!$list->getId()) {
            $data = array();
            $data['nome'] = 'Customer List';
            $data['name'] = 'Customer List';
            $data['internal_name'] = Mage::helper('fidelitas')->__('List with all your customers');
            $data['purpose'] = 'client';
            $data['store_id'] = '0';
            $list = Mage::getModel('fidelitas/lists')->setData($data)->save();
        }

        return $list;
    }

    public function getDefaultList() {
        $collection = $this->getCollection()->addFieldToFilter('is_default', '1');

        if ($collection->count() != 1) {
            return false;
        }

        return $collection->getFirstItem();
    }

    public function getListForStore($storeId) {

        if ($storeId == 0) {
            $default = $this->getDefaultList();

            if ($default) {
                return $default;
            }
            return new Varien_Object;
        }
        $lStores = Mage::getModel('fidelitas/lstores')->load($storeId, 'store_id');

        if (!$lStores->getId()) {
            $lists = Mage::getModel('fidelitas/lists')
                    ->getCollection()
                    ->addFieldToFilter('purpose', 'regular');

            if ($lists->count() == 0) {
                return new Varien_Object;
            }

            $list = $lists->getFirstItem();

            $data = array();
            $data['store_id'] = $storeId;
            $data['list_id'] = $list->getId();

            Mage::getModel('fidelitas/lstores')->setData($data)->save();

            return $list;
        } else {
            return Mage::getModel('fidelitas/lists')->load($lStores->getListId());
        }
    }

    public function getAvailableStores($listId = null) {

        $lStores = Mage::getModel('fidelitas/lstores')
                ->getCollection();

        if ($listId > 0) {
            $lStores->addFieldToFilter('list_id', array('neq' => $listId));
        }

        $result = $lStores->getAllIds('store_id');

        $nin = array_flip($result);

        $stores = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash();

        $return = array();
        foreach ($stores as $key => $store) {

            if (array_key_exists($key, $nin)) {
                continue;
            }

            $return[] = array('label' => $store, 'value' => $key);
        }

        return $return;
    }

    public function getStoresNames($id = false) {

        if ($id) {
            $stores = $this->load($id);
            $storesIds = $stores->getStoreIds();
        } else {
            $storesIds = $this->getStoreIds();
        }

        $storesIds = explode(',', $storesIds);

        $name = array();
        foreach ($storesIds as $store) {
            if ($store == 0) {
                continue;
            }

            $name[] = Mage::getSingleton('adminhtml/system_store')->getStoreNameWithWebsite($store);
        }

        return $name;
    }

    public function load($id, $field = null) {

        $list = parent::load($id, $field);

        $lStores = Mage::getModel('fidelitas/lstores')->getCollection()
                ->addFieldToFilter('list_id', $id)
                ->getAllIds('store_id');

        $list->setData('store_ids', implode(',', $lStores));

        return $list;
    }

    public function getStoreIdsArray() {

        if (!$this->getData('store_ids')) {
            $lStores = Mage::getModel('fidelitas/lstores')->getCollection()
                    ->addFieldToFilter('list_id', $this->getId())
                    ->getAllIds('store_id');

            $this->setData('store_ids', implode(',', $lStores));
        }

        return explode(',', $this->getData('store_ids'));
    }

    public function getFinalStoreId($listId = null) {

        if ($listId === null) {
            $listId = $this->getId();
        }

        $lStore = Mage::getModel('fidelitas/lstores')->getCollection()
                ->addFieldToFilter('list_id', $listId);

        if ($lStore->count() == 0) {
            return $this->getStoreId();
        }

        return $lStore->getFirstItem()->getStoreId();
    }

}
