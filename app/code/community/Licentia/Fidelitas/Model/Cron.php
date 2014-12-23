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
class Licentia_Fidelitas_Model_Cron extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/cron');
    }

    public function canSendCron($report, $scope, $value) {

        if ($report['enable'] == 0) {
            return false;
        }

        $date = Mage::app()->getLocale()->date();
        $runtime = Mage::app()
                ->getLocale()
                ->date()
                ->setHour($report['runtime'])
                ->setMinute(0)
                ->setSecond(0)
                ->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);

        $now = $date->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);

        if ($now < $runtime) {
            return false;
        }

        if ($report['day'] == 'yesterday') {
            $date->subDay(1);
        }

        $period = $date->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);

        $collection = $this->getCollection()
                ->addFieldToFilter('scope', $scope)
                ->addFieldToFilter('value', $value)
                ->addFieldToFilter('period', array('from' => $period));

        if ($collection->count() > 0) {
            return false;
        }


        return true;
    }

    public function sendStats($report, $scope, $value) {

        $connection = Mage::getSingleton('core/resource')->getConnection('default_setup');
        $final = array();

        if ($scope == 'global') {
            $final['Scope'] = 'Global';
            $storeIds = false;
        }

        if ($scope == 'websites') {
            $storeIds = array();
            foreach (Mage::app()->getWebsites() as $website) {
                if ($website->getId() != $value) {
                    continue;
                }


                $final['Scope'] = $website->getName();
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        $storeIds[] = $store->getId();
                    }
                }
            }
        }

        if ($scope == 'stores') {
            $final['Scope'] = Mage::app()->getStore($value)->getName();
            $storeIds = array($value);
        }

        $date = Mage::app()->getLocale()->date();
        if ($report['day'] == 'yesterday') {
            $date->subDay(1);
        }

        $period = $date->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);
        $smsDate = $date->get('dd EEE');

        $table = Mage::getSingleton('core/resource')->getTableName('customer_entity');

        $select = new Zend_Db_Select($connection);
        $select->from($table, array())
                ->where('created_at = ?', $period)
                ->columns(array('total' => new Zend_Db_Expr('COUNT(*)')))
                ->where('is_active=?', 1);

        if (is_array($storeIds)) {
            $select->where('store_id IN (?)', $storeIds);
        }

        $result = $select->query();
        $total = $result->fetchAll();

        $final['New Customers'] = (int) $total[0]['total'];


        $table = Mage::getSingleton('core/resource')->getTableName('sales_order_aggregated_created');
        $select = new Zend_Db_Select($connection);
        $desc = $connection->describeTable($table);

        $select->from($table, array('*'))
                ->where('period = ?', $period);

        if (is_array($storeIds)) {
            $select->where('store_id IN (?)', $storeIds);
        }

        foreach ($desc as $key => $field) {
            if (in_array($key, array('id', 'period', 'store_id', 'order_status'))) {
                continue;
            }
            $select->columns(array($field['COLUMN_NAME'] => new Zend_Db_Expr("SUM({$field['COLUMN_NAME']})")));
        }

        $result = $select->query();
        $total = $result->fetchAll();

        $final['New Orders'] = (int) round($total[0]['orders_count']);
        $final['Amt Invoiced'] = (int) round($total[0]['total_invoiced_amount']);
        $final['Income Amt'] = (int) round($total[0]['total_income_amount']);
        $final['Revenue Amt'] = (int) round($total[0]['total_revenue_amount']);
        $final['Profit Amt'] = (int) round($total[0]['total_profit_amount']);

        $msg = Mage::helper('fidelitas')->__("Mag report: %s; ", $smsDate);

        foreach ($final as $key => $smsValue) {

            $msg .= Mage::helper('fidelitas')->__($key) . ': ' . $smsValue . '; ';
        }

        $msg = rtrim($msg, '; ');

        $list = Mage::getModel('fidelitas/lists')->getAdminList();


        if (!isset($report['recipients']) || !isset($report['sms_number'])) {
            return false;
        }

        $data = array();
        $data['cellphone'] = explode(',', $report['recipients']);
        $data['message'] = substr($msg, 0, 160);
        $data['auto'] = '1';
        $data['subject'] = 'Daily Stats:' . $scope;
        $data['listID'] = $list->getListnum();

        $localData = array();
        $localData['scope'] = $scope;
        $localData['value'] = $value;
        $localData['period'] = $period;
        $localData['status'] = 'sent';
        $this->setData($localData)->save();

        return Mage::getModel('fidelitas/egoi')->setData($data)->sendSMS();
    }

}
