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
class Licentia_Fidelitas_Model_Reports extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('fidelitas/reports');
    }

    public function cron() {

        return;
        $model = Mage::getModel('fidelitas/campaigns')->getCollection()->getData();

        #REMOVE OLD ENTRIES
        Mage::getModel('fidelitas/reports')->getCollection()->delete();

        foreach ($model as $campaign) {
            $report = Mage::getModel('fidelitas/egoi');
            $report->setData('campaign', $campaign['hash']);
            $result = $report->getReports()->getData();

            if (!array_key_exists('ERROR', $result[0])) {

                $result = $result[0];

                foreach ($result as $key => $value) {
                    if (is_array($value)) {
                        $result[$key] = serialize($value);
                    }
                }

                $result['updated_at'] = new Zend_Db_Expr('NOW()');
                Mage::getModel('fidelitas/reports')->setData($result)->save();
            }
        }
    }

    public function refresh($hash) {

        $report = Mage::getModel('fidelitas/egoi');
        $report->setData('campaign', $hash);
        $result = $report->getReports()->getData();

        if (!array_key_exists('ERROR', $result[0])) {

            $result = $result[0];
            $campaign = Mage::getModel('fidelitas/campaigns')->load($hash, 'hash');

            foreach ($result as $key => $value) {
                if (is_array($value)) {
                    $result[$key] = serialize($value);
                }
            }

            $save = false;
            if ($campaign->getId() && $campaign->getData('views') <= $result['views']) {
                $campaign->setData('views', $result['views']);
                $save = true;
            } else {
                $result['views'] = $campaign->getData('views');
            }

            if ($campaign->getId() && $campaign->getData('unique_views') <= $result['unique_views']) {
                $campaign->setData('unique_views', $result['unique_views']);
                $save = true;
            } else {
                $result['unique_views'] = $campaign->getData('unique_views');
            }

            if ($campaign->getId() && $campaign->getData('unique_clicks') <= $result['unique_clicks']) {
                $campaign->setData('unique_clicks', $result['unique_clicks']);
                $save = true;
            } else {
                $result['unique_clicks'] = $campaign->getData('unique_clicks');
            }

            if ($campaign->getId() && $campaign->getData('clicks') <= $result['clicks_sub']) {
                $campaign->setData('clicks', $result['clicks_sub']);
                $save = true;
            } else {
                $result['clicks_sub'] = $campaign->getData('clicks');
            }
            if ($campaign->getId() && $campaign->getData('sent') <= $result['sent']) {
                $campaign->setData('sent', $result['sent']);
                $save = true;
            }
            if ($save) {
                $campaign->save();
            }

            $model = Mage::getModel('fidelitas/reports')->load($hash, 'hash');

            $result['updated_at'] = new Zend_Db_Expr('NOW()');
            return $model->addData($result)->save();
        }

        return false;
    }

}
