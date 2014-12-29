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


class Licentia_Fidelitas_Model_Senders extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('fidelitas/senders');
    }

    public function getSenders($channel = 'email') {

        $channel = strtolower($channel);

        if ($channel == 'sms')
            $channel = 'telemovel';

        $return = array();
        $senders = Mage::getModel('fidelitas/senders')
                ->getCollection()
                ->getData();

        foreach ($senders as $sender)
        {
            if ($sender['channel'] == $channel) {
                $return[$sender['code']] = $sender['sender'];
            }
        }
        return $return;
    }

    public function cron() {
        $data = Mage::getModel('fidelitas/egoi')->getSenders()->getData();

        Mage::getModel('fidelitas/senders')
                ->getCollection()
                ->delete();

        foreach ($data as $sender)
        {
            $sender['code'] = $sender['fromid'];
            Mage::getModel('fidelitas/senders')->setData($sender)->save();
        }

        return $this;
    }

}