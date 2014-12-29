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
class Licentia_Fidelitas_Model_Followup extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('fidelitas/followup');
    }

    public function getOptionValues() {

        $options = array();
        $options[] = array('value' => 'no_open', 'label' => Mage::helper('fidelitas')->__("Didn't Open the Message"));
        $options[] = array('value' => 'open', 'label' => Mage::helper('fidelitas')->__("Open the Message"));
        $options[] = array('value' => 'no_click', 'label' => Mage::helper('fidelitas')->__("Didn't click the Message"));
        $options[] = array('value' => 'click', 'label' => Mage::helper('fidelitas')->__("Click the Message"));
        $options[] = array('value' => 'no_conversion', 'label' => Mage::helper('fidelitas')->__("No Conversion"));
        $options[] = array('value' => 'conversion', 'label' => Mage::helper('fidelitas')->__("Converted"));

        return $options;
    }

    public function cron() {

        $date = Mage::getSingleton('core/date')->gmtdate();

        $followups = Mage::getModel('fidelitas/followup')
                ->getCollection()
                ->addFieldToFilter('send_at', array('lteq' => $date))
                ->addFieldToFilter('sent', 0)
                ->addFieldToFilter('active', 1);

        foreach ($followups as $followup) {
            $campaign = Mage::getModel('fidelitas/campaigns')->load($followup->getCampaignId());
            $list = Mage::getModel('fidelitas/lists')->load($campaign->getListnum(), 'listnum');

            $recipients = $this->buildRecipients($followup, $campaign);

            $subscribers = Mage::getModel('fidelitas/subscribers')
                    ->getCollection();

            if (count($recipients['include']) > 0) {
                $subscribers->addFieldToFilter('subscriber_id', array('in' => $recipients['include']));
            }
            if (count($recipients['exclude']) > 0) {
                $subscribers->addFieldToFilter('subscriber_id', array('nin' => $recipients['exclude']));
            }

            if ($followup->getSegmentId() > 0) {
                Mage::getModel('fidelitas/segments_list')->loadList($followup->getSegmentId(), $campaign->getListnum());

                $subs = Mage::getModel('fidelitas/segments_list')->getCollection()
                        ->addFieldToFilter('listnum', $campaign->getListnum())
                        ->addFieldToFilter('segment_id', $followup->getSegmentId())
                        ->getAllIds('subscriber_id');

                $subscribers->addFieldToFilter('subscriber_id', array('in' => $subs));
            }


            $field = $campaign->getChannel() == 'email' ? 'email' : 'cellphone';
            $final = $subscribers->getAllIds($field);
            $followup->setRecipients(implode(',', $final))->save();

            if (strtolower($followup->getChannel()) == 'email') {

                $store = Mage::app()->getStore($list->getFinalStoreId());

                $data = array();
                $data['listnum'] = $campaign->getListnum();
                $data['subject'] = str_replace('{{subject}}', $campaign->getSubject(), $followup->getSubject());
                $data['internal_name'] = '[FW]' . $campaign->getName();
                $data['deploy_at'] = $followup->getSendAt();
                $data['message'] = str_replace("{{message}}", $campaign->getMessage(), $followup->getMessage());
                $data['from'] = $campaign->getFrom();
                $data['url'] = $store->getBaseUrl();
                $data['recurring'] = '0';
                $data['auto'] = '1';
                $data['channel'] = 'email';
                $data['followup_id'] = $followup->getId();

                $egoi = Mage::getModel('fidelitas/egoi');
                $newCampaign = Mage::getModel('fidelitas/campaigns')->setData($data)->save();

                $url = $store->getBaseUrl() . '/fidelitas/campaign/view/c/' . $newCampaign->getHash() . '/?___store=' . $store->getCode();
                $newCampaign->setUrl($url)->save();

                $data = array();
                $data['campaign'] = $newCampaign->getHash();
                $data['email'] = $followup->getRecipients();
                $data['fromID'] = $newCampaign->getFrom();
                $data['listID'] = $newCampaign->getListnum();
                $data['subject'] = $newCampaign->getSubject();

                $result = $egoi->setData($data)->sendEmail();
                if ($result->getData('id')) {
                    Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($newCampaign);
                }

                $followup->setSent(1)->save();
            }


            if (strtolower($followup->getChannel()) == 'sms') {

                $data = array();
                $data['subject'] = str_replace('{{subject}}', $campaign->getSubject(), $followup->getSubject());
                $data['internal_name'] = '[FW]' . $campaign->getName();
                $data['from'] = $campaign->getFrom();
                $data['listnum'] = $campaign->getListnum();
                $data['message'] = str_replace("{{message}}", $campaign->getMessage(), $followup->getMessage());
                $data['deploy_at'] = $followup->getSendAt();
                $data['recurring'] = '0';
                $data['auto'] = '1';
                $data['channel'] = 'SMS';
                $data['followup_id'] = $followup->getId();

                $egoi = Mage::getModel('fidelitas/egoi');
                $newCampaign = Mage::getModel('fidelitas/campaigns')->setData($data)->save();

                $data = array();
                $data['campaign'] = $newCampaign->getHash();
                $data['cellphone'] = $followup->getRecipients();
                $data['fromID'] = $newCampaign->getFrom();
                $data['listID'] = $newCampaign->getListnum();

                $result = $egoi->setData($data)->sendSMS();
                if ($result->getData('id')) {
                    Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($newCampaign);
                }
                $followup->setSent(1)->save();
            }
        }
    }

    public function buildRecipients($followup, $campaign) {

        $include = array();
        $exclude = array();

        $recipients = explode(',', $followup->getRecipientsOptions());

        if (
                (in_array('no_open', $recipients) && in_array('open', $recipients)) ||
                (in_array('no_click', $recipients) && in_array('click', $recipients)) ||
                (in_array('conversion', $recipients) && in_array('no_conversion', $recipients))
        ) {
            $include = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToFilter('list', $campaign->getListnum())
                    ->getAllIds();
            return array('include' => $include, 'exclude' => $exclude);
        }

        if (in_array('open', $recipients)) {
            $tmp = Mage::getModel('fidelitas/stats')
                    ->getCollection()
                    ->addFieldToFilter('views', array('gt' => 0))
                    ->addFieldToFilter('campaign_id', $campaign->getId())
                    ->getAllIds('subscriber_id');

            $include = array_merge($include, $tmp);
        }

        if (in_array('no_open', $recipients)) {
            $tmp = Mage::getModel('fidelitas/stats')
                    ->getCollection()
                    ->addFieldToFilter('views', array('gt' => 0))
                    ->addFieldToFilter('campaign_id', $campaign->getId())
                    ->getAllIds('subscriber_id');

            $exclude = array_merge($exclude, $tmp);
        }

        if (in_array('click', $recipients)) {
            $tmp = Mage::getModel('fidelitas/stats')
                    ->getCollection()
                    ->addFieldToFilter('clicks', array('gt' => 0))
                    ->addFieldToFilter('campaign_id', $campaign->getId())
                    ->getAllIds('subscriber_id');

            $include = array_merge($include, $tmp);
        }

        if (in_array('click', $recipients)) {
            $tmp = Mage::getModel('fidelitas/stats')
                    ->getCollection()
                    ->addFieldToFilter('clicks', array('gt' => 0))
                    ->addFieldToFilter('campaign_id', $campaign->getId())
                    ->getAllIds('subscriber_id');
            $exclude = array_merge($exclude, $tmp);
        }

        if (in_array('conversion', $recipients)) {
            $tmp = Mage::getModel('fidelitas/conversions')
                    ->getCollection()
                    ->addFieldToFilter('campaign_id', $campaign->getId())
                    ->getAllIds('subscriber_id');

            $include = array_merge($include, $tmp);
        }

        if (in_array('click', $recipients)) {
            $tmp = Mage::getModel('fidelitas/conversions')
                    ->getCollection()
                    ->addFieldToFilter('campaign_id', $campaign->getId())
                    ->getAllIds('subscriber_id');
            $exclude = array_merge($exclude, $tmp);
        }

        return array('include' => $include, 'exclude' => $exclude);
    }

    public function updateSendDate($campaign) {

        $collection = $this->getCollection()
                ->addFieldToFilter('campaign_id', $campaign->getId());

        foreach ($collection as $item) {

            $date = Mage::app()
                    ->getLocale()
                    ->date()
                    ->setTime($campaign->getData('deploy_at'), Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME)
                    ->setDate($campaign->getData('deploy_at'), Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME)
                    ->addDay($item->getData('days'));

            $item->setData('send_at', $date->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME))
                    ->save();
        }
    }

    public function _beforeSave() {
        $this->setDeployAt(Mage::getModel('core/date')->gmtDate(null, $this->getDeployAt()));

        parent::_beforeSave();
    }

}
