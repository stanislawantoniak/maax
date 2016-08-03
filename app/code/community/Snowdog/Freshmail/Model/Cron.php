<?php

class Snowdog_Freshmail_Model_Cron
{
    /**
     * Max subscribers to sync in a single batch execution
     *
     * The value is restricted by api.
     * We can send max 100 subscribers in a single api request.
     *
     * @const int
     */
    const SYNC_BATCH_LIMIT = 100;

    /**
     * Max requests to process in a single cron running
     *
     * @const int
     */
    const PROCESS_MAX_REQUESTS = 10;

    /**
     * Logger instance
     *
     * @var Snowdog_Freshmail_Model_Log_Adapter
     */
    protected $_logger;

    /**
     * Core resource singleton
     *
     * @var Mage_Core_Model_Resource
     */
    protected $_resourceModel;

    /**
     * Read connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_readConnection;

    /**
     * Sync flag instance
     *
     * @var Mage_Core_Model_Flag
     */
    protected $_syncFlag;

    /**
     * Init logger instance
     */
    public function __construct()
    {
        $this->_logger = Mage::getModel(
            'snowfreshmail/log_adapter',
            'snowfreshmail_cron.log'
        );
        $this->_resourceModel = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resourceModel->getConnection('core_read');
        $this->_syncFlag = Mage::getSingleton('snowfreshmail/flag_sync')
            ->loadSelf();
    }

    public function cleanRequestLogs()
    {
        $saveDays = Mage::getSingleton('snowfreshmail/config')->getCleanQueueAfterDay();
        if (!$saveDays || !is_numeric($saveDays)) {
            return;
        }

        $time = Mage::getSingleton('core/date')->gmtTimestamp() - ($saveDays * (86400 / 24));
        Mage::getSingleton('snowfreshmail/api_requestManager')->clean($time);
    }

    /**
     * @param int $fromSubscriberId
     *
     * @return array
     */
    protected function _loadSubscriberData($fromSubscriberId)
    {
        $select = $this->_readConnection->select()
            ->from($this->_resourceModel->getTableName('newsletter/subscriber'))
            ->where('subscriber_id > ?', $fromSubscriberId)
            ->limit(self::SYNC_BATCH_LIMIT);
        return $this->_readConnection->fetchAssoc($select);
    }

    /**
     * Sync subscribers of a single store view
     *
     * @param int   $storeId
     * @param array $subscribers
     *
     * @throws Mage_Core_Exception
     */
    protected function _syncStoreSubscribers($storeId, $subscribers)
    {
        /** @var Snowdog_Freshmail_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('snowfreshmail/api');
        /** @var Snowdog_Freshmail_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('snowfreshmail');
        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');

        $listHash = $configModel->getListHash($storeId);
        $emails = array_keys($subscribers);
        $emailsByState = $apiHelper->checkSubscribersExist($listHash, $emails);

        $customerIds = array();
        $subscriberIds = array();
        foreach ($subscribers as $item) {
            if ($item['customer_id']) {
                $customerIds[] = $item['customer_id'];
            } else {
                $subscriberIds[] = $item['subscriber_id'];
            }
        }

        $select = $this->_readConnection->select()
            ->from($this->_resourceModel->getTableName('customer/entity'))
            ->where('entity_id IN (?)', $customerIds);
        $result = $this->_readConnection->fetchAll($select);
        $customerData = array();
        foreach ($result as $row) {
            $customerData[$row['entity_id']] = $row;
        }

        $select = $this->_readConnection->select()
            ->from($this->_resourceModel->getTableName('snowfreshmail/custom_data'))
            ->where('subscriber_id IN (?)', $subscriberIds);
        $result = $this->_readConnection->fetchAll($select);
        $customData = array();
        foreach ($result as $row) {
            $customData[$row['subscriber_id']] = unserialize($row['subscriber_data']);
        }

        $attributes = array();
        foreach (array_keys($apiHelper->getDefaultFields()) as $attribute) {
            $attributes[] = $attribute;
        }
        $mappings = $configModel->getCustomFieldMappings();
        foreach ($mappings as $mapping) {
            $attributes[] = $mapping['source_field'];
        }
        Mage::getSingleton('eav/config')->preloadAttributes('customer', $attributes);
        foreach ($attributes as $attributeCode) {
            /** @var Mage_Customer_Model_Attribute $attribute */
            $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
            if ($attribute->isStatic()) {
                continue;
            }
            $select = $this->_readConnection->select()
                ->from($attribute->getBackendTable(), array('entity_id', 'value'))
                ->where('attribute_id = ?', $attribute->getId())
                ->where('entity_id IN (?)', $customerIds);
            $result = $this->_readConnection->fetchAll($select);
            foreach ($result as $row) {
                $customerData[$row['entity_id']][$attributeCode] = $row['value'];
            }
        }

