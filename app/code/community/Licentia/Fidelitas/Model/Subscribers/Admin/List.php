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
class Licentia_Fidelitas_Model_Subscribers_Admin_List extends Mage_Core_Model_Abstract {

    public function toOptionArray() {


        $list = Mage::getModel('fidelitas/lists')
                ->getCollection()
                ->addFieldToFilter('store_id', '0')
                ->getData();

        if (!isset($list[0])) {
            $data = array('status' => '1', 'nome' => 'Notifications', 'internal_name' => 'Used For Configuration', 'canal_sms' => 1, 'canal_email' => 1, 'store_id' => '0');
            Mage::getModel('fidelitas/lists')->setData($data)->save();

            Mage::getSingleton('adminhtml/session')->addNotice('Please add Subscribers to the Mag Administrators List in order to be possible to select receivers');

            return array(array('label' => 'None', 'value' => '0'));
        }

        $listId = $list[0]['listnum'];

        $model = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToSelect('cellphone')
                ->addFieldToFilter('list', $listId);

        $result = array();

        foreach ($model as $subs) {
            if (strlen($subs->getCellphone()) == 0)
                continue;

            $name = $subs->getFirstName() . ' ' . $subs->getLastName();

            $result[] = array('value' => $subs->getCellphone(), 'label' => $name);
        }

        if (count($result) == 0) {
            $result[] = array('label' => 'No Subscribers With Cellphone', 'value' => '0');
        }

        return $result;
    }

}
