<?php

class Snowdog_Freshmail_Helper_Api extends Mage_Core_Helper_Abstract
{
    /**
     * Identifier of subscription lists cache
     */
    const LISTS_CACHE_ID = 'snowfreshmail_lists';

    /**
     * Status flag instance
     *
     * @var Snowdog_Freshmail_Model_Flag_Api_Status
     */
    protected $_statusFlag;

    /**
     * Default fields supported by this module
     *
     * @var array
     */
    protected $_defaultFields = array(
        'store_id' => 'FMA_STORE_ID',
        'website_id' => 'FMA_WEBSITE_ID',
        'created_in' => 'FMA_CREATED_IN',
        'group_id' => 'FMA_GROUP_ID',
    );

    /**
     * @var array
     */
    protected $_metaFields = array(
        'FMA_R', // Recency
        'FMA_F', // Frequency
        'FMA_M', // Monetary
    );

    /**
     * @return array
     */
    public function getDefaultFields()
    {
        return $this->_defaultFields;
    }

    /**
     * Retrieve a status flag
     *
     * @return Snowdog_Freshmail_Model_Flag_Api_Status
     */
    public function getStatusFlag()
    {
        if (null === $this->_statusFlag) {
            $this->_statusFlag = Mage::getModel(
                'snowfreshmail/flag_api_status'
            )->loadSelf();
        }
        return $this->_statusFlag;
    }

    /**
     * Retrieve a connection status
     * It does not make api connection.
     *
     * @return bool
     */
    public function isConnected()
    {
        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');
        if (!$configModel->getKey() || !$configModel->getSecret()) {
            return false;
        }
        if (!$this->getStatusFlag()->hasFlagData()) {
            return false;
        }
        return (bool) $this->getStatusFlag()->getFlagData();
    }

    /**
     * Retrieve subscription lists
     *
     * @return array
     */
    public function getLists()
    {
        $cache = Mage::app()->getCacheInstance();
        $data = $cache->load(self::LISTS_CACHE_ID);
        if ($data === false) {
            $data = Mage::getModel('snowfreshmail/serviceManager')->getLists();
            $cache->save(serialize($data), self::LISTS_CACHE_ID);
        } else {
            $data = unserialize($data);
        }

        return $data;
    }

    /**
     * Check specified emails exist in FM
     *
     * @param string    $listHash
     * @param array     $emails
     *
     * @return array
     */
    public function checkSubscribersExist($listHash, array $emails)
    {
        $result = array();

        /** @var Snowdog_Freshmail_Model_ServiceManager $serviceManager */
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        $response = $serviceManager->getMultipleSubscribers($listHash, $emails);

        if (isset($response['data'])) {
            if (isset($response['data']['errors'])) {
                foreach ($response['data']['errors'] as $item) {
                    $result[$item['email']] = false;
                }
                unset($response['data']['errors']);
            }
            foreach ($response['data'] as $item) {
                $result[$item['email']] = true;
            }
        }

        return $result;
    }

    /**
     * Update subscribers status
     *
     * @param string        $listHash
     * @param string|array  $emails
     * @param int           $status
     */
    public function updateSubscriberStatus($listHash, $emails, $status)
    {
        /** @var Snowdog_Freshmail_Model_ServiceManager $serviceManager */
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');

        if (!is_array($emails)) {
            $emails = array($emails);
        }

        $subscribers = array();
        foreach ($emails as $email) {
            $subscribers[] = array('email' => $email);
        }
        $serviceManager->editMultipleSubscribers(
            $listHash,
            $subscribers,
            Mage::helper('snowfreshmail')->getFreshmailStatus($status)
        );
    }

    /**
     * Checking and setting custom fields
     *
     * @param string $listHash
     */
    public function initFields($listHash)
    {
        $this->_initMetaFields($listHash);
        $this->_initDefaultFields($listHash);
        $this->_initCustomFields($listHash);

        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');
        if ($coreHelper->isModuleEnabled('Enterprise_CustomerSegment')) {
            $this->_initCustomerSegmentFields($listHash);
        }
    }

    /**
     * Create default fields in Freshmail
     *
     * @param string $listHash
     */
    protected function _initDefaultFields($listHash)
    {
        /** @var Snowdog_Freshmail_Model_ServiceManager $serviceManager */
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        foreach ($this->_defaultFields as $fieldName) {
            $serviceManager->addField($listHash, $fieldName);
        }
    }

    /**
     * @param string $listHash
     */
    protected function _initMetaFields($listHash)
    {
        /** @var Snowdog_Freshmail_Model_ServiceManager $serviceManager */
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        foreach ($this->_metaFields as $fieldName) {
            $serviceManager->addField($listHash, $fieldName);
        }
    }

    /**
     * Create fields for Magento Enterprise Edition in Freshmail
     *
     * @param string $listHash
     *
     * @throws Exception
     */
    protected function _initCustomerSegmentFields($listHash)
    {
        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');
        $mappings = $configModel->getCustomerSegmentMappings();

        /** @var Snowdog_Freshmail_Model_ServiceManager $serviceManager */
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        foreach ($mappings as $mapping) {
            $serviceManager->addField(
                $listHash,
                $mapping['target_field'],
                'numeric'
            );
        }
    }

    /**
     * Init custom fields
     *
     * @param string $listHash
     */
    protected function _initCustomFields($listHash)
    {
        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');
        $mappings = $configModel->getCustomFieldMappings();

        /** @var Snowdog_Freshmail_Model_ServiceManager $serviceManager */
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        foreach ($mappings as $mapping) {
            $serviceManager->addField($listHash, $mapping['target_field']);
        }
    }

    /**
     * Convert subscriber data to freshmail format
     *
     * @param string    $email
     * @param array     $data
     * @param mixed     $store
     *
     * @return array
     */
    public function convertSubscriberData($email, $data = array(), $store = null)
    {
        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');
        $convertedData = array();
        if ($data) {
            foreach ($this->_defaultFields as $attribute => $target) {
                if (isset($data[$attribute])) {
                    $tag = strtolower($target);
                    $convertedData[$tag] = $data[$attribute];
                }
            }

            $mappings = $configModel->getCustomFieldMappings($store);
            foreach ($mappings as $mapping) {
                $targetFieldName = strtolower($mapping['target_field']);
                $sourceFieldName = $mapping['source_field'];
                if (isset($data[$sourceFieldName])) {
                    $convertedData[$targetFieldName] = $data[$sourceFieldName];
                }
            }

            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');
            if ($coreHelper->isModuleEnabled('Enterprise_CustomerSegment')) {
                if (isset($data['segment_ids'])) {
                    if (!is_array($data['segment_ids'])) {
                        $data['segment_ids'] = array($data['segment_ids']);
                    }
                    $customerSegmentIds = array_flip($data['segment_ids']);
                    $mappings = $configModel->getCustomerSegmentMappings($store);
                    foreach ($mappings as $mapping) {
                        $segmentId = $mapping['segment_id'];
                        if (isset($customerSegmentIds[$segmentId])) {
                            $value = 1;
                        } else {
                            $value = 0;
                        }
                        $targetFieldName = strtolower($mapping['target_field']);
                        $convertedData[$targetFieldName] = $value;
                    }
                }
            }

            $rfm = Mage::helper('snowfreshmail')->getRfm($email);
            $convertedData['fma_r'] = $rfm['r'];
            $convertedData['fma_f'] = $rfm['f'];
            $convertedData['fma_m'] = $rfm['m'];
        }

        return $convertedData;
    }
}
