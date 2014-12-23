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
class Licentia_Fidelitas_Model_Segments extends Mage_Rule_Model_Rule {

    protected $_eventPrefix = 'fidelitas_segments';
    protected $_eventObject = 'rule';
    protected $_customersIds;
    protected $_customersEmails;

    protected function _construct() {

        $this->_init('fidelitas/segments');
    }

    public function getOptionArray() {

        $lists = Mage::getModel('fidelitas/segments')
                ->getCollection()
                ->addFieldToSelect('segment_id')
                ->addFieldToSelect('name')
                ->addFieldToFilter('is_active', 1);

        $return = array();
        $return[] = array('value' => '0', 'label' => Mage::helper('fidelitas')->__("-- None --"));

        foreach ($lists as $list) {
            $return[] = array('value' => $list->getId(), 'label' => $list->getName());
        }

        return $return;
    }

    public function getConditionsInstance() {
        return Mage::getModel('fidelitas/segments_condition_combine');
    }

    public function getActionsInstance() {
        return Mage::getModel('fidelitas/segments_action_collection');
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingCustomersIds($customerIdToSegment = false) {


        if (is_null($this->_customersEmails)) {

            $this->_customersIds = array();
            $this->setCollectedAttributes(array());
            Mage::register('current_segment', $this, true);

            $customerCollection = Mage::getResourceModel('customer/customer_collection');
            $list = Mage::registry('current_list');

            if ($this->getType() == 'customers') {
                if (!$customerIdToSegment) {
                    $subscribers = Mage::getModel('fidelitas/subscribers')
                            ->getCollection()
                            ->addFieldToSelect('customer_id')
                            ->addFieldToFilter('status', 1)
                            ->addFieldToFilter('list', $list->getListnum())
                            ->addFieldToFilter('customer_id', array('gt' => 0));

                    $subs = array();
                    foreach ($subscribers as $sub) {
                        $subs[] = $sub->getCustomerId();
                    }

                    $customerCollection->addAttributeToFilter('entity_id', array('in' => $subs));
                } else {
                    $customerCollection->addAttributeToFilter('entity_id', $customerIdToSegment);
                }


                Mage::getSingleton('core/resource_iterator')->walk(
                        $customerCollection->getSelect(), array(array($this, 'callbackValidateCustomer')), array(
                    'attributes' => $this->getCollectedAttributes(),
                    'customer' => Mage::getModel('customer/customer'),
                        )
                );
            }

            $this->getConditions()->collectValidatedAttributes($customerCollection);

            if ($this->getType() != 'customers') {

                $websites = Mage::app()->getWebsites();

                $ordersCollection = Mage::getModel('sales/order')
                        ->getCollection()
                        ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                        ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()));

                if ($this->getType() == 'visitors') {
                    $ordersCollection->addAttributeToFilter('customer_is_guest', 1);
                }

                $quoteCollection = Mage::getModel('sales/quote')
                        ->getCollection()
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('store_id', array('in' => $list->getStoreIdsArray()));

                if ($this->getType() == 'visitors') {
                    $quoteCollection->addFieldToFilter('checkout_method', 'guest');
                }

                Mage::getSingleton('core/resource_iterator')->walk(
                        $ordersCollection->getSelect(), array(array($this, 'callbackValidateCustomer')), array(
                    'attributes' => $this->getCollectedAttributes(),
                    'customer' => Mage::getModel('sales/order'),
                        )
                );

                Mage::getSingleton('core/resource_iterator')->walk(
                        $quoteCollection->getSelect(), array(array($this, 'callbackValidateCustomer')), array(
                    'attributes' => $this->getCollectedAttributes(),
                    'customer' => Mage::getModel('sales/quote'),
                        )
                );
            }

            $this->_customersEmails = array_unique($this->_customersEmails);
            foreach ($this->_customersIds as $email => $customerId) {

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                if (array_key_exists('customer_id', $customerId)) {
                    foreach ($websites as $site) {
                        $cInfo = Mage::getModel('customer/customer')->setWebsiteId($site->getId())->loadByEmail($email);
                        $data = $cInfo->getData();
                        if (count($data) > 0) {
                            break;
                        }
                    }
                } else {
                    $cInfo = Mage::getModel('customer/customer')->load($customerId['entity_id']);
                    $data = $cInfo->getData();
                }

                $data['segment_id'] = $this->getId();

                if (array_key_exists('customer_id', $customerId)) {
                    $data['customer_id'] = null;
                    $data['email'] = $customerId['customer_email'];
                    $data['first_name'] = $customerId['customer_firstname'];
                    $data['last_name'] = $customerId['customer_lastname'];
                } else {
                    $addr = $cInfo->getDefaultBillingAddress();

                    $data['customer_id'] = $customerId['entity_id'];
                    $data['email'] = $cInfo->getEmail();
                    $data['first_name'] = $cInfo->getFirstname();
                    $data['last_name'] = $cInfo->getLastname();

                    if (is_object($addr)) {
                        $cellphoneField = Mage::getStoreConfig('fidelitas/config/cellphone');
                        $data['cellphone'] = Mage::getModel('fidelitas/subscribers')->getPrefixForCountry($addr->getCountryId()) . '-' . preg_replace('/\D/', '', $addr->getData($cellphoneField));
                    }
                }

                $data['list_id'] = $list->getId();
                $data['listnum'] = $list->getListnum();

                $subs = Mage::getModel('fidelitas/subscribers')->subscriberExists('email', $data['email'], $data['listnum']);

                if ($subs) {
                    $data['subscriber_id'] = $subs->getId();
                } else {

                    $existsInListKey = array_search($email,$this->_customersEmails);
                    if($existsInListKey !== false){
                        unset($this->_customersEmails[$existsInListKey]);
                        unset($this->_customersIds[$email]);
                    }

                    continue;
                }


                if (!$customerIdToSegment) {
                    Mage::getModel('fidelitas/segments_list')->saveRecord($data);
                }
            }
        }

