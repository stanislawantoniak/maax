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
class Licentia_Fidelitas_Model_Stats extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/stats');
    }

    public function logViews($campaign, $subscriber) {

        if (!$campaign->getId() || !$subscriber->getId())
            return;


        $date = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);

        $collection = Mage::getModel('fidelitas/stats')
                ->getCollection()
                ->addFieldToFilter('subscriber_id', $subscriber->getId())
                ->addFieldToFilter('campaign_id', $campaign->getId());

        if ($collection->count() == 1) {

            $stat = $collection->getFirstItem();
            $stat->setData('view_at', $date);
            $stat->setData('views', $stat->getData('views') + 1)
                    ->save();

            $campaign->setData('views', $campaign->getData('views') + 1)
                    ->save();

            if ($campaign->getParentId()) {
                if ($parent = Mage::getModel('fidelitas/campaigns')->load($campaign->getParentId())) {
                    $parent->setData('views', $parent->getData('views') + 1);
                    $parent->save();
                }
            }
            
            if ($campaign->getSplitId()) {
                if ($split = Mage::getModel('fidelitas/splits')->load($campaign->getSplitId())) {
                    $split->setData('views_' . $campaign->getSplitVersion(), $split->getData('views_' . $campaign->getSplitVersion()) + 1);
                    $split->save();
                }
            }

        } else {

            $data = array();
            $data['campaign_id'] = $campaign->getId();
            $data['subscriber_id'] = $subscriber->getId();
            $data['customer_id'] = $subscriber->getCustomerId();
            $data['view_at'] = $date;
            $data['views'] = 1;
            $stat = Mage::getModel('fidelitas/stats')->setData($data)->save();

            $campaign->setData('views', $campaign->getData('views') + 1)
                    ->setData('unique_views', $campaign->getData('unique_views') + 1)
                    ->save();

            Mage::getModel('fidelitas/autoresponders')->newView($subscriber, $campaign);

            if ($campaign->getParentId()) {
                if ($parent = Mage::getModel('fidelitas/campaigns')->load($campaign->getParentId())) {
                    $parent->setData('views', $parent->getData('views') + 1);
                    $parent->setData('unique_views', $parent->getData('unique_views') + 1);
                    $parent->save();
                }
            }

            if ($campaign->getSplitId()) {
                if ($split = Mage::getModel('fidelitas/splits')->load($campaign->getSplitId())) {
                    $split->setData('views_' . $campaign->getSplitVersion(), $split->getData('views_' . $campaign->getSplitVersion()) + 1);
                    $split->save();
                }
            }
        }
    }

    public function logClicks($campaign, $subscriber) {

        if (!$campaign->getId() || !$subscriber->getId())
            return;

        $session = Mage::getSingleton('customer/session');

        if ($session->getData('fidelitas_' . $campaign->getId() . '_click') == true)
            return;


        $session->setData('fidelitas_' . $campaign->getId() . '_click', true);

        $date = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);

        $collection = Mage::getModel('fidelitas/stats')
                ->getCollection()
                ->addFieldToFilter('subscriber_id', $subscriber->getId())
                ->addFieldToFilter('campaign_id', $campaign->getId());

        if ($collection->count() == 1) {

            $stat = $collection->getFirstItem();
            $stat->setData('clicks', $stat->getData('clicks') + 1);

            if ($stat->getClickAt() === null) {
                $stat->setData('click_at', $date);
            }

            if ($stat->getData('unique_clicks') === null) {
                $stat->setData('unique_clicks', 1);
                $campaign->setData('unique_clicks', $campaign->getData('unique_clicks') + 1);

                Mage::getModel('fidelitas/autoresponders')->newClick($subscriber, $campaign);

                if ($campaign->getParentId()) {
                    if ($parent = Mage::getModel('fidelitas/campaigns')->load($campaign->getParentId())) {
                        $parent->setData('clicks', $parent->getData('clicks') + 1);
                        $parent->setData('unique_clicks', $parent->getData('unique_clicks') + 1);
                        $parent->save();
                    }
                }
                if ($campaign->getSplitId()) {
                    if ($split = Mage::getModel('fidelitas/splits')->load($campaign->getSplitId())) {
                        $split->setData('clicks_' . $campaign->getSplitVersion(), $split->getData('clicks_' . $campaign->getSplitVersion()) + 1);
                        $split->save();
                    }
                }
            }

            $stat->save();

            $campaign->setData('clicks', $campaign->getData('clicks') + 1);
            $campaign->save();

            if ($campaign->getParentId()) {
                if ($parent = Mage::getModel('fidelitas/campaigns')->load($campaign->getParentId())) {
                    $parent->setData('clicks', $parent->getData('clicks') + 1);
                    $parent->save();
                }
            }
            if ($campaign->getSplitId()) {
                if ($split = Mage::getModel('fidelitas/splits')->load($campaign->getSplitId())) {
                    $split->setData('clicks_' . $campaign->getSplitVersion(), $split->getData('clicks_' . $campaign->getSplitVersion()) + 1);
                    $split->save();
                }
            }
        } else {

            $data = array();
            $data['campaign_id'] = $campaign->getId();
            $data['subscriber_id'] = $subscriber->getId();
            $data['customer_id'] = $subscriber->getCustomerId();
            $data['click_at'] = $date;
            $data['clicks'] = 1;
            $stat = Mage::getModel('fidelitas/stats')->setData($data)->save();

            Mage::getModel('fidelitas/autoresponders')->newClick($subscriber, $campaign);

            $campaign->setData('clicks', $campaign->getData('clicks') + 1)
                    ->setData('unique_clicks', $campaign->getData('unique_clicks') + 1)
                    ->save();

            if ($campaign->getParentId()) {
                if ($parent = Mage::getModel('fidelitas/campaigns')->load($campaign->getParentId())) {
                    $parent->setData('clicks', $parent->getData('clicks') + 1);
                    $parent->setData('unique_clicks', $parent->getData('unique_clicks') + 1);
                    $parent->save();
                }
            }
            if ($campaign->getSplitId()) {
                if ($split = Mage::getModel('fidelitas/splits')->load($campaign->getSplitId())) {
                    $split->setData('clicks_' . $campaign->getSplitVersion(), $split->getData('clicks_' . $campaign->getSplitVersion()) + 1);
                    $split->save();
                }
            }
        }
    }

}
