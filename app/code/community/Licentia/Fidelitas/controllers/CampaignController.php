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
class Licentia_Fidelitas_CampaignController extends Mage_Core_Controller_Front_Action {

    public function viewAction() {

        $url = Mage::getUrl('*/*/user', array('c' => $this->getRequest()->getParam('c'), 'uid' => '!uid'));
        echo '{{HTML LINK="' . $url . '<' . $url . '>"}}';
        die();
    }

    public function userAction() {

        $camp = $this->getRequest()->getParam('c');
        $u = $this->getRequest()->getParam('uid');
        $c = Mage::getModel('fidelitas/campaigns')->load($camp, 'hash');

        $list = Mage::getModel('fidelitas/lists')->load($c->getListnum(), 'listnum');
        Mage::app()->getStore()->setId($list->getFinalStoreId());

        $user = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter('list', $c->getListnum());

        if ($this->getRequest()->getParam('sid')) {
            $user->addFieldToFilter('subscriber_id', $this->getRequest()->getParam('sid'));
        } else {
            $user->addFieldToFilter('uid', $u);
        }

        $subscriber = new Varien_Object();
        if ($user->count() == 1) {
            $subscriber = $user->getFirstItem();
        }

        if ($subscriber->getId()) {
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            Mage::register('current_customer', $customer);
            Mage::register('fidelitas_current_subscriber', $subscriber);
            Mage::register('fidelitas_current_campaign', $c);

            if ((int) $customer->getStoreId() > 0 && $list->getFinalStoreId() != $customer->getStoreId()) {

                $sA = Mage::getModel('core/store')->load($customer->getStoreId());
                $groupB = Mage::app()->getGroup()->getId();

                if ($sA->getGroupId() == $groupB) {
                    Mage::app()->getStore()->setId($customer->getStoreId());
                }
            }
        } else {
            $customer = new Varien_Object;
        }

        Mage::dispatchEvent('fidelitas_campaign_view', array('campaign' => $c, 'customer' => $customer, 'subscriber' => $user));

        $text = Mage::helper('cms')->getBlockTemplateProcessor()->filter($c->getMessage());
        $vars = Licentia_Fidelitas_Model_Campaigns::getTemplateVars($subscriber);

        $text = str_replace(array_keys($vars), $vars, $text);
        $text .= ' <img width="1" height="1" src="' . Mage::getUrl('*/*/stat', array('c' => $this->getRequest()->getParam('c'), 'uid' => $u)) . '" border="0"> ';

        $doc = new DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $text);
        foreach ($doc->getElementsByTagName('a') as $link) {
            $urlParams = array('uid' => $subscriber->getUid(), 'fidcamp' => $camp, 'url' => base64_encode($link->getAttribute('href')));

            $link->setAttribute('href', Mage::getUrl('fidelitas/campaign/go/', $urlParams));
        }

        foreach ($doc->childNodes as $item) {
            if ($item->nodeType == XML_PI_NODE) {
                $doc->removeChild($item); // remove hack
                $doc->encoding = 'UTF-8'; // insert proper
            }
        }
        echo $doc->saveHTML();
        die();
    }

    public function goAction() {

        $request = $this->getRequest();
        $uid = $request->getParam('uid');
        $camp = $request->getParam('fidcamp');

        $url = base64_decode($request->getParam('url'));

        Mage::register('fidelitas_open_url', $url, true);

        if (!$camp && !$uid) {
            header('LOCATION:' . $url);
            exit;
        }

        $session = Mage::getSingleton('customer/session');
        $session->setFidelitasConversion(true);
        $session->setFidelitasConversionCampaign($camp);
        $session->setFidelitasConversionSubscriber($uid);

        $campaign = Mage::getModel('fidelitas/campaigns')->load($camp, 'hash');
        $subscriber = Mage::getModel('fidelitas/subscribers')->load($uid, 'uid');

        Mage::getModel('fidelitas/stats')->logClicks($campaign, $subscriber);
        Mage::getModel('fidelitas/urls')->logUrl($campaign, $subscriber, $url);

        $request->setParam('uid', null);
        $request->setParam('fidcamp', null);

        header('LOCATION:' . $url);
        #$this->_redirect($url);

        exit;
    }

    public function statAction() {
        $camp = $this->getRequest()->getParam('c');
        $u = $this->getRequest()->getParam('uid');
        $campaign = Mage::getModel('fidelitas/campaigns')->load($camp, 'hash');

        $user = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter('list', $campaign->getListnum())
                ->addFieldToFilter('uid', $u);

        $subscriber = new Varien_Object();
        if ($user->count() == 1) {
            $subscriber = $user->getFirstItem();
        }

        Mage::getModel('fidelitas/stats')->logViews($campaign, $subscriber);

        $im = imagecreatetruecolor(1, 1);
        imagefilledrectangle($im, 0, 0, 99, 99, 0xFFFFFF);
        header('Content-Type: image/gif');

        imagegif($im);
        imagedestroy($im);
        die();
    }

}
