<?php

class Snowdog_Freshmail_Model_Observer
{
    /**
     * Append dynamically generated css files
     */
    public function layoutGenerateBlocksAfter()
    {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::app()->getLayout();
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $layout->getBlock('head');
        if (!$headBlock) {
            return;
        }
        $filePath = Mage::getBaseUrl('media') . 'snowfreshmail/styles/'
           . 'popup_' . Mage::app()->getStore()->getCode() . '.css';
        $headBlock->addItem('link_rel', $filePath, 'rel="stylesheet"');
    }

    /**
     * Alert about cron issues
     */
    public function predispatchAdminhtmlNewsletterSubscriberIndex()
    {
        $lastHeartbeat = Mage::helper('snowfreshmail')->getLastHeartbeat();
        $adminhtml = Mage::getSingleton('adminhtml/session');
        if (is_null($lastHeartbeat)) {
            $adminhtml->addError(Mage::helper('snowfreshmail')->__('No cron heartbeat found. Check if cron is configured correctly.'));
        } else {
            $timespan = Mage::helper('snowfreshmail')->dateDiff($lastHeartbeat);
            if ($timespan <= 5 * 60) {
                $adminhtml->addSuccess(Mage::helper('snowfreshmail')->__('Last cron heartbeat: %s minute(s) ago', round($timespan / 60)));
            } elseif ($timespan > 5 * 60 && $timespan <= 60 * 60) {
                $adminhtml->addNotice(Mage::helper('snowfreshmail')->__('Last cron heartbeat is older than %s minutes.', round($timespan / 60)));
            } else {
                $adminhtml->addError(Mage::helper('snowfreshmail')->__('Last cron heartbeat is older than one hour. Please check your settings and your configuration!'));
            }
        }
    }
    
    /**
     * Add customer segments configuration if the module is enabled
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlInitSystemConfig($observer)
    {
        $config = $observer->getConfig();
        if (Mage::helper('core')->isModuleEnabled('Enterprise_CustomerSegments')) {
            $section = $config->getNode('sections/snowfreshmail/groups/lists/fields');
            $group = new Mage_Core_Model_Config_Element('
                <segments translate="label">
                    <label>Customer Segments</label>
                    <frontend_model>snowfreshmail/system_config_form_field_segments</frontend_model>
                    <backend_model>adminhtml/system_config_backend_serialized_array</backend_model>
                    <sort_order>30</sort_order>
                    <show_in_default>1</show_in_default>
                    <show_in_website>0</show_in_website>
                    <show_in_store>0</show_in_store>
                </segments>
            ');
            $section->appendChild($group);
        }
    }

    /**
     * Test connection on freshmail configuration save
     */
    public function adminSystemConfigChangedSectionFreshmail()
    {
        $cache = Mage::app()->getCacheInstance();
        $cache->remove(Snowdog_Freshmail_Helper_Api::LISTS_CACHE_ID);

        $flag = Mage::helper('snowfreshmail/api')->getStatusFlag();
        $flag->setFlagData(
            Mage::getSingleton('snowfreshmail/serviceManager')->testConnection()
        );
        $flag->save();

        $lists = array();
        foreach (Mage::app()->getStores() as $store) {
            $listHash = Mage::getSingleton('snowfreshmail/config')->getListHash($store->getId());
            if ($listHash && !isset($lists[$listHash])) {
                Mage::helper('snowfreshmail/api')->initFields($listHash);
                $lists[$listHash] = true;
            }
        }

        Mage::helper('snowfreshmail')->compilePopupCss();
    }

    /**
     * Remove customer from Freshmail upon delete
     *
     * @param Varien_Event_Observer $observer
     */
    public function newsletterSubscriberDeleteAfter($observer)
    {
        $subscriber = $observer->getSubscriber();
        $storeId = $subscriber->getStoreId();
        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');
        $data = array(
            'email' => $subscriber->getSubscriberEmail(),
            'list' => $configModel->getListHash($storeId)
        );
        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        $serviceManager->deleteSubscriber($data);
    }

    /**
     * On subscriber save
     *
     * @param Varien_Event_Observer $observer
     */
    public function newsletterSubscriberSaveAfter($observer)
    {
        if (Mage::registry('snowfreshmail_disable_event')) {
            return;
        }
        $subscriber = $observer->getSubscriber();

        $oldStatus = $subscriber->getOrigData('subscriber_status');
        $status = $subscriber->getSubscriberStatus();
        $storeId = $subscriber->getStoreId();

        if ($oldStatus === (int) $status) {
            // No subscriber status changes
            return;
        }

        /** @var Snowdog_Freshmail_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('snowfreshmail/api');

        $customerData = $subscriber->getData();
        if ($subscriber->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            $customerData = $customer->getData();
            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');
            if ($coreHelper->isModuleEnabled('Enterprise_CustomerSegment')) {
                $customerData += array(
                    'segment_ids' => Mage::helper('snowfreshmail')
                        ->getCustomerSegmentIds($customer),
                );
            }
        } else {
            $resourceModel = Mage::getSingleton('core/resource');
            $adapter = $resourceModel->getConnection('core_read');
            $select = $adapter->select()
                ->from($resourceModel->getTableName('snowfreshmail/custom_data'), 'subscriber_data')
                ->where('subscriber_id = ?', $subscriber->getId());
            $result = $adapter->fetchOne($select);
            if ($result) {
                $customerData = array_merge($customerData, unserialize($result));
            }
        }

        $customFields = $apiHelper->convertSubscriberData(
            $subscriber->getSubscriberEmail(),
            $customerData,
            $storeId
        );

        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');
        $data = array(
            'list' => $configModel->getListHash($storeId),
            'email' => $subscriber->getSubscriberEmail(),
            'state' => Mage::helper('snowfreshmail')->getFreshmailStatus($status),
            'custom_fields' => $customFields,
        );

        $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
        $serviceManager->editSubscriber($data);
    }

    /**
     * Edit/add subscriber on customer account save action
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveAfter($observer)
    {
        $customer = $observer->getCustomer();

        if ($customer->getIsSubscribed()) {
            $newEmail = $customer->getEmail();
            $oldEmail = $customer->getOrigData('email');
            if ($newEmail == $oldEmail) {
                return;
            }

            /** @var Snowdog_Freshmail_Helper_Api $apiHelper */
            $apiHelper = Mage::helper('snowfreshmail/api');
            $storeId = $customer->getStoreId();
            $customerData = $customer->getData();

            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');
            if ($coreHelper->isModuleEnabled('Enterprise_CustomerSegment')) {
                $customerData += array(
                    'segment_ids' => Mage::helper('snowfreshmail')
                        ->getCustomerSegmentIds($customer),
                );
            }

            $customFields = $apiHelper->convertSubscriberData(
                $newEmail,
                $customerData,
                $storeId
            );

            /** @var Snowdog_Freshmail_Model_Config $configModel */
            $configModel = Mage::getSingleton('snowfreshmail/config');
            $data = array(
                'list' => $configModel->getListHash($storeId),
                'email' => $newEmail,
                'custom_fields' => $customFields,
            );

            $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
            $serviceManager->editSubscriber($data, $oldEmail);
        }
    }
}
