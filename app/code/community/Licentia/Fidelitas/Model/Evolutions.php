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
class Licentia_Fidelitas_Model_Evolutions extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/evolutions');
    }

    public function log($segmentId, $customersIds) {

        $day = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);

        $list = Mage::registry('current_list');
        $campaign = Mage::registry('current_campaign');

        $todayCampaign = Mage::getModel('fidelitas/evolutions')
                ->getCollection()
                ->addFieldToSelect('campaign_id')
                ->addFieldToFilter('segment_id', $segmentId)
                ->addFieldToFilter('campaign_id', array('gt' => 0))
                ->addFieldToFilter('created_at', $day);

        //This means a campaign has already been sent today, so no need to mess stats
        //Besides we need campaign info to mark as "important event"
        if ($todayCampaign->count() > 0) {
            #return false;
        }

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableEvolutions = Mage::getSingleton('core/resource')->getTableName('fidelitas/evolutions');
        $write->delete($tableEvolutions, array('created_at = ?' => $day));

        $tableSummary = Mage::getSingleton('core/resource')->getTableName('fidelitas/summary');
        $write->delete($tableSummary, array('created_at = ?' => $day, 'list_id=?' => $list->getId(), 'segment_id=?' => $segmentId));

        $subs = Mage::getmodel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter('list', $list->getListnum())
                ->addFieldToFilter('customer_id', array('in' => $customersIds));

        foreach ($subs as $subscriber) {
            $data = $subscriber->getData();
            $data['segment_id'] = $segmentId;
            $data['list_id'] = $list->getId();
            $data['listnum'] = $list->getListnum();
            $data['created_at'] = $day;

            if ($campaign && $campaign->getId()) {
                $data['campaign_id'] = $campaign->getId();
            }

            $conversionsData = Mage::getModel('fidelitas/consegments')->getInfoForSegmentCustomer($data['customer_id'], $data['segment_id'], $list->getListnum());

            $dataInsert = array_merge($data, $conversionsData);

            Mage::getModel('fidelitas/evolutions')->setData($dataInsert)->save();

            Mage::getModel('fidelitas/segments_list')->setData($dataInsert)->save();
        }


        $collection = Mage::getModel('fidelitas/evolutions')->getCollection()
                ->addFieldToFilter('segment_id', $segmentId)
                ->addFieldToFilter('list_id', $list->getId())
                ->addFieldToFilter('created_at', $day);

        $summary = array();
        $summary['segment_id'] = $segmentId;
        $summary['created_at'] = $day;
        $summary['records'] = $collection->count();
        $summary['unique_conversions'] = 0;
        $summary['list_id'] = $list->getId();

        if ($campaign && $campaign->getId()) {
            $summary['campaign_id'] = $campaign->getId();
        }

        $summary['conversions_number'] = 0;
        $summary['conversions_amount'] = 0;
        $summary['unique_conversions'] = 0;
        $summary['conversions_average'] = 0;
        foreach ($collection as $item) {
            $summary['conversions_number'] += $item->getData('conversions_number');
            $summary['conversions_amount'] += $item->getData('conversions_amount');

            if ($summary['conversions_number'] > 0) {
                $summary['unique_conversions'] += 1;
            }
        }

        if ($summary['conversions_number'] > 0) {
            $summary['conversions_average'] = round($summary['conversions_amount'] / $summary['conversions_number'], 4);
        }


        $previousRecords = Mage::getModel('fidelitas/summary')->getCollection()
                ->addFieldToFilter('segment_id', $segmentId)
                ->addFieldToFilter('list_id', $list->getId())
                ->addFieldToFilter('created_at', array('lt' => $day))
                ->setOrder('created_at', 'DESC')
                ->setPageSize(1);

        if ($previousRecords->count() != 1) {
            $previousRecordsNumber = 0;
        } else {
            $previousRecordsNumber = $previousRecords->getFirstItem()->getData('records');
        }

        $summary['change'] = $summary['records'] - $previousRecordsNumber;

        Mage::getModel('fidelitas/summary')->setData($summary)->save();
    }

}
