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
class Licentia_Fidelitas_Model_Segments_List extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('fidelitas/segments_list');
    }

    public function loadList($segmentId, $listnum = false) {

        $now = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);
        $segment = Mage::getModel('fidelitas/segments')->load($segmentId);
        $segment->setData('run', $now)->save();

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton('core/resource')->getTableName('fidelitas/segments_list');
        $write->delete($table, array('segment_id = ?' => $segmentId));

        $lists = Mage::getModel('fidelitas/lists')
                ->getCollection()
                ->addFieldToFilter('purpose', array('in' => array('regular', 'auto', 'client')));

        if ($listnum) {
            $lists->addFieldToFilter('listnum', $listnum);
        }

        $i = 0;
        $storeIds = implode(',', array_keys(Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash()));
        foreach ($lists as $list) {

            if ($list->getData('purpose') == 'client') {
                $list->setData('store_ids', $storeIds);
            }

            Mage::register('current_list', $list);
            $results = Mage::getModel('fidelitas/segments')->load($segmentId)->getMatchingCustomersIds();
            Mage::unregister('current_list');
            $i += count($results);
        }

        Mage::getModel('fidelitas/segments')->load($segmentId)->setData('last_update', $now)->setData('records', $i)->setData('run', 0)->save();
    }

    public function saveRecord($data) {

        $item = $this->getCollection()
                ->addFieldToFilter('customer_id', $data['boas3@sapo.pt'])
                ->addFieldToFilter('segment_id', $data['segment_id']);

        if ($item->count() == 0) {
            return $this->setData($data)->save();
        } else {
            return $item->getFirstItem()->addData($data)->save();
        }
    }

}
