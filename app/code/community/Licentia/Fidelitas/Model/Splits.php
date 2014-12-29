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
class Licentia_Fidelitas_Model_Splits extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/splits');
    }

    public function getSubscribersForTest($split) {

        $collectionTemp = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToSelect('subscriber_id')
                ->addFieldToFilter('list', $split->getListnum());

        $total = $collectionTemp->count();
        $limit = round($total * $split->getPercentage() / 100);

        unset($collectionTemp);

        $collection = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToSelect('subscriber_id')
                ->addFieldToFilter('list', $split->getListnum());

        if ($split->getSegmentId() > 0) {
            Mage::getModel('fidelitas/segments_list')->loadList($split->getSegmentId(), $split->getListnum());

            $subs = Mage::getModel('fidelitas/segments_list')->getCollection()
                    ->addFieldToFilter('listnum', $split->getListnum())
                    ->addFieldToFilter('segment_id', $split->getSegmentId())
                    ->getAllIds('subscriber_id');

            $collection->addFieldToFilter('subscriber_id', array('in' => $subs));
        }

        $collection->setPageSize($limit)
                ->setOrder('subscriber_id', 'ASC');

        $final = array();

        $i = 1;
        foreach ($collection as $subscriber) {
            $a = $i > round($limit / 2) ? 0 : 1;
            $final[$a][] = $subscriber->getId();
            $i++;
        }

        $split->setData('last_subscriber_id', end($final[1]))->save();

        return $final;
    }

    public function getSubscribersForGeneral($split) {

        $collection = Mage::getModel('fidelitas/subscribers')->getCollection()
                ->addFieldToFilter('list', $split->getListnum())
                ->addFieldToFilter('subscriber_id', array('gt' => $split->getLastSubscriberId()));

        if ($split->getSegmentId() > 0) {
            Mage::getModel('fidelitas/segments_list')->loadList($split->getSegmentId(), $split->getListnum());

            $subs = Mage::getModel('fidelitas/segments_list')->getCollection()
                    ->addFieldToFilter('listnum', $split->getListnum())
                    ->addFieldToFilter('segment_id', $split->getSegmentId())
                    ->getAllIds('subscriber_id');

            $collection->addFieldToFilter('subscriber_id', array('in' => $subs));
        }


        return $collection->getAllIds('email');
    }

    public function cron() {
        $date = Mage::getSingleton('core/date')->gmtdate();

        $collectionPercentage = Mage::getModel('fidelitas/splits')
                ->getCollection()
                ->addFieldToFilter('sent', 0)
                ->addFieldToFilter('active', 1)
                ->addFieldToFilter('deploy_at', array('lteq' => $date));


        foreach ($collectionPercentage as $split) {
            $recipients = array();
            $subscribers = $this->getSubscribersForTest($split);

            $recipients['a'] = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('email')
                    ->addFieldToFilter('subscriber_id', array('in' => $subscribers[0]))
                    ->getAllIds('email');

            $recipients['b'] = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('email')
                    ->addFieldToFilter('subscriber_id', array('in' => $subscribers[1]))
                    ->getAllIds('email');

            foreach (array('a', 'b') as $version) {
                $this->_sendCampaignData($split, $version, $recipients[$version]);
            }

            $split->setData('sent', 1)
                    ->setData('recipients_a', implode(',', $recipients['a']))
                    ->setData('recipients_b', implode(',', $recipients['b']))
                    ->save();
        }

        $collectionGeneral = Mage::getModel('fidelitas/splits')
                ->getCollection()
                ->addFieldToFilter('sent', 1)
                ->addFieldToFilter('closed', 0)
                ->addFieldToFilter('active', 1)
                ->addFieldToFilter('winner', array('neq' => 'manually'))
                ->addFieldToFilter('send_at', array('lteq' => $date));


        foreach ($collectionGeneral as $split) {
            $subscribers = $this->getSubscribersForGeneral($split);

            $winner = $split->getData('winner');

            if (
                    ($winner == 'views' && $split->getData('views_a') >= $split->getData('views_b')) ||
                    ($winner == 'clicks' && $split->getData('clicks_a') >= $split->getData('clicks_b')) ||
                    ($winner == 'conversions' && $split->getData('conversions_a') >= $split->getData('conversions_b'))
            ) {
                $version = 'a';
            } else {
                $version = 'b';
            }

            $this->_sendCampaignData($split, $version, $subscribers, true);

            $split->setData('closed', 1)->setData('active', 0)->setData('recipients', implode(',', $subscribers))->save();
        }

        return true;
    }

    public function sendManually($split, $version) {
        $subscribers = $this->getSubscribersForGeneral($split);
        $this->_sendCampaignData($split, $version, $subscribers, true);
        $split->setData('closed', 1)->setData('recipients', implode(',', $subscribers))->save();
    }

    protected function _sendCampaignData($split, $version, $recipients, $final = false) {

        $list = Mage::getModel('fidelitas/lists')->load($split->getListnum(), 'listnum');
        $store = Mage::app()->getStore($list->getFinalStoreId());

        $data = array();
        $data['listnum'] = $split->getListnum();
        $data['subject'] = $split->getData('subject_' . $version);
        $data['internal_name'] = '[ST] ' . $split->getName();
        $data['deploy_at'] = $split->getDeployAt();
        $data['message'] = $split->getData('message_' . $version);
        $data['from'] = $split->getData('sender_' . $version);
        $data['url'] = $store->getBaseUrl();
        $data['recurring'] = '0';
        $data['auto'] = ($final) ? 0 : 1;
        $data['channel'] = 'email';
        $data['split_id'] = $split->getId();
        $data['split_version'] = $version;
        $data['split_final'] = ($final) ? 1 : 0;

        $egoi = Mage::getModel('fidelitas/egoi');
        $newCampaign = Mage::getModel('fidelitas/campaigns')->setData($data)->save();

        $url = $store->getBaseUrl() . '/fidelitas/campaign/view/c/' . $newCampaign->getHash() . '/?___store=' . $store->getCode();
        $newCampaign->setUrl($url)->save();

        $dataSend = array();
        $dataSend['campaign'] = $newCampaign->getHash();
        $dataSend['email'] = $recipients;
        $dataSend['fromID'] = $newCampaign->getFrom();
        $dataSend['listID'] = $newCampaign->getListnum();
        $dataSend['subject'] = $newCampaign->getSubject();

        $result = $egoi->setData($dataSend)->sendEmail();
        if ($result->getData('id')) {
            Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($newCampaign);
        }

        if ($final == 1) {
            $this->updateStatsForMainSplit($split, $newCampaign);
        }

        return $newCampaign;
    }

    public function updateStatsForMainSplit($split, $campaign) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tables = array('conversions', 'stats', 'consegments', 'coupons');

        $campaigns = Mage::getModel('fidelitas/campaigns')
                ->getCollection()
                ->addFieldToFilter('split_id', $split->getId())
                ->addFieldToFilter('split_final', 0);

        foreach ($campaigns as $record) {
            foreach ($tables as $table) {
                $tablename = Mage::getSingleton('core/resource')->getTableName('fidelitas/' . $table);
                $write->update($tablename, array('campaign_id' => $campaign->getId()), array('campaign_id = ?' => $record->getId()));
            }

            $campaign->setData('clicks', $record->getData('clicks') + $campaign->getData('clicks'));
            $campaign->setData('unique_clicks', $record->getData('unique_clicks') + $campaign->getData('unique_clicks'));
            $campaign->setData('views', $record->getData('views') + $campaign->getData('views'));
            $campaign->setData('unique_views', $record->getData('unique_views') + $campaign->getData('unique_views'));

            $campaign->save();
        }
    }

    public function getFinalCampaign($split, $field = false) {

        if ($split->getClosed() == 0)
            return false;

        $campaigns = Mage::getModel('fidelitas/campaigns')
                ->getCollection()
                ->addFieldToFilter('split_id', $split->getId())
                ->addFieldToFilter('split_final', 1);

        if ($field) {
            $campaigns->addFieldToSelect($field);
        }

        if ($campaigns->count() == 1) {
            return $campaigns->getFirstItem();
        }
        return new Varien_Object;
    }

    public function getWinnerOptions() {
        return array('views' => Mage::helper('fidelitas')->__('Views'),
            'clicks' => Mage::helper('fidelitas')->__('Clicks'),
            'conversions' => Mage::helper('fidelitas')->__('Conversions'),
            'manually' => Mage::helper('fidelitas')->__('Manually'));
    }

    public function _beforeSave() {
        $this->setDeployAt(Mage::getModel('core/date')->gmtDate(null, $this->getDeployAt()));
        parent::_beforeSave();
    }

}
