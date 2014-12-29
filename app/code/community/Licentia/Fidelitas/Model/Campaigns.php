<?php

class Licentia_Fidelitas_Model_Campaigns extends Mage_Core_Model_Abstract {

    const MYSQL_DATE = 'yyyy-MM-dd';
    const MYSQL_DATETIME = 'yyyy-MM-dd HH:mm:ss';

    /**
     * Init resource model and id field
     */
    protected function _construct() {
        parent::_construct();
        $this->_init('fidelitas/campaigns', 'campaign_id');
    }

    public function getChildrenCampaigns($id = null) {

        if ($id === null) {
            $id = $this->getId();
        }

        return $this->getCollection()->addFieldToFilter('parent_id', $id);
    }

    /**
     * Retrieves a list of available channels
     * @return array
     */
    public function getChannelsOption() {

        $channels = array();
        $channels['Email'] = 'Email';
        $channels['SMS'] = 'SMS';
        return $channels;
    }

    public function getSegments(array $lists = array()) {

        if (count($lists) == 0) {
            $lColleciton = Mage::getModel('fidelitas/lists')->getCollection();
            $lists = array();
            foreach ($lColleciton as $list) {
                $lists[] = $list->getListnum();
            }
        }

        $result = array();
        foreach ($lists as $list) {

            $segs = Mage::getModel('fidelitas/egoi');
            $nl = $segs->setData('listID', $list)->getSegments()->getData();

            if (isset($nl[0]['ERROR'])) {
                continue;
            }

            foreach ($nl as $seg) {
                $result[] = array('label' => $seg['segment']['NAME'], 'value' => $seg['segment']['NAME']);
            }
        }
        return $result;
    }

    public function getChannel() {
        return strtolower($this->getData('channel'));
    }

    /**
     * Returns a lis of existing campaign status messages
     * @return array
     */
    public function getStatusOptionArray() {


        $collection = Mage::getModel('fidelitas/campaigns')->getCollection();
        $collection->getSelect()->reset('columns')->columns(array('distinct' => new Zend_Db_Expr('DISTINCT(status)')));


        $result = $collection->getData();
        $list = array();

        foreach ($result as $country) {
            if (strlen($country['distinct']) == 0)
                continue;

            $list[$country['distinct']] = $country['distinct'];
        }

        return $list;
    }

    /**
     * Exustues daily cron JOB
     * @return \Licentia_Fidelitas_Model_Campaigns
     */
    public function cron() {

        $lists = Mage::getModel('fidelitas/lists')->getCollection()->getData();

        $listsIds = array();

        foreach ($lists as $list) {
            $listsIds[] = $list['listnum'];
        }

        $model = Mage::getModel('fidelitas/egoi')->getCampaigns()->getData();

        $remoteCampaignsIds = array();

        foreach ($model as $campaign) {
            if (!in_array($campaign['listnum'], $listsIds))
                continue;


            if ($campaign['end'] == '0000-00-00 00:00:00') {
                $campaign['end'] = new Zend_Db_Expr('NULL');
            }

            if ($campaign['start'] == '0000-00-00 00:00:00') {
                $campaign['start'] = new Zend_Db_Expr('NULL');
            }

            if (stripos($campaign['channel'], 'sms') !== false) {
                $campaign['channel'] = 'SMS';
            } elseif (stripos($campaign['channel'], 'email') !== false) {
                $campaign['channel'] = 'Email';
            } else {
                continue;
            }

            $camp = Mage::getModel('fidelitas/campaigns')->load($campaign['hash'], 'hash');
            if (!$camp->getId())
                continue;

            $campaign['campaign_id'] = $camp->getId();

            $remoteCampaignsIds[] = $campaign['hash'];

            Mage::getModel('fidelitas/campaigns')->setData($campaign)->setData('inCron', true)->save();
        }

        $localCampaigns = Mage::getModel('fidelitas/campaigns')
                ->getCollection()
                ->addFieldToSelect('hash')
                ->addFieldToSelect('campaign_id');

        //Let's delete lists that where removed from the e-goi servers
        foreach ($localCampaigns as $campaign) {
            if (!in_array($campaign->getHash(), $remoteCampaignsIds)) {
                Mage::getModel('fidelitas/campaigns')->load($campaign->getId())->delete();
            }
        }

        //Remove delete campaigns
        Mage::getModel('fidelitas/campaigns')->getCollection()->addFieldToFilter('status', 'deleted')->delete();
        return $this;
    }

    /**
     * Returns a list of available cron options
     *
     * @return type
     */
    public static function getCronList() {

        $list = array(
            '0' => Mage::helper('fidelitas')->__('No'),
            'd' => Mage::helper('fidelitas')->__('Daily'),
            'w' => Mage::helper('fidelitas')->__('Weekly'),
            'm' => Mage::helper('fidelitas')->__('Monthly'),
            'y' => Mage::helper('fidelitas')->__('Yearly'));

        return $list;
    }

    /**
     * Returns a list o list days
     * @return array
     */
    public static function getDaysList() {

        $lista = array(
            '0' => Mage::helper('fidelitas')->__('Sunday'),
            '1' => Mage::helper('fidelitas')->__('Monday'),
            '2' => Mage::helper('fidelitas')->__('Tuesday'),
            '3' => Mage::helper('fidelitas')->__('Wednesday'),
            '4' => Mage::helper('fidelitas')->__('Thursday'),
            '5' => Mage::helper('fidelitas')->__('Friday'),
            '6' => Mage::helper('fidelitas')->__('Saturday'));

        $list = array();
        foreach ($lista as $key => $value) {
            $list[] = array('value' => $key, 'label' => $value);
        }


        return $list;
    }

