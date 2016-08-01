<?php

class Snowdog_Freshmail_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * We have dynamic styles, so we need to compile it
     */
    public function compilePopupCss()
    {
        $path = Mage::getBaseDir('media') . DS . 'snowfreshmail' . DS . 'styles';
        if (!file_exists($path) || !is_dir($path)) {
            if (!@mkdir($path, 0777, true)) {
                Mage::throwException(Mage::helper('core')->__('Unable to create directory: %s', $path));
            }
        }

        $includeFilePath = Mage::getModuleDir('', 'Snowdog_Freshmail') . DS . 'styles.php';
        foreach (Mage::app()->getStores() as $store) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());
            ob_start();
            include $includeFilePath;
            $css = ob_get_clean();
            file_put_contents($path . DS . sprintf('popup_%s.css', $store->getCode()), $css, LOCK_EX);
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
    }

    /**
     * Get popup design config field value
     *
     * @param string    $field
     * @param mixed     $store
     *
     * @return string
     */
    public function getPopupProperty($field, $store = null)
    {
        $path = sprintf('snowfreshmail/popup_design/%s', $field);
        return Mage::getStoreConfig($path, $store);
    }

    /**
     * @return null|string
     */
    public function getLastHeartbeat()
    {
        $flag = Mage::getSingleton('snowfreshmail/flag_heartbeat')->loadSelf();
        return $flag->getFlagData();
    }

    /**
     * Diff between to times;
     *
     * @param $time1
     * @param $time2
     * @return int
     */
    public function dateDiff($time1, $time2 = null)
    {
        if (is_null($time2)) {
            $time2 = Mage::getModel('core/date')->gmtDate();
        }
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);
        return $time2 - $time1;
    }

    /**
     * Get RFM for customer
     * 
     * @param string $email
     *
     * @return array
     */
    public function getRfm($email)
    {
        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getSingleton('core/date');
        $now = $dateModel->gmtTimestamp();
        $createdAt = strtotime('-3 months', $now);

        /** @var Mage_Core_Model_Resource $resourceModel */
        $resourceModel = Mage::getSingleton('core/resource');
        $connection = $resourceModel->getConnection('core_read');
        $select = $connection->select()
            ->from(
                $resourceModel->getTableName('sales_flat_order'),
                array(
                    'total_orders' => 'COUNT(entity_id)',
                    'created_at' => 'MAX(created_at)',
                    'grand_total' => 'SUM(grand_total)',
                )
            )
            ->where('customer_email = ?', $email)
            ->where('created_at >= ?', date('Y-m-d', $createdAt));
        $result = $connection->fetchRow($select);

        if (!$result) {
            return array('r' => 0, 'f' => 0, 'm' => 0);
        }

        $grantTotalSum = $result['grand_total'];
        $totalOrders = $result['total_orders'];
        $lastCreatedAt = $now - strtotime($result['created_at']);

        switch (true) {
            case ($grantTotalSum > 0 && $grantTotalSum < 10):
                $m = 1;
                break;
            case ($grantTotalSum >= 10 && $grantTotalSum < 20):
                $m = 2;
                break;
            case ($grantTotalSum >= 20):
                $m = 3;
                break;
            default:
                $m = 0;
                break;
        }
        switch (true) {
            case ($totalOrders == 1):
                $f = 1;
                break;
            case ($totalOrders == 2 || $totalOrders == 3):
                $f = 2;
                break;
            case ($totalOrders > 3):
                $f = 3;
                break;
            default:
                $f = 0;
                break;
        }
        switch (true) {
            case ($lastCreatedAt < 604800):
                $r = 3;
                break;
            case ($lastCreatedAt < 2592000):
                $r = 2;
                break;
            case ($lastCreatedAt < 7862400):
                $r = 1;
                break;
            default:
                $r = 0;
                break;
        }

        return array('r' => $r, 'f' => $f, 'm' => $m);
    }

    /**
     * Translate Magento Newsletter status to Freshmail status
     * 
     * @param int
     *
     * @return int
     */
    public function getFreshmailStatus($status)
    {
        switch ((int)$status) {
            case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                $status = Snowdog_Freshmail_Model_ServiceManager::SUBSCRIBER_STATUS_SUBSCRIBED;
                break;
            case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
                $status = Snowdog_Freshmail_Model_ServiceManager::SUBSCRIBER_STATUS_NOT_ACTIVE;
                break;
            case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                $status = Snowdog_Freshmail_Model_ServiceManager::SUBSCRIBER_STATUS_UNSUBSCRIBED;
                break;
            case Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
                $status = Snowdog_Freshmail_Model_ServiceManager::SUBSCRIBER_STATUS_AWAITS_ACTIVATION;
                break;
            default:
                $status = Snowdog_Freshmail_Model_ServiceManager::SUBSCRIBER_STATUS_NOT_ACTIVE;
                break;
        }
        return $status;
    }
    
    /**
     * Retrieve customer segments ids
     *
     * @param int $customerId
     * @param int $websiteId
     *
     * @return array
     */
    public function getCustomerSegmentIds($customerId, $websiteId)
    {
        $segmentIds = Mage::getResourceModel(
            'enterprise_customersegment/customer'
        )->getCustomerWebsiteSegments(
            $customerId,
            $websiteId
        );

        return $segmentIds;
    }

    /**
     * Retrieve api item statuses array
     *
     * @return array
     */
    public function getItemStatusesArray()
    {
        return array(
            Snowdog_Freshmail_Model_Api_Request::STATUS_FAILED => Mage::helper('snowfreshmail')->__('Failed'),
            Snowdog_Freshmail_Model_Api_Request::STATUS_SUCCESS => Mage::helper('snowfreshmail')->__('Success'),
            Snowdog_Freshmail_Model_Api_Request::STATUS_NEW => Mage::helper('snowfreshmail')->__('New'),
            Snowdog_Freshmail_Model_Api_Request::STATUS_EXPIRED => Mage::helper('snowfreshmail')->__('Expired'),
        );
    }

    /**
     * Retrieve api item statuses options
     *
     * @return array
     */
    public function getItemStatusesOptionsArray()
    {
        $options = array();
        foreach ($this->getStatusesArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label,
            );
        }

        return $options;
    }

    /**
     * Decorate item status
     *
     * @param Snowdog_Freshmail_Model_Api_Request $item
     *
     * @return string
     */
    public function decorateItemStatus($item)
    {
        $classes = '';

        switch ($item->getStatus()) {
            case Snowdog_Freshmail_Model_Api_Request::STATUS_SUCCESS:
                $classes = 'bar-green';
                break;
            case Snowdog_Freshmail_Model_Api_Request::STATUS_NEW:
                $classes = 'bar-gray';
                break;
            case Snowdog_Freshmail_Model_Api_Request::STATUS_FAILED:
                $classes = 'bar-red';
                break;
            case Snowdog_Freshmail_Model_Api_Request::STATUS_EXPIRED:
                $classes = 'bar-lightgray';
                break;
        }

        if ($classes) {
            return sprintf(
                '<span class="%s"><span>%s</span></span>',
                $classes,
                $item->getStatusLabel()
            );
        }

        return $item->getStatusLabel();
    }
}
