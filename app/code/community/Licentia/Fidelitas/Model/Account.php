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
class Licentia_Fidelitas_Model_Account extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/account');
    }

    function cron() {

        $fid = Mage::getModel('fidelitas/egoi');
        $result = $fid->getAccountDetails()->getData();
        $result[0]['account_id'] = 1;
        $account = Mage::getModel('fidelitas/account')->load(1);

        if ($account->getId()) {
            $account->setData($result[0])->save();
        } else {
            Mage::getModel('fidelitas/account')->setData($result[0])->save();
        }
    }

}
