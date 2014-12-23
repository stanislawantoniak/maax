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
class Licentia_Fidelitas_Model_Templates extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/templates');
    }

    function cron() {
        $subscribers = array();
        $result = array();

        $fid = Mage::getModel('fidelitas/egoi');

        $lists = $fid->getLists()->getData();

        foreach ($lists as $list) {
            $fid->setData(array('listID' => $list['listnum'], 'subscriber' => 'all_subscribers'));
            $result[$list['listnum']] = $fid->getSubscriberData()->getData();
        }

        foreach ($result as $list => $subscribers) {
            foreach ($subscribers as $subscriber) {
                $subscriberData = array_change_key_case($subscriber, CASE_LOWER);
                Mage::getModel('fidelitas/subscribers')->setData($subscriberData)->save();
            }
        }
    }

    public function getOptionArray() {

        $list = Mage::getModel('fidelitas/templates')
                ->getCollection()
                ->addFieldToSelect('template_id')
                ->addFieldToSelect('name');

        $result = array();


        foreach ($list as $template) {
            $result[] = array('value' => $template->getId(), 'label' => $template->getName());
        }

        return $result;
    }

}