        return $this->_customersEmails;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     * @return void
     */
    public function callbackValidateCustomer($args) {
        $customer = clone $args['customer'];
        $customer->setData($args['row']);

        if ($this->getConditions()->validate($customer)) {
            $email = $customer->getData('email') ? $customer->getData('email') : $customer->getData('customer_email');
            $this->_customersEmails[] = $email;
            $this->_customersIds[$email] = $customer->getData();
        }
    }

    /**
     * Get array of assigned customer group ids
     *
     * @return array
     */
    public function getCustomerGroupIds() {
        $ids = $this->getData('customer_group_ids');
        if (($ids && !$this->getCustomerGroupChecked()) || is_string($ids)) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $groupIds = Mage::getModel('customer/group')->getCollection()->getAllIds();
            $ids = array_intersect($ids, $groupIds);
            $this->setData('customer_group_ids', $ids);
            $this->setCustomerGroupChecked(true);
        }
        return $ids;
    }

    /**
     * Returns a list of segments IDS and internal name
     * @return type
     */
    public function toFormValues() {
        $return = array();
        $collection = $this->getCollection()
                ->addFieldToSelect('segment_id')
                ->addFieldToSelect('name');

        foreach ($collection as $segment) {
            $return[$segment->getId()] = $segment->getName();
        }

        return $return;
    }

    public function buildUser() {
        $segments = $this->getCollection()
                ->addFieldToFilter('build', 1);

        foreach ($segments as $segment) {
            Mage::getModel('fidelitas/segments')->load($segment->getId())->setData('build', 2)->save();
            Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
            Mage::getModel('fidelitas/segments')->load($segment->getId())->setData('build', 0)->save();
        }
    }

    public function cron() {

        $date = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);

        $segments = $this->getCollection()
                ->addFieldToFilter('cron', array('neq' => '0'));

        //Version Compatability
        $segments->getSelect()
                ->where(" cron_last_run <? or cron_last_run IS NULL ", $date);


        foreach ($segments as $segment) {

            if ($segment->getCron() == 'd') {
                Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
                Mage::getModel('fidelitas/segments')->load($segment->getId())
                        ->setData('cron_last_run', $date)
                        ->setData('last_update', $date)
                        ->save();
            }

            if ($segment->getCron() == 'w' && $date->get('e') == 1) {
                Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
                Mage::getModel('fidelitas/segments')->load($segment->getId())
                        ->setData('cron_last_run', $date)
                        ->setData('last_update', $date)
                        ->save();
            }

            if ($segment->getCron() == 'm' && $date->get('d') == 1) {
                Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
                Mage::getModel('fidelitas/segments')->load($segment->getId())
                        ->setData('cron_last_run', $date)
                        ->setData('last_update', $date)
                        ->save();
            }
        }
    }

    public function _beforeSave() {

        if (!$this->getData('controller')) {
            return;
        }
        return parent::_beforeSave();
    }

    public function getStoreIds() {
        $websiteIds = explode(',', $this->getData('websites_ids'));

        $storeIds = array();
        foreach ($websiteIds as $websiteId) {
            $storeIds = array_merge($storeIds, Mage::app()->getWebsite($websiteId)->getStoreIds());
        }

        return $storeIds;
    }

}
