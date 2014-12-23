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
class Licentia_Fidelitas_Model_Autoresponders extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/autoresponders');
    }

    public function toOptionArray() {

        $return = array(
            'new_account' => Mage::helper('fidelitas')->__('Account - New'),
            'campaign_open' => Mage::helper('fidelitas')->__('Campaign - Open'),
            'campaign_click' => Mage::helper('fidelitas')->__('Campaign - Clicked Any Campaign Link'),
            'campaign_link' => Mage::helper('fidelitas')->__('Campaign - Clicked Specific Campaign Link'),
            'new_search' => Mage::helper('fidelitas')->__('Search - New'),
            'order_new' => Mage::helper('fidelitas')->__('Order - New Order'),
            'order_product' => Mage::helper('fidelitas')->__('Order - Bought Specific Product'),
            'order_status' => Mage::helper('fidelitas')->__('Order - Order Status Changes'),
            #'new_tag' => 'Tagged a Product',
            'new_review' => Mage::helper('fidelitas')->__('Product - New Review'),
            'new_review_self' => Mage::helper('fidelitas')->__('Product - New Review on a Bought Product'),
        );

        if (version_compare(Mage::getVersion(), '1.7') == -1) {
            unset($return['new_account']);
        }

        return $return;
    }

    public function changeStatus($event) {

        $order = $event->getEvent()->getOrder();
        $newStatus = $order->getData('status');
        $olderStatus = $order->getOrigData('status');

        if ($newStatus == $olderStatus) {
            return;
        }

        $email = $order->getCustomerEmail();

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', 'order_status')
                ->addFieldToFilter('order_status', $newStatus);

        if ($autoresponders->count() == 0)
            return;

        foreach ($autoresponders as $autoresponder) {

            $subscriber = $this->loadSubscriber($autoresponder, $email);

            if (!$subscriber) {
                break;
            }

            $this->_insertData($autoresponder, $subscriber);
        }
    }

    public function newSearch($event) {

        $query = Mage::helper('catalogsearch')->getQuery();

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        } else {
            return;
        }

        $email = $customer->getEmail();

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', 'new_search');

        if ($autoresponders->count() == 0) {
            return;
        }

        foreach ($autoresponders as $autoresponder) {

            $subscriber = $this->loadSubscriber($autoresponder, $email);

            if (!$subscriber) {
                break;
            }

            $search = explode(',', $autoresponder->getSearch());

            foreach ($search as $string) {

                if ($autoresponder->getSearchOption() == 'eq' && strtolower($query) == strtolower($string)) {
                    $this->_insertData($autoresponder, $subscriber);
                }

                if ($autoresponder->getSearchOption() == 'like' && stripos($query, $string) !== false) {
                    $this->_insertData($autoresponder, $subscriber);
                }
            }
        }
    }

    public function newCustomer($event) {

        $customer = $event->getEvent()->getCustomer();
        $email = $customer->getEmail();

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', 'new_account');

        if ($autoresponders->count() == 0)
            return;

        foreach ($autoresponders as $autoresponder) {

            $subscriber = $this->loadSubscriber($autoresponder, $email);

            if (!$subscriber) {
                break;
            }
            $this->_insertData($autoresponder, $subscriber);
        }
    }

    public function newReviewSelf($event) {
        $review = $event->getObject();
        $productId = $review->getProductId();
        $customerId = $review->getCustomerId();

        if (!$customerId) {
            return false;
        }

        $orders = Mage::getResourceModel('sales/order_collection')
                ->addFieldToFilter('customer_id', $customerId);

        $return = true;
        foreach ($orders as $order) {
            $items = $order->getAllItems();
            foreach ($items as $item) {
                if ($item->getProductId() == $productId) {
                    $return = false;
                    break 2;
                }
            }
        }

        if ($return) {
            return;
        }

        $customer = Mage::getModel('customer/customer')->load($customerId);
        $email = $customer->getEmail();

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', 'new_review_self');

        if ($autoresponders->count() == 0) {
            return;
        }

        foreach ($autoresponders as $autoresponder) {
            $subscriber = $this->loadSubscriber($autoresponder, $email);

            if (!$subscriber) {
                break;
            }
            $this->_insertData($autoresponder, $subscriber);
        }
    }

    public function newReview($event) {
        $review = $event->getObject();

        $customerId = $review->getCustomerId();

        if (!$customerId) {
            return false;
        }

        $customer = Mage::getModel('customer/customer')->load($customerId);
        $email = $customer->getEmail();

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', 'new_review');

        if ($autoresponders->count() == 0) {
            return;
        }

        foreach ($autoresponders as $autoresponder) {
            $subscriber = $this->loadSubscriber($autoresponder, $email);

            if (!$subscriber) {
                break;
            }
            $this->_insertData($autoresponder, $subscriber);
        }
    }

    public function newOrder($event) {

        $order = $event->getEvent()->getOrder();
        $email = $order->getCustomerEmail();

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', array('in' => array('order_product', 'order_new')));

        if ($autoresponders->count() == 0) {
            return;
        }

        foreach ($autoresponders as $autoresponder) {

            if ($autoresponder->getEvent() == 'order_product') {
                $items = $order->getAllItems();
                $ok = false;
                foreach ($items as $item) {
                    if ($item->getProductId() == $autoresponder->getProduct()) {
                        $ok = true;
                        break;
                    }
                }
                if ($ok === false) {
                    break;
                }
            }

            $subscriber = $this->loadSubscriber($autoresponder, $email);

            if (!$subscriber) {
                break;
            }

            $this->_insertData($autoresponder, $subscriber);
        }
    }

    public function newView($subscriber, $campaign) {

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', 'campaign_open')
                ->addFieldToFilter('campaign_id', $campaign->getId());

        if ($autoresponders->count() == 0)
            return;

        foreach ($autoresponders as $autoresponder) {
            $this->_insertData($autoresponder, $subscriber);
        }
    }

    public function newClick($subscriber, $campaign) {

        $autoresponders = $this->_getCollection()
                ->addFieldToFilter('event', array('in' => array('campaign_link', 'campaign_click')))
                ->addFieldToFilter('campaign_id', $campaign->getId());

        if ($autoresponders->count() == 0)
            return;

        foreach ($autoresponders as $autoresponder) {

            if ($autoresponder->getEvent() == 'campaign_link') {

                $linkOpen = Mage::registry('fidelitas_open_url');

                $links = Mage::getModel('fidelitas/links')
                        ->getCollection()
                        ->addFieldToFilter('link_id', $autoresponder->getLinkId());

                if ($links->count() != 1) {
                    break;
                }

                $link = $links->getFirstItem()->getLink();

                if (stripos($linkOpen, $link) === false) {
                    break;
                }
            }

            $this->_insertData($autoresponder, $subscriber);
        }
    }

    public function loadSubscriber($autoresponder, $email) {

        $subscribers = Mage::getModel('fidelitas/subscribers')->getCollection()
                ->addFieldToFilter('list', $autoresponder->getListnum())
                ->addFieldToFilter('email', $email);

        if ($subscribers->count() == 0) {

            $list = Mage::getModel('fidelitas/lists')->getListForStore(Mage::app()->getStore()->getId());

            if (!$list->getId()) {
                return;
            }

            $data['listID'] = $list->getListnum();
            $data['email'] = $email;
            $data['active'] = 1;
            try {
                return Mage::getModel('fidelitas/subscribers')->setData($data)->save();
            } catch (Exception $e) {
                Mage::logException($e);
                return false;
            }
        }

        $subscriber = false;
        if ($subscribers->count() > 0) {

            $subscriber = $subscribers->getFirstItem();

            if ($autoresponder->getSegmentId() > 0) {

                if ((int) $subscriber->getCustomerId() == 0) {
                    return false;
                }

                $ok = Mage::getModel('fidelitas/segments')
                        ->load($autoresponder->getSegmentId())
                        ->getMatchingCustomersIds($subscriber->getCustomerId());

                if (count($ok) != 1) {
                    return false;
                }
            }
        }

        return $subscriber;
    }

    public function calculateSendDate($autoresponder) {
        if ($autoresponder->getSendMoment() == 'occurs') {
            $date = Mage::app()->getLocale()->date()
                    ->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);
        }

        if ($autoresponder->getSendMoment() == 'after') {
            $date = Mage::app()->getLocale()->date();

            if ($autoresponder->getAfterHours() > 0) {
                $date->addHour($autoresponder->getAfterHours());
            }
            if ($autoresponder->getAfterDays() > 0) {
                $date->addDay($autoresponder->getAfterDays());
            }
            $date->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);
        }

        return $date;
    }

    public function send() {
        $date = Mage::getSingleton('core/date')->gmtdate();

        $emails = Mage::getModel('fidelitas/events')->getCollection()
                ->addFieldToFilter('sent', 0)
                ->addFieldToFilter('channel', 'email')
                ->addFieldToFilter('send_at', array('lteq' => $date));

        foreach ($emails as $cron) {

            $autoresponder = Mage::getModel('fidelitas/autoresponders')->load($cron->getAutoresponderId());
            $subscriber = Mage::getModel('fidelitas/subscribers')->load($cron->getSubscriberId());

            if (!$autoresponder->getId() || !$subscriber->getId()) {
                $cron->setSent(1)->save();
                continue;
            }

            $list = Mage::getModel('fidelitas/lists')->load($subscriber->getList(), 'listnum');
            $store = Mage::app()->getStore($list->getFinalStoreId());

            $data = array();
            $data['listnum'] = $subscriber->getList();
            $data['subject'] = $autoresponder->getSubject();
            $data['internal_name'] = $autoresponder->getName();
            $data['deploy_at'] = $cron->getSendAt();
            $data['message'] = $autoresponder->getMessage();
            $data['from'] = $autoresponder->getFrom();
            $data['url'] = $store->getBaseUrl();
            $data['recurring'] = '0';
            $data['auto'] = '1';
            $data['channel'] = 'email';
            $data['autoresponder'] = $autoresponder->getId();
            $data['autoresponder_recipient'] = $subscriber->getEmail();
            $data['autoresponder_event'] = $autoresponder->getEvent();
            $data['sent'] = 1;

            $egoi = Mage::getModel('fidelitas/egoi');
            $campaign = Mage::getModel('fidelitas/campaigns')->setData($data)->save();

            $url = $store->getBaseUrl() . '/fidelitas/campaign/user/uid/' . $subscriber->getData('uid') . '/c/' . $campaign->getHash() . '/?___store=' . $store->getCode();

            $campaign->setUrl($url)->save();

            $data = array();
            $data['campaign'] = $campaign->getHash();
            $data['email'] = $subscriber->getEmail();
            $data['fromID'] = $campaign->getFrom();
            $data['listID'] = $campaign->getListnum();
            $data['subject'] = $campaign->getSubject();



            $result = $egoi->setData($data)->sendEmail($data);
            if ($result->getData('id')) {
                Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);
            }

            $cron->setSent(1)->setSentAt($date)->save();
        }


        $smsCollection = Mage::getModel('fidelitas/events')->getCollection()
                ->addFieldToFilter('sent', 0)
                ->addFieldToFilter('channel', 'sms')
                ->addFieldToFilter('send_at', array('lteq' => $date));

        foreach ($smsCollection as $cron) {

            $autoresponder = Mage::getModel('fidelitas/autoresponders')->load($cron->getAutoresponderId());
            $subscriber = Mage::getModel('fidelitas/subscribers')->load($cron->getSubscriberId());

            if (!$autoresponder->getId() || !$subscriber->getId() || !$subscriber->getCellphone()) {
                $cron->setSent(1)->save();
                continue;
            }

            $data = array();
            $data['cellphone'] = $subscriber->getCellphone();
            $data['subject'] = $autoresponder->getSubject();
            $data['internal_name'] = $autoresponder->getName();
            $data['from'] = $autoresponder->getFrom();
            $data['listnum'] = $autoresponder->getListnum();
            $data['message'] = Mage::helper('cms')->getBlockTemplateProcessor()->filter($autoresponder->getMessage());
            $data['deploy_at'] = $cron->getSendAt();
            $data['recurring'] = '0';
            $data['auto'] = '1';
            $data['channel'] = 'SMS';
            $data['autoresponder'] = $autoresponder->getId();
            $data['autoresponder_recipient'] = $subscriber->getCellphone();
            $data['autoresponder_event'] = $autoresponder->getEvent();
            $data['sent'] = 1;

            $egoi = Mage::getModel('fidelitas/egoi');
            $campaign = Mage::getModel('fidelitas/campaigns')->setData($data)->save();

            $data = array();
            $data['campaign'] = $campaign->getHash();
            $data['subject'] = $campaign->getSubject();
            $data['cellphone'] = $subscriber->getCellphone();
            $data['fromID'] = $campaign->getFrom();
            $data['listID'] = $campaign->getListnum();
            try {
                $result = $egoi->setData($data)->sendSMS();
                if ($result->getData('id')) {
                    Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);
                    $cron->setSent(1)->setSentAt($date)->save();
                }
            } catch (Excepton $e) {
                Mage::logException($e);
            }
        }
    }

    protected function _insertData($autoresponder, $subscriber) {

        if ($autoresponder->getSendOnce() == 1) {
            $exists = Mage::getModel('fidelitas/events')->getCollection()
                    ->addFieldToFilter('autoresponder_id', $autoresponder->getId())
                    ->addFieldToFilter('subscriber_id', $subscriber->getId());

            if ($exists->count() != 0) {
                return;
            }
        }

        $data = array();
        $data['send_at'] = $this->calculateSendDate($autoresponder);
        $data['autoresponder_id'] = $autoresponder->getId();
        $data['customer_id'] = $subscriber->getCustomerId();
        $data['subscriber_id'] = $subscriber->getId();
        $data['subscriber_firstname'] = $subscriber->getFirstName();
        $data['subscriber_lastname'] = $subscriber->getLastName();
        $data['subscriber_email'] = $subscriber->getEmail();
        $data['subscriber_cellphone'] = $subscriber->getCellphone();
        $data['event'] = $autoresponder->getEvent();
        $data['created_at'] = new Zend_Db_Expr('NOW()');
        $data['sent'] = 0;
        $data['channel'] = $autoresponder->getChannel();

        Mage::getModel('fidelitas/events')->setData($data)->save();
        $autoresponder->setData('number_subscribers', $autoresponder->getData('number_subscribers') + 1)->save();
    }

    public function toFormValues() {
        $return = array();
        $collection = $this->getCollection()
                ->addFieldToSelect('name')
                ->addFieldToSelect('autoresponder_id')
                ->setOrder('name', 'ASC');
        foreach ($collection as $autoresponder) {
            $return[$autoresponder->getId()] = $autoresponder->getName() . ' (ID:' . $autoresponder->getId() . ')';
        }

        return $return;
    }

    protected function _getCollection() {

        $date = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);
        //Version Compatability
        $return = $this->getCollection()->addFieldToFilter('active', 1);
        $return->getSelect()
                ->where(" from_date <=? or from_date IS NULL ", $date)
                ->where(" to_date >=? or to_date IS NULL ", $date);

        return $return;
    }

    public function _beforeSave() {
        $this->setSendAt(Mage::getModel('core/date')->gmtDate(null, $this->getSendAt()));
        parent::_beforeSave();
    }

}