    /**
     * returns month list
     * @return array
     */
    public static function getMonthsList() {

        $list = array(
            '1' => Mage::helper('fidelitas')->__('January'),
            '2' => Mage::helper('fidelitas')->__('February'),
            '3' => Mage::helper('fidelitas')->__('March'),
            '4' => Mage::helper('fidelitas')->__('April'),
            '5' => Mage::helper('fidelitas')->__('May'),
            '6' => Mage::helper('fidelitas')->__('June'),
            '7' => Mage::helper('fidelitas')->__('July'),
            '8' => Mage::helper('fidelitas')->__('August'),
            '9' => Mage::helper('fidelitas')->__('September'),
            '10' => Mage::helper('fidelitas')->__('October'),
            '11' => Mage::helper('fidelitas')->__('November'),
            '12' => Mage::helper('fidelitas')->__('December'));

        return $list;
    }

    /**
     * Returns a list of hours
     * @return array
     */
    public static function getRunAroundList() {

        $return = array();

        for ($i = 0; $i <= 23; $i++) {
            $return[] = array('value' => $i, 'label' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00');
        }

        return $return;
    }

    /**
     * Retuns a list of possible days and expressions available to send a campaign.
     * Such expressions include Last day of the month, first Mondat, etcet
     * @return array
     */
    public static function getDaysMonthsList() {

        $list = array();

        for ($i = 1; $i <= 31; $i++) {
            $list[] = array('label' => $i, 'value' => $i);
        }


        $final = array();
        $final[] = array('label' => Mage::helper('fidelitas')->__('Specific Day'), 'value' => $list);

        $days = self::getDaysList();

        $din = array();
        for ($i = 1; $i <= 4; $i++) {
            foreach ($days as $day) {
                $din[] = array('value' => $i . '-' . $day['value'], 'label' => Mage::helper('fidelitas')->__('On the %s %s of the month', $i, $day['label']));
            }
        }

        foreach ($days as $day) {
            $din[] = array('value' => '|' . $day['value'], 'label' => Mage::helper('fidelitas')->__('On the last %s of the month', $day['label']));
        }

        $din[] = array('value' => 'u-u', 'label' => Mage::helper('fidelitas')->__('Last Day of the Month '));

        $final[] = array('label' => Mage::helper('fidelitas')->__('Dynamic Day'), 'value' => $din);

        return $final;
    }

    /**
     * Sends a SMS campaign
     *
     * @param type $campaign
     * @return boolean
     */
    public function sendSms($campaign) {

        $campaign->setLocalStatus('running')->save();
        $segments = explode(',', $campaign->getSegmentsIds());

        $list = Mage::getModel('fidelitas/lists')->load($campaign->getListnum(), 'listnum');

        if (Mage::registry('current_list')) {
            Mage::unregister('current_list');
        }
        Mage::register('current_list', $list);


        $egoi = Mage::getModel('fidelitas/egoi');

        if ((count($segments) > 1 || $segments[0] != '0') && $campaign->getData('segments_origin') == 'store') {

            $customers = array();

            foreach ($segments as $segment) {
                $load = Mage::getModel('fidelitas/segments')->load($segment);

                if ($load->getId()) {
                    Mage::register('current_campaign', $campaign, true);
                    $customers[] = $load->getMatchingCustomersIds();
                    Mage::unregister('current_campaign');
                }
            }

            $finalCustomers = array();

            foreach ($customers as $sendC) {
                $finalCustomers = array_merge($finalCustomers, $sendC);
            }

            $finalCustomers = array_unique($finalCustomers);

            $subscribersPhones = Mage::getModel('fidelitas/subscribers')->getSubscribersInfo('cellphone', $finalCustomers, $campaign->getListnum());
            if (count($subscribersPhones) == 0) {
                return true;
            }

            /* If we can't send more than one SMS per recurring campaign, we need to unset subscriber phone */
            if ($campaign->getRecurringUnique() == 1) {

                foreach ($subscribersPhones as $key => $phone) {
                    $unique = Mage::getModel('fidelitas/history')->getCollection()
                            ->addFieldToSelect('campaign_id')
                            ->addFieldToFilter('campaign_id', $campaign->getParentId())
                            ->addFieldToFilter('subscriber_phone', $phone);

                    if ($unique->count() != 0) {
                        unset($subscribersPhones[$key]);
                    }
                }
            }

            $data = array();
            $data['campaign'] = $campaign->getHash();
            $data['cellphone'] = $subscribersPhones;
            $data['fromID'] = $campaign->getFrom();
            $data['listID'] = $campaign->getListnum();
            $data['message'] = $campaign->getMessage();
            try {

                Mage::dispatchEvent('fidelitas_campaign_send_before', array('campaign' => $campaign, 'egoi_data' => $data));

                $result = $egoi->setData($data)->sendSms();
                if ($result->getData('id')) {
                    Mage::dispatchEvent('fidelitas_campaign_send_after', array('campaign' => $campaign, 'egoi_data' => $data));
                    Mage::log('SMS Campaign sent - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
                    Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);

                    /* Log campaigns sent */
                    foreach ($subscribersPhones as $phone) {
                        $data = array();
                        $data['campaign_id'] = $campaign->getParentId();
                        $data['children_id'] = $campaign->getId();
                        $data['sent_at'] = now();
                        $data['subscriber_phone'] = $phone;

                        Mage::getModel('fidelitas/history')->setData($data)->save();
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $this->resolveException($campaign, $data, $e);
                Mage::log('SMS Campaign NOT sent - ' . $campaign->getId(), 3, 'fidelitas-campaigns.log');
            }
        } else {
            $segments = strlen($campaign->getEgoiSegments()) > 0 ? explode(',', $campaign->getEgoiSegments()) : 'all';

            $data = array();
            $data['campaign'] = $campaign->getHash();
            $data['segment'] = $segments;
            $data['fromID'] = $campaign->getFrom();
            $data['listID'] = $campaign->getListnum();
            $data['message'] = $campaign->getMessage();
            try {
                Mage::dispatchEvent('fidelitas_campaign_send_before', array('campaign' => $campaign, 'egoi_data' => $data));

                $result = $egoi->setData($data)->sendSms();

                if ($result->getData('id')) {
                    Mage::dispatchEvent('fidelitas_campaign_send_after', array('campaign' => $campaign, 'egoi_data' => $data));

                    Mage::log('SMS Campaign sent - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
                    Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::log('SMS Campaign NOT sent - ' . $campaign->getId(), 3, 'fidelitas-campaigns.log');
                $this->resolveException($campaign, $data, $e);
            }
        }

        return $result;
    }

    /**
     * Send email campaign
     * @param type $campaign
     * @return boolean
     */
    public function sendEmail($campaign) {

        $segments = explode(',', $campaign->getSegmentsIds());

        $list = Mage::getModel('fidelitas/lists')->load($campaign->getListnum(), 'listnum');

        Mage::register('current_list', $list, true);

        $updateUrlData = $campaign->getData();
        if (strlen(trim($updateUrlData['url'])) == 0 || $updateUrlData['url'] == Mage::app()->getStore()->getUrl()) {
            $store = Mage::app()->getStore($list->getFinalStoreId());
            $url = $store->getBaseUrl() . 'fidelitas/campaign/view/c/' . $campaign->getHash() . '/?___store=' . $store->getCode();
            $updateUrlData['url'] = $url;
            $campaign->setData('url', $url);
            Mage::getModel('fidelitas/campaigns')->setData($updateUrlData)->save();
        }

        $egoi = Mage::getModel('fidelitas/egoi');

        if ((count($segments) > 1 || $segments[0] != '0') && $campaign->getData('segments_origin') == 'store') {

            $customers = array();

            foreach ($segments as $segment) {
                $load = Mage::getModel('fidelitas/segments')->load($segment);

                if ($load->getId()) {
                    Mage::register('current_campaign', $campaign, true);
                    $customers[] = $load->getMatchingCustomersIds();
                    Mage::unregister('current_campaign');
                }
            }


            $finalCustomers = array();
            foreach ($customers as $sendC) {
                $finalCustomers = array_merge($finalCustomers, $sendC);
            }

            $finalCustomers = array_unique($finalCustomers);

            $subscribersEmails = Mage::getModel('fidelitas/subscribers')->getSubscribersInfo('email', $finalCustomers, $campaign->getListnum());

            if (count($subscribersEmails) == 0) {
                return true;
            }

            if ($campaign->getRecurringUnique() == 1) {

                foreach ($subscribersEmails as $key => $email) {
                    $unique = Mage::getModel('fidelitas/history')->getCollection()
                            ->addFieldToSelect('campaign_id')
                            ->addFieldToFilter('campaign_id', $campaign->getParentId())
                            ->addFieldToFilter('subscriber_email', $email);

                    if ($unique->count() != 0) {
                        unset($subscribersEmails[$key]);
                    }
                }
            }


            if (count($subscribersEmails) == 1) {
                $onlyEmail = reset($subscribersEmails);
                $onlySubscriber = Mage::getModel('fidelitas/subscribers')->load($onlyEmail, 'email');

                $store = Mage::app()->getStore($list->getFinalStoreId());
                $url = $store->getBaseUrl() . 'fidelitas/campaign/user/uid/' . $onlySubscriber->getData('uid') . '/c/' . $campaign->getHash() . '/?___store=' . $store->getCode();
                $updateUrlData['url'] = $url;
                $campaign->setData('url', $url);
                Mage::getModel('fidelitas/campaigns')->setData($updateUrlData)->save();
            }

            $data = array();
            $data['campaign'] = $campaign->getHash();
            $data['email'] = $subscribersEmails;
            #$data['segment'] = 'all';
            $data['fromID'] = $campaign->getFrom();
            $data['listID'] = $campaign->getListnum();
            $data['subject'] = $campaign->getSubject();

            if ($campaign->getHeaderFooterTemplate()) {
                $data['header_footer_template'] = $campaign->getHeaderFooterTemplate();
            }

            try {
                Mage::dispatchEvent('fidelitas_campaign_send_before', array('campaign' => $campaign, 'egoi_data' => $data));

                Mage::log(serialize($data), null, 'fidelitas-campaigns.log', true);
                $result = $egoi->setData($data)->sendEmail();
                if ($result->getData('id')) {
                    Mage::dispatchEvent('fidelitas_campaign_send_after', array('campaign' => $campaign, 'egoi_data' => $data));

                    Mage::log('Email Campaign sent - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
                    Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);

                    foreach ($subscribersEmails as $email) {
                        $data = array();
                        $data['campaign_id'] = $campaign->getParentId();
                        $data['children_id'] = $campaign->getId();
                        $data['sent_at'] = now();
                        $data['subscriber_email'] = $email;

                        Mage::getModel('fidelitas/history')->setData($data)->save();
                    }

                    $campaign->setData('sent', count($subscribersEmails))->save();
                }
            } catch (Exception $e) {
                Mage::log('Email Campaign NOT sent - ' . $campaign->getId(), 3, 'fidelitas-campaigns.log');
                Mage::logException($e);
                $this->resolveException($campaign, $data, $e);
            }
        } else {
            $segments = strlen($campaign->getEgoiSegments()) > 0 ? explode(',', $campaign->getEgoiSegments()) : 'all';
            $data = array();
            $data['campaign'] = $campaign->getHash();
            $data['segment'] = $segments;
            $data['fromID'] = $campaign->getFrom();
            $data['listID'] = $campaign->getListnum();
            $data['subject'] = $campaign->getSubject();

            if ($campaign->getHeaderFooterTemplate()) {
                $data['header_footer_template'] = $campaign->getHeaderFooterTemplate();
            }

            try {
                Mage::dispatchEvent('fidelitas_campaign_send_before', array('campaign' => $campaign, 'egoi_data' => $data));

                $result = $egoi->setData($data)->sendEmail($data);
                if ($result->getData('id')) {
                    Mage::dispatchEvent('fidelitas_campaign_send_after', array('campaign' => $campaign, 'egoi_data' => $data));

                    Mage::log('Email Campaign sent - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
                    Mage::getModel('fidelitas/campaigns')->updateCampaignAfterSend($campaign);

//                    $list = Mage::getModel('fidelitas/lists')->load($campaign->getListnum(), 'listnum');
//
//                    if ($list->getId()) {
//                        $campaign->setData('sent', $list->getData('subs_activos'))->save();
//                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::log('Email Campaign NOT sent - ' . $campaign->getId(), 3, 'fidelitas-campaigns.log');
                $this->resolveException($campaign, $data, $e);
            }
        }


        return $result;
    }

    /**
     * Send campaign from CRON
     */
    public function sendCampaigns() {

        Mage::log('Send Campaigns: Cron STARTS', null, 'fidelitas-campaigns.log');
        $now = Mage::getSingleton('core/date')->gmtdate();

        //Non-Recurring Campaigns
        $collection = Mage::getModel('fidelitas/campaigns')
                ->getCollection()
                ->addFieldToFilter('local_status', array('in' => array('standby', 'error')))
                ->addFieldToFilter('deploy_at', array('lteq' => $now))
                ->addFieldToFilter('recurring', '0');

        foreach ($collection as $campaign) {
            Mage::log('Start Process Sending campaign - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
            if (strtolower($campaign->getChannel()) == 'sms') {
                $this->sendSms($campaign);
            }

            if (strtolower($campaign->getChannel()) == 'email') {
                $this->sendEmail($campaign);
            }
            $campaign->updateCampaignAfterSend($campaign);
            Mage::log('Ended Sending campaign - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
        }

        //Recurring Campaigns
        $collectionRecurring = Mage::getModel('fidelitas/campaigns')
                ->getCollection()
                ->addFieldToFilter('local_status', array('in' => array('standby', 'error')))
                ->addFieldToFilter('recurring_next_run', array('lteq' => $now))
                ->addFieldToFilter('recurring', array('neq' => '0'));

        foreach ($collectionRecurring as $campaign) {
            Mage::log('Start Process Sending campaign - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
            Mage::register('recurring_campaign', $campaign);

            $newCampaignData = $campaign->getData();
            unset($newCampaignData['campaign_id']);
            $newCampaignData['nex_run'] = new Zend_Db_Expr('NULL');
            $newCampaignData['recurring'] = 0;
            $newCampaignData['parent_id'] = $campaign->getId();
            $newCampaignData['auto'] = '1';
            $newCampaignData['internal_name'] = $newCampaignData['internal_name'] . ' [AUTO]';
            $newCampaignData['clicks'] = 0;
            $newCampaignData['unique_clicks'] = 0;
            $newCampaignData['views'] = 0;
            $newCampaignData['unique_views'] = 0;
            $newCampaignData['unsent'] = 0;
            $newCampaignData['sent'] = 0;
            $newCampaignData['bounces'] = 0;
            $newCampaignData['unsubscribes'] = 0;

            $newCampaign = Mage::getModel('fidelitas/campaigns')->setData($newCampaignData)->save();

            $campaign->setLocalStatus('running')->save();

            if (strtolower($newCampaign->getChannel()) == 'sms') {
                $this->sendSms($newCampaign);
            }
            if (strtolower($newCampaign->getChannel()) == 'email') {
                $this->sendEmail($newCampaign);
            }

            $campaignFinal = Mage::getModel('fidelitas/campaigns')->load($campaign->getId());
            $campaignFinal->updateCampaignAfterSend($campaignFinal);

            Mage::unregister('recurring_campaign');
            Mage::log('Ended Sending campaign - ' . $campaign->getId(), null, 'fidelitas-campaigns.log');
        }


        Mage::log('Send Campaigns: Cron ENDS', null, 'fidelitas-campaigns.log');
    }

    /**
     * Builds next send date for recurring campaigns
     * @param type $campaignData
     * @return type
     */
    public function getNextRecurringDate($campaignData) {


        if (isset($campaignData['recurring_last_run'])) {
            $now = Mage::app()
                    ->getLocale()
                    ->date()
                    ->setDate($campaignData['recurring_last_run'], self::MYSQL_DATE)
                    ->setTime($campaignData['recurring_last_run'], self::MYSQL_DATETIME);
        } else {
            $now = Mage::app()->getLocale()->date();
        }

        if ($campaignData['recurring'] == '0')
            return $campaignData['deploy_at'];

        if (!isset($campaignData['recurring_first_run']) || strlen($campaignData['recurring_first_run']) == 0) {
            $campaignData['recurring_first_run'] = $now->get(self::MYSQL_DATE);
        }

        if (isset($campaignData['run_until'])) {
            $dateStart = Mage::app()
                    ->getLocale()
                    ->date()
                    ->setDate($campaignData['recurring_first_run'], self::MYSQL_DATE)
                    ->setTime($campaignData['recurring_first_run'], self::MYSQL_DATETIME);
        } else {
            $dateStart = $now;
        }

        $today = $dateStart->get(Zend_Date::WEEKDAY_DIGIT);

        switch ($campaignData['recurring']) {
            case 'd':
            case 'w':
                $oldDay = null;
                $days = explode(',', $campaignData['recurring_daily']);

                if ($campaignData['recurring'] == 'w') {
                    $days = explode(',', $campaignData['recurring_day']);
                }


                if (count($days) > 1) {
                    $index = array_search($today, $days);

                    if ($index === false) {

                        foreach ($days as $key => $day) {
                            if (!isset($oldDay)) {
                                $oldDay = $key;
                            }
                            if ($day > $today) {
                                $index = $oldDay;
                                break;
                            }

                            $oldDay = $key;
                        }

                        if ($index === false) {
                            $index = $days[0];
                        }
                    }

                    if (isset($days[$index])) {
                        $nextDay = $days[$index];
                    } else {
                        $nextDay = reset($days);
                    }

                    $nextDay = $nextDay - $today;
                } else {

                    if ($today == 0) {
                        $nextDay = $days[0];
                    } else {
                        $nextDay = abs(7 - $today + $days[0]);
                    }

                    if ($nextDay == 7) {
                        $nextDay = 0;
                    }
                }

                if ($nextDay < 0) {
                    $nextDay = $nextDay + 6;
                }

                $run = $dateStart->addDay($nextDay);


                if ($now->get(self::MYSQL_DATETIME) >= $run->get(self::MYSQL_DATETIME)) {

                    if ($campaignData['recurring'] == 'w') {
                        $run = $now->addWeek(1);
                    } else {
                        $run = $now->addDay(1);
                    }
                }


                $nextDate = $run->get(self::MYSQL_DATETIME);

                break;

            case 'm':

                $nextDateTemp = $this->calculateRecurringMonth($dateStart, $campaignData);
                $nextDate = $nextDateTemp->get(self::MYSQL_DATETIME);

                break;
            case 'y':
                $dateStart->setMonth($campaignData['recurring_month']);
                $day = $this->calculateRecurringMonth($dateStart, $campaignData)->get(Zend_Date::DAY);
                $run = $dateStart->setDay($day);

                if ($now->get(self::MYSQL_DATETIME) >= $run->get(self::MYSQL_DATETIME)) {

                    $run->setMonth($campaignData['recurring_month']);
                    $run->addYear(1);

                    $day = $this->calculateRecurringMonth($run, $campaignData)->get(Zend_Date::DAY);
                    $run = $run->setDay($day);
                }

                $nextDate = $run->get(self::MYSQL_DATETIME);

                break;
        }


        return $nextDate;
    }

    /**
     * Builds next month to send recurring campaign
     * @param type $dateStart
     * @param type $campaignData
     * @param type $monthsToAdd
     * @return type
     */
    public function calculateRecurringMonth($dateStart, $campaignData, $monthsToAdd = null) {

        $now = Mage::app()->getLocale()->date();

        if (strpos($campaignData['recurring_monthly'], '|') !== false) {

            $tDate = $dateStart;

            $lastDay = cal_days_in_month(CAL_GREGORIAN, $tDate->get('MM'), $tDate->get('yyyy'));

            $calcDay = trim($campaignData['recurring_monthly'], '|');

            if ($monthsToAdd) {
                $tDate->addMonth($monthsToAdd);
            }

            $testDate = clone $dateStart;
            $testDate->setDay($lastDay);

            for ($i = $lastDay; $i >= $lastDay - 7; $i--) {
                $dayN = $testDate->get(Zend_Date::WEEKDAY_DIGIT);

                if ($dayN == $calcDay) {
                    $finalDay = $testDate->get(Zend_Date::DAY);
                    break;
                }
                $testDate->subDay(1);
            }

            $run = $dateStart->setDay($finalDay);

            if ($now->get(self::MYSQL_DATETIME) > $run->get(self::MYSQL_DATETIME)) {
                $run = $this->calculateRecurringMonth($dateStart, $campaignData, 1);
            }
        } elseif ($campaignData['recurring_monthly'] == 'u-u') {

            $tDate = $dateStart;
            $lastDay = cal_days_in_month(CAL_GREGORIAN, $tDate->get('MM'), $tDate->get('yyyy'));
            $run = $dateStart->setDay($lastDay);
        } elseif (strpos($campaignData['recurring_monthly'], '-') !== false) {

            $calcDay = explode('-', $campaignData['recurring_monthly']);

            $tDate = $dateStart;

            if ($monthsToAdd) {
                $tDate->addMonth($monthsToAdd);
            }

            $testDate = clone $dateStart;
            $testDate->setDay(1);

            for ($i = 0; $i <= 6; $i++) {
                $dayN = $testDate->get(Zend_Date::WEEKDAY_DIGIT);

                if ($dayN == $calcDay[1]) {
                    $day = $testDate->get(Zend_Date::DAY);
                    break;
                }

                $testDate->addDay(1);
            }

            if ($calcDay[0] > 1) {
                $finalDay = $day + (($calcDay[0] - 1) * 7);
            } else {
                $finalDay = $day;
            }

            $run = $dateStart->setDay($finalDay);

            if ($now->get(self::MYSQL_DATETIME) > $run->get(self::MYSQL_DATETIME)) {
                $run = $this->calculateRecurringMonth($dateStart, $campaignData, 1);
            }
        } else {
            $run = $dateStart->setDay($campaignData['recurring_monthly']);

            if ($now->get(self::MYSQL_DATETIME) > $run->get(self::MYSQL_DATETIME)) {
                $dateStart->addMonth(1)->setDay($campaignData['recurring_monthly']);
            }
        }

        return $run;
    }

    /**
     * Saves campaign
     * @return type
     */
    public function save() {

        if ($this->getData('inCron') === true) {
            return parent::save();
        }

        if ($this->getDeployAt()) {
            $this->setDeployAt(Mage::getModel('core/date')->gmtDate(null, $this->getDeployAt()));
        }

        $data = $this->getData();

        $this->setData('recurring_next_run', $this->getNextRecurringDate($this->getData()));

        $channel = strtolower($data['channel']);

        $id = $this->getId();

        if (!isset($data['segments_ids'])) {
            $data['segments_ids'] = array();
        }

        $egoi = Mage::getModel('fidelitas/egoi');

        $data['listID'] = $data['listnum'];

        if ($this->getId()) {
            Mage::getModel('fidelitas/followup')->updateSendDate($this);
        }

        $remote = Mage::getModel('fidelitas/campaigns')->updateRemote($this, array('listnum', 'subject', 'message', 'from', 'internal_name', 'url', 'link_referer_top', 'link_referer_bottom', 'link_view_top', 'link_view_bottom', 'link_edit_top', 'link_edit_bottom', 'link_print_top', 'link_print_bottom', 'link_social_networks_top', 'link_social_networks_bottom'));

        if ($this->getId() && $this->getOrigData('message') != $this->getData('message')) {
            $this->findLinksForCampaign($this);
        }

        if (!$remote) {
            return parent::save();
        }

        if ($channel == 'sms') {

            if ($id) {
                $hash = Mage::getModel('fidelitas/campaigns')->load($id)->getHash();
                $data['campaign'] = $hash;
                $egoi->setData($data);
                $egoi->editCampaignSms();
            } else {

                $egoi->setData($data);
                $egoi->createCampaignSms();
                $result = $egoi->getData();

                if (isset($result['id'])) {
                    $this->setData('hash', $result['id']);
                }
            }
        }

        if ($channel == 'email') {

            if (!isset($data['url']) || (isset($data['url']) && strlen($data['url']) == 0)) {
                $list = Mage::getModel('fidelitas/lists')->load($this->getListnum(), 'listnum');
                $store = Mage::app()->getStore($list->getFinalStoreId());
                $data['url'] = $store->getBaseUrl();
            }

            if ($id) {
                $hash = Mage::getModel('fidelitas/campaigns')->load($id)->getHash();
                $data['campaign'] = $hash;
                $egoi->setData($data);
                $result = $egoi->editCampaignEmail();
            } else {
                $egoi->setData($data);
                $egoi->createCampaignEmail();
                $result = $egoi->getData();

                if (isset($result['id'])) {
                    $this->setData('hash', $result['id']);
                }
            }
        }


        return parent::save();
    }

    /**
     * Removes a campaign
     * @return type
     */
    public function delete() {

        $egoi = Mage::getModel('fidelitas/egoi');
        $hash = $this->getHash();

        if ($hash) {
            $egoi->setData('campaign', $hash);
            $egoi->deleteCampaign();
        }

        //let's check for childrens
        $childrens = Mage::getModel('fidelitas/campaigns')->getCollection()->addFieldToFilter('parent_id', $this->getId());
        foreach ($childrens as $children) {
            $children->delete();
        }

        return parent::delete();
    }

    /**
     * Updates campaign on server
     * @param type $data
     * @param type $fields
     * @return boolean
     */
    public function updateRemote($data, $fields = array()) {

        if (!$data->getId()) {
            return true;
        }

        $class = clone $data;
        $class->load($data->getId());

        foreach ($fields as $field) {
            if ($class->getOrigData($field) != $data->getData($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Precesses somo "meta data" for campaigns
     * @param type $campaign
     */
    public function updateCampaignAfterSend($campaign) {

        $data = $campaign->getData();

        $now = Mage::app()->getLocale()->date();

        $data['recurring_last_run'] = $now->get(self::MYSQL_DATETIME);

        if ($data['recurring'] != '0') {

            $data['local_status'] = 'standby';

            $finishRun = false;
            if (($data['run_times'] - $data['run_times_left']) <= 1 && (int) $data['run_times'] > 0) {
                $finishRun = true;
            }

            $data['run_times_left'] = new Zend_Db_Expr('run_times_left - 1');


            $finishDate = false;
            if ($now->get(self::MYSQL_DATE) > $data['run_until'] || $data['recurring_next_run'] > $data['run_until']) {
                $finishDate = true;
            }

            if ($finishDate && $finishRun) {

                $data['local_status'] = 'finished';
            }
        } else {
            $data['local_status'] = 'finished';
        }

        $campaign->setData($data)->save();
    }

    /**
     * Send SMS comment from orders, invoices, etc
     * @param type $params
     * @return type
     */
    public function sendSmsComment($params) {

        if (strlen(trim($params['fidelitas']['comment'])) == 0) {
            return;
        }

        if (isset($params['comment']['comment']) && strlen(trim($params['comment']['comment'])) == 0) {
            return;
        }

        if (isset($params['history']['comment']) && strlen(trim($params['history']['comment'])) == 0) {
            return;
        }

        $list = Mage::getModel('fidelitas/lists')->getClientList();

        if (!$list) {
            return false;
        }

        $fidelitas = $params['fidelitas'];
        $number = $fidelitas['number'];
        $title = '';

        if (array_key_exists('order_id', $params)) {
            $title = 'Order Comment to customer';
            $info = Mage::registry('current_order');
            $customerId = $info->getCustomerId();
            $store_id = $info->getStoreId();
        }
        if (array_key_exists('invoice_id', $params)) {
            $title = 'Invoice Comment to customer';
            $info = Mage::getModel('sales/order_invoice')->load($params['invoice_id']);
            $customerId = $info->getOrder()->getCustomerId();
            $store_id = $info->getOrder()->getStoreId();
        }
        if (array_key_exists('shipment_id', $params)) {
            $title = 'Shippment Comment to customer';
            $info = Mage::getModel('sales/order_shipment')->load($params['shipment_id']);
            $customerId = $info->getOrder()->getCustomerId();
            $store_id = $info->getOrder()->getStoreId();
        }
        if (array_key_exists('creditmemo_id', $params)) {
            $title = 'Credit Memo Comment to customer';
            $info = Mage::getModel('sales/order_creditmemo')->load($params['creditmemo_id']);
            $customerId = $info->getOrder()->getCustomerId();
            $store_id = $info->getOrder()->getStoreId();
        }

        $sender = Mage::getStoreConfig('fidelitas/comments/sms_number', $store_id);

        $email = Mage::getModel('customer/customer')->load($customerId)->getEmail();

        $subs = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter('email', $email)
                ->addFieldToFilter('list', $list->getListnum());

        if ($subs->count() == 0) {
            $data = array();
            $data['email'] = $email;
            $data['status'] = 1;
            $data['cellphone'] = $number;
            $data['list'] = $list->getListnum();
            $sub = Mage::getModel('fidelitas/subscribers')->setData($data)->save();
        } else {
            $sub = $subs->getFirstItem();
        }

        if ($sub->getData('cellphone') != $number) {
            $sub->setData('cellphone', $number)->save();
        }

        $data = array();
        $data['cellphone'] = $number;
        $data['message'] = substr($fidelitas['comment'], 0, 160);
        $data['auto'] = '1';
        $data['subject'] = $title;
        $data['internal_title'] = $title;
        $data['listID'] = $list->getListnum();
        $data['fromID'] = $sender;

        return Mage::getModel('fidelitas/egoi')->setData($data)->sendSMS();
    }

    /**
     * Send a daily report by SMS
     * @return type
     */
    public function sendDailyReport() {

        $list = Mage::getModel('adminhtml/system_store')->getStoresStructure();

        $value = array();
        $value['global'] = Mage::app()->getConfig()->getNode('fidelitas/reports', 'default', 0);
        if (!is_object($value['global'])) {
            return;
        }


        $value['global']->asArray();

        foreach ($list as $sites) {
            $value['websites'][$sites['value']] = Mage::app()->getConfig()->getNode('fidelitas/reports', 'websites', (int) $sites['value'])->asArray();

            foreach ($sites['children'] as $storeGroup) {
                foreach ($storeGroup['children'] as $store) {
                    $value['stores'][$store['value']] = Mage::app()->getConfig()->getNode('fidelitas/reports', 'stores', (int) $store['value'])->asArray();
                }
            }
        }

        foreach ($value as $scope => $report) {

            if ($scope == 'global') {

                if (!Mage::getModel('fidelitas/cron')->canSendCron($report, $scope, 0)) {
                    continue;
                }

                $stats = Mage::getModel('fidelitas/cron')->sendStats($report, $scope, 0);
            } elseif ($scope == 'websites') {

                $stats = false;
                foreach ($report as $siteId => $siteReport) {
                    if (!Mage::getModel('fidelitas/cron')->canSendCron($siteReport, $scope, $siteId)) {
                        continue;
                    }

                    $stats = Mage::getModel('fidelitas/cron')->sendStats($siteReport, $scope, $siteId);
                }

                if (!$stats) {
                    continue;
                }
            } elseif ($scope == 'stores') {

                $stats = false;
                foreach ($report as $storeId => $storeReport) {
                    if (!Mage::getModel('fidelitas/cron')->canSendCron($storeReport, $scope, $storeId)) {
                        continue;
                    }

                    $stats = Mage::getModel('fidelitas/cron')->sendStats($storeReport, $scope, $storeId);
                }

                if (!$stats) {
                    continue;
                }
            }
        }
    }

    /**
     * Error report
     * @param type $data
     * @param type $errorMessage
     */
    public function sendErrorMessage($data, $errorMessage) {

        if (!Mage::getStoreConfigFlag('fidelitas/config/email')) {
            return;
        }

        $info = Mage::getModel('fidelitas/egoi')->getUserData()->getData();
        $info = $info[0];


        $msg = Mage::helper('fidelitas')->__("An error occurred while trying to send a campaing.");
        $msg .= "<br><br>";
        $msg .= Mage::helper('fidelitas')->__("E-Goi service response:<br> %s", $errorMessage);
        $msg .= "<br><br>";
        $msg .= Mage::helper('fidelitas')->__("Data sent to E-Goi:<br>");

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode("\n", $value);
            }
            $msg .="$key : $value <br>";
        }


        $mail = Mage::getModel('core/email');
        $mail->setToName($info['first_name'] . ' ' . $info['last_name']);
        $mail->setToEmail($info['email']);
        $mail->setBody($msg);
        $mail->setSubject(Mage::helper('fidelitas')->__('Campaign Report - ERROR'));
        $mail->setFromEmail($info['email']);
        $mail->setFromName(Mage::helper('fidelitas')->__('Magento E-Goi Extension'));
        $mail->setType('html');

        try {
            $mail->send();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Exception handling
     * @param type $campaign
     * @param type $data
     * @param type $exception
     */
    public function resolveException($campaign, $data, $exception) {

        if (Mage::registry('recurring_campaign')) {
            Mage::registry('recurring_campaign')->setLocalStatus('error')->setServiceResult($exception->getMessage())->save();
        }

        $campaign->setLocalStatus('error')->setServiceResult($exception->getMessage())->save();
        $campaign->sendErrorMessage($data, $exception->getMessage());
        #throw new Exception($exception->getPrevious());
    }

    /**
     * Returns a list of campaigns IDS and internal name
     * @return type
     */
    public function toFormValues() {
        $return = array();
        $collection = $this->getCollection()
                ->addFieldToSelect('internal_name')
                ->addFieldToSelect('campaign_id')
                ->addFieldToFilter('auto', 0)
                ->setOrder('internal_name', 'ASC');
        foreach ($collection as $campaign) {
            $return[$campaign->getId()] = $campaign->getInternalName() . ' (ID:' . $campaign->getId() . ')';
        }

        return $return;
    }

    /**
     * Returns a list of campaigns IDS and internal name
     * @return type
     */
    public function toFormValuesNonAuto() {
        $return = array();
        $collection = $this->getCollection()
                ->addFieldToFilter('auto', 0)
                ->addFieldToSelect('internal_name')
                ->addFieldToSelect('campaign_id')
                ->setOrder('internal_name', 'ASC');
        foreach ($collection as $campaign) {
            $return[$campaign->getId()] = $campaign->getInternalName() . ' (ID:' . $campaign->getId() . ')';
        }

        return $return;
    }

    /**
     * Campaign Vars
     * @param Varien_Object $subscriber
     * @return type
     */
    public static function getTemplateVars(Varien_Object $subscriber) {
        $replace = array();
        $replace['!fname'] = $subscriber->getFirstName();
        $replace['!lname'] = $subscriber->getLastName();
        $replace['!email'] = $subscriber->getEmail();
        $replace['!cellphone'] = $subscriber->getCellphone();
        $replace['!birth_date'] = $subscriber->getBirthDate();

        return $replace;
    }

    public function findLinksForCampaign($campaign) {

        $message = Mage::helper('cms')->getBlockTemplateProcessor()->filter($campaign->getMessage());

        if (!$campaign->getId()) {
            return;
        }

        $data = array();
        $data['campaign_id'] = $campaign->getId();

        $links = Mage::getModel('fidelitas/links')
                ->getCollection()
                ->addFieldToFilter('campaign_id', $campaign->getId());

        $exists = array();
        $temp = array();
        foreach ($links as $link) {
            $exists[$link->getId()] = $link->getLink();
            #$link->delete();
        }

        $doc = new DOMDocument();
        $doc->loadHTML($message);
        foreach ($doc->getElementsByTagName('a') as $link) {

            $data['link'] = $link->getAttribute('href');
            $data['campaign_id'] = $campaign->getId();

            if (in_array($data['link'], $exists)) {
                $temp[] = $data['link'];
                continue;
            }

            $result = Mage::getModel('fidelitas/links')->setData($data)->save();

            $temp[] = $result->getLink();
        }


        $links = Mage::getModel('fidelitas/links')
                ->getCollection()
                ->addFieldToFilter('campaign_id', $campaign->getId());

        foreach ($links as $link) {

            if (!in_array($link->getLink(), $temp)) {
                $link->delete();
            }
        }
    }

}
