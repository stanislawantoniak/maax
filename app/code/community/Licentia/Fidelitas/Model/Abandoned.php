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
class Licentia_Fidelitas_Model_Abandoned extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/abandoned');
    }

    public function send() {

        $date = Mage::getSingleton('core/date')->gmtdate();

        $emailsAbandoned = Mage::getModel('fidelitas/abandoned')->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('channel', 'email');
        $emailsAbandoned->getSelect()
                ->where(" from_date <=? or from_date IS NULL ", $date)
                ->where(" to_date >=? or to_date IS NULL ", $date);

        $list = Mage::getModel('fidelitas/lists')->getClientList();
        $store = Mage::app()->getStore($list->getFinalStoreId());

        foreach ($emailsAbandoned as $abandoned) {

            $collection = $this->_getCollection($abandoned);

            $emails = array();
            foreach ($collection as $emailCollection) {

                $email = $emailCollection->getCustomerEmail();

                if (strlen($email) < 6) {
                    continue;
                }

                $alreadySent = Mage::getModel('fidelitas/abandonedlog')->getCollection()
                        ->addFieldToFilter('abandoned_id', $abandoned->getId())
                        ->addFieldToFilter('subscriber', $email);

                if ($alreadySent->count() > 0) {
                    continue;
                }

                $emails[] = $email;

                Mage::getModel('fidelitas/abandonedlog')
                        ->setData(array('subscriber' => $email, 'abandoned_id' => $abandoned->getId()))
                        ->save();
            }

            if (count($emails) == 0) {
                continue;
            }

            $data = array();
            $data['listnum'] = $list->getListnum();
            $data['subject'] = $abandoned->getSubject();
            $data['internal_name'] = $abandoned->getName();
            $data['deploy_at'] = now(true);
            $data['message'] = $abandoned->getMessage();
            $data['from'] = $abandoned->getFrom();
            $data['url'] = $store->getBaseUrl();
            $data['recurring'] = '0';
            $data['auto'] = '1';
            $data['channel'] = 'email';
            $data['sent'] = 1;

            $egoi = Mage::getModel('fidelitas/egoi');
            $campaign = Mage::getModel('fidelitas/campaigns')->setData($data)->save();

            $updateUrlData = $campaign->getData();
            $url = $store->getBaseUrl() . 'fidelitas/campaign/view/c/' . $campaign->getHash() . '/?___store=' . $store->getCode();
            $updateUrlData['url'] = $url;
            Mage::getModel('fidelitas/campaigns')->setData($updateUrlData)->save();

            $dataEmail = array();
            $dataEmail['campaign'] = $campaign->getHash();
            $dataEmail['email'] = $emails;
            $dataEmail['fromID'] = $campaign->getFrom();
            $dataEmail['listID'] = $campaign->getListnum();
            $dataEmail['subject'] = $campaign->getSubject();

            $result = $egoi->setData($dataEmail)->sendEmail();
            if ($result->getData('id')) {
                Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);
            }

            $abandoned->setData('sent_number', $abandoned->getData('sent_number') + count($emails))->save();
        }



        $smsAbandoned = Mage::getModel('fidelitas/abandoned')->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('channel', 'sms');
        $smsAbandoned->getSelect()
                ->where(" from_date <=? or from_date IS NULL ", $date)
                ->where(" to_date >=? or to_date IS NULL ", $date);

        foreach ($smsAbandoned as $abandoned) {

            $collection = $this->_getCollection($abandoned);

            $numbers = array();
            $cellphoneField = Mage::getStoreConfig('fidelitas/config/cellphone');
            foreach ($collection as $sms) {
                $billing = $sms->getBillingAddress();
                $prefix = Mage::getModel('fidelitas/subscribers')->getPrefixForCountry($billing->getCountryId());
                $number = preg_replace('/\D/', '', $billing->getData($cellphoneField));
                $number = ltrim(ltrim($number, $prefix), 0);
                $smsNumber = $prefix . '-' . $number;

                if (strlen($smsNumber) < 6) {
                    continue;
                }

                $alreadySent = Mage::getModel('fidelitas/abandonedlog')->getCollection()
                        ->addFieldToFilter('abandoned_id', $abandoned->getId())
                        ->addFieldToFilter('subscriber', $smsNumber);

                if ($alreadySent->count() > 0) {
                    continue;
                }


                $numbers[] = $smsNumber;

                Mage::getModel('fidelitas/abandonedlog')
                        ->setData(array('subscriber' => $smsNumber, 'abandoned_id' => $abandoned->getId()))
                        ->save();
            }

            if (count($numbers) == 0) {
                continue;
            }

            $data = array();
            $data['cellphone'] = $numbers;
            $data['subject'] = $abandoned->getSubject();
            $data['internal_name'] = $abandoned->getName();
            $data['from'] = $abandoned->getFrom();
            $data['listnum'] = $list->getListnum();
            $data['message'] = Mage::helper('cms')->getBlockTemplateProcessor()->filter($abandoned->getMessage());
            $data['deploy_at'] = now(true);
            $data['recurring'] = '0';
            $data['auto'] = '1';
            $data['channel'] = 'SMS';
            $data['sent'] = 1;

            $egoi = Mage::getModel('fidelitas/egoi');
            $campaign = Mage::getModel('fidelitas/campaigns')->setData($data)->save();

            $dataSend = array();
            $dataSend['campaign'] = $campaign->getHash();
            $dataSend['cellphone'] = $numbers;
            $dataSend['fromID'] = $campaign->getFrom();
            $dataSend['listID'] = $campaign->getListnum();

            $result = $egoi->setData($dataSend)->sendSMS();
            if ($result->getData('id')) {
                Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);
            }
            $abandoned->setData('sent_number', $abandoned->getData('sent_number') + count($numbers))->save();
        }
    }

    protected function _getCollection($abandoned) {

        $date = Mage::getSingleton('core/date')->gmtdate();

        $real = Mage::app()
                ->getLocale()
                ->date($date)
                ->subDay($abandoned->getData('days'))
                ->subHour($abandoned->getData('hours'))
                ->subMinute($abandoned->getData('minutes'))
                ->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);

        $stores = explode(',', $abandoned->getData('stores'));
        $groups = explode(',', $abandoned->getData('groups'));

        $collection = Mage::getResourceModel('reports/quote_collection');
        $collection->addFieldToFilter('items_count', array('neq' => '0'))
                ->addFieldToFilter('main_table.is_active', '1')
                ->setOrder('updated_at');

        $collection->addFieldToFilter('store_id', array('in' => $stores));
        $collection->addFieldToFilter('customer_group_id', array('in' => $groups));
        $collection->addFieldToFilter('updated_at', array('lt' => $real));

        return $collection;
    }

}