        $toAdd = array();
        $toEdit = array();
        $toActivate = array();
        $segmentsEnabled = Mage::helper('core')->isModuleEnabled('Enterprise_CustomerSegment');
        foreach ($subscribers as $email => $subscriberData) {
            $data = array();
            $subscriberId = $subscriberData['subscriber_id'];
            $customerId = $subscriberData['customer_id'];
            if ($customerId && isset($customerData[$customerId])) {
                $data = $customerData[$customerId];
                if ($segmentsEnabled) {
                    $segments = $dataHelper->getCustomerSegmentIds($customerId, $subscriberData['website_id']);
                    $subscriberData['segment_ids'] = $segments;
                }
            } elseif (isset($customData[$subscriberId])) {
                $data = $customData[$subscriberId];
            }
            $data = array(
                'email' => $email,
                'custom_fields' => $apiHelper->convertSubscriberData($email, $data, $subscriberData['store_id']),
            );
            $exists = $emailsByState[$email];
            if ($exists) {
                $toEdit[] = $data;
            } else {
                $toAdd[] = $data;
            }
            if (Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED === (int) $subscriberData['subscriber_status']) {
                $toActivate[] = $email;
            }
        }

        /** @var Snowdog_Freshmail_Model_ServiceManager $serviceManager */
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        Mage::helper('snowfreshmail/api')->initFields($listHash);
        if ($toAdd) {
            $serviceManager->addMultipleSubscribers($listHash, $toAdd);
        }
        if ($toEdit) {
            $serviceManager->editMultipleSubscribers($listHash, $toEdit);
        }
        if ($toActivate) {
            $apiHelper->updateSubscriberStatus(
                $listHash,
                $toActivate,
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED
            );
        }
    }

    /**
     * Synchronize all stores subscribers
     *
     * @return int
     *
     * @throws Exception
     */
    public function runSubscribersSyncBatch()
    {
        if (!Mage::helper('snowfreshmail/api')->isConnected()) {
            return 0;
        }

        $lastSubscriberId = (int)$this->_syncFlag->getFlagData();
        $subscribers = $this->_loadSubscriberData($lastSubscriberId);
        if (!$subscribers) {
            if (!$lastSubscriberId) {
                return 0;
            }
            // Reset flag
            // and start check from the first subscriber
            $this->_syncFlag->delete();
            $lastSubscriberId = 0;
            $subscribers = $this->_loadSubscriberData($lastSubscriberId);
        }

        try {
            $subscribersByEmail = array();
            foreach ($subscribers as $subscriberData) {
                $storeId = $subscriberData['store_id'];
                $subscriberEmail = $subscriberData['subscriber_email'];
                $subscribersByEmail[$storeId][$subscriberEmail] = $subscriberData;
            }

            foreach ($subscribersByEmail as $storeId => $storeSubscribers) {
                $this->_syncStoreSubscribers($storeId, $storeSubscribers);
            }

            $subscriberIds = array_keys($subscribers);
            $lastSubscriberId = array_pop($subscriberIds);
            $this->_syncFlag->setFlagData($lastSubscriberId);
            $this->_syncFlag->save();
            return count($subscribers);
        } catch (Exception $e) {
            $this->_logger->log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Process requests
     */
    public function processRequests()
    {
        if (!Mage::helper('snowfreshmail/api')->isConnected()) {
            return;
        }

        $requestManager = Mage::getSingleton('snowfreshmail/api_requestManager');
        $collection = Mage::getModel('snowfreshmail/api_request')
            ->getCollection()
            ->addFieldToFilter('status', array('in' => array(
                Snowdog_Freshmail_Model_Api_Request::STATUS_NEW,
                Snowdog_Freshmail_Model_Api_Request::STATUS_FAILED,
            )))
            ->setOrder('request_id', 'ASC')
            ->setOrder('processed_at', 'ASC')
            ->setPageSize(self::PROCESS_MAX_REQUESTS);
        foreach ($collection as $request) {
            $requestManager->run($request);
        }

        $flag = Mage::getSingleton('snowfreshmail/flag_heartbeat')
            ->loadSelf()
            ->setFlagData(Mage::getSingleton('core/date')->gmtDate());
        $flag->save();
    }
}
