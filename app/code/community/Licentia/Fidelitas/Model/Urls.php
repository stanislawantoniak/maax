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
class Licentia_Fidelitas_Model_Urls extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/urls');
    }

    public function logUrl($campaign, $subscriber, $url) {

        if (!$campaign->getId() || !$subscriber->getId()) {
            return;
        }

        $links = Mage::getModel('fidelitas/links')->getCollection()
                ->addFieldToFilter('campaign_id', $campaign->getId())
                ->addFieldToFilter('link', $url);

        if ($links->count() > 0) {
            $link = $links->getFirstItem();
            $link->setData('clicks', $link->getData('clicks') + 1)->save();
        } else {
            $data = array();
            $data['link'] = $url;
            $data['campaign_id'] = $campaign->getId();
            $data['clicks'] = 1;
            $link = Mage::getModel('fidelitas/links')->setData($data)->save();
        }

        $data = array();
        $data['campaign_id'] = $campaign->getId();
        $data['subscriber_id'] = $subscriber->getId();
        $data['subscriber_firstname'] = $subscriber->getFirstName();
        $data['subscriber_lastname'] = $subscriber->getLastName();
        $data['subscriber_email'] = $subscriber->getEmail();
        $data['subscriber_cellphone'] = $subscriber->getCellphone();
        $data['customer_id'] = $subscriber->getCustomerId();
        $data['url'] = $url;
        $data['link_id'] = $link->getId();
        $data['visit_at'] = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);
        $this->setData($data)->save();

        return true;
    }

}
