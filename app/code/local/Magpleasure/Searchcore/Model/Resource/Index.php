<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Resource_Index extends Mage_Core_Model_Mysql4_Abstract
{
    const INDEXER_PROCESS_ID = 'magpleasure_searchcore';
    const FULL_REINDEX_KEY = 'mp_search_if_full_reindex';

    protected $_storeId;

    public function reindexAll()
    {
        $usedStoreIds = $this->_getStores();
        $this->setIsFullReindex(true);

        # 1. Flush old index records
        Varien_Profiler::start('mp::searchcore::reindex_all::flush_index');
        /** @var Magpleasure_Searchcore_Model_Index $indexModel */
        $indexModel = Mage::getModel('searchcore/index');
        $indexCollection = $indexModel->getCollection();
        $indexCollection->flush($usedStoreIds);
        Varien_Profiler::stop('mp::searchcore::reindex_all::flush_index');

        # 3. Get list of Types
        $types = Mage::getModel('searchcore/type')->getCollection();

        # 4. For each type get indexing params
        foreach ($types as $type) {

            /** @var Magpleasure_Searchcore_Model_Type $type */
            $config = $type->getConfig();

            /** @var Magpleasure_Common_Model_Resource_Abstract $types */
            if ($resource = $config->getTargetResource()) {
                if (method_exists($resource, 'reindexAll')) {
                    $resource->reindexAll();
                } else {

                    Varien_Profiler::start('mp::searchcore::reindex_all::reindex_collection::' . $type->getTypeCode());

                    $modelName = $this->_wrapModel($config->getModel());

                    /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $modelCollection */
                    $modelCollection = Mage::getModel($modelName)->getCollection();
                    /** @var Mage_Core_Model_Resource_Abstract $resourceModel */
                    $resourceModel = Mage::getResourceModel($modelName);

                    foreach ($modelCollection as $modelItem) {
                        if (method_exists($resourceModel, 'reindexItem')) {
                            $resourceModel->reindexItem($modelItem, $config);
                        } else {
                            $this->reindexItem($modelItem, $config, true);
                        }
                    }

                    Varien_Profiler::stop('mp::searchcore::reindex_all::reindex_collection' . $type->getTypeCode());
                }
            }
        }

        return $this;
    }

    protected function _getStores()
    {
        return $this->_helper()->getCommon()->getStore()->getAllStores();
    }

    /**
     * Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setIsFullReindex($value)
    {
        Mage::register(self::FULL_REINDEX_KEY, true, true);

        return $this;
    }

    protected function _wrapModel($modelName)
    {
        ///TODO Change in future
        return $modelName;
    }

    /**
     * Reindex Abstract Item
     *
     * @param Mage_Core_Model_Abstract                 $object
     * @param Magpleasure_Searchcore_Model_Type_Config $config
     * @param bool                                     $isFullIndex
     *
     * @return $this
     */
    public function reindexItem(Mage_Core_Model_Abstract $object, Magpleasure_Searchcore_Model_Type_Config $config, $isFullIndex = false)
    {
        # 1. Load object to preprocess data we need to index
        if ($config->getLoadBeforeIndex() && $isFullIndex) {
            $object->load($object->getId());
        }

        $stores = array();

        if (($storeKeys = $config->getStores()) && !Mage::app()->isSingleStoreMode()) {

            # 2.1 Get store keys to reindex
            $stores = $object->getData($storeKeys);

            # 2.2 Get store keys to be deleted
            if ($origStores = $object->getOrigData($storeKeys)) {

                $storesToDelete = $this->_helper()->getCommon()->getArrays()->findDeletedValues($origStores, $stores);

                # 2.3 Delete index rows from store indexes
                $this->_deleteFromStores($config, $object->getId(), $storesToDelete);
            }
        }

        $stores[] = '0';
        $stores = array_unique($stores);

        # 3. Reindex
        foreach ($stores as $storeId) {

            try {
                if (in_array($storeId, $this->_getStores())) {

                    if (!Mage::app()->isSingleStoreMode()) {
                        $object->setStoreId($storeId);
                    }

                    $storeFullText = $config->getProcessor()->process($object);

                    if ($storeFullText) {
                        /** @var Magpleasure_Searchcore_Model_Index $indexModel */
                        $indexModel = Mage::getModel('searchcore/index');
                        $indexModel->setStoreId($storeId);

                        if (!$isFullIndex) {
                            $indexModel->loadByFewFields(array(
                                'type_id'   => $config->getTypeId(),
                                'entity_id' => $object->getId(),
                                'store_id'  => $storeId
                            ));
                        }

                        $indexModel
                            ->setTypeId($config->getTypeId())
                            ->setEntityId($object->getId())
                            ->setStoreId($storeId)
                            ->setDataIndex($storeFullText)
                            ->setUpdatedAt($config->getUpdatedAtField($object))
                            ->save();
                    }

                }
            } catch (Exception $e) {
                $this->_helper()->getCommon()->getException()->logException($e);
            }
        }

        return $this;
    }

    /**
     * Delete Index records form
     *
     * @param Magpleasure_Searchcore_Model_Type_Config $config
     * @param                                          $entityId
     * @param array                                    $storeIds
     *
     * @return $this
     */
    protected function _deleteFromStores(Magpleasure_Searchcore_Model_Type_Config $config, $entityId, array $storeIds)
    {
        $indexTableName = $this->getMainTable();

        $parts = array();

        $storeIds = array_unique($storeIds);
        $storeFiler = "'" . implode("','", $storeIds) . "'";

        $parts[] = sprintf("type_id = '%d'", $config->getTypeId());
        $parts[] = sprintf("entity_id = '%d'", $entityId);
        $parts[] = sprintf("store_id IN (%s)", $storeFiler);

        $whereFilter = "((" . implode(") AND (", $parts) . "))";

        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->delete($indexTableName, $whereFilter);
        $write->commit();

        return $this;
    }

    public function flushForStore($storeId)
    {
        $indexCollection = Mage::getModel('searchcore/index')->getCollection();
        $indexCollection->flush($storeId);

        return $this;
    }

    public function deleteIndex(Mage_Core_Model_Abstract $object, Magpleasure_Searchcore_Model_Type_Config $config)
    {
        $stores = array();

        if (($storeKeys = $config->getStores()) && !Mage::app()->isSingleStoreMode()) {
            $stores = $object->getData($storeKeys);
        }
        $stores[] = 0;
        $this->_deleteFromStores($config, $object->getId(), $stores);

        return $this;
    }

    public function setReindexRequiredFlag()
    {
        /** @var Mage_Index_Model_Process $process */
        $process = Mage::getModel('index/process')->load(self::INDEXER_PROCESS_ID, 'indexer_code');
        if ($process->getId()) {
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }

    public function isManualUpdateModeEnabled()
    {
        /** @var Mage_Index_Model_Process $process */
        $process = Mage::getModel('index/process')->load(self::INDEXER_PROCESS_ID, 'indexer_code');
        if ($process->getId()) {
            return $process->getMode() == Mage_Index_Model_Process::MODE_MANUAL;
        }

        return false;
    }

    /**
     * Load Absctract Collection by few key fields
     *
     * @param Mage_Core_Model_Abstract $object
     * @param array                    $data
     *
     * @return Magpleasure_Common_Model_Resource_Abstract
     */
    public function loadByFewFields(Mage_Core_Model_Abstract $object, array $data)
    {
        /** @var $itemModel Magpleasure_Searchcore_Model_Index */
        $itemModel = Mage::getModel('searchcore/index');
        $itemModel->setStoreId($this->getStoreId());
        $collection = $itemModel->getCollection();

        if ($collection) {
            foreach ($data as $field => $value) {
                $collection->addFieldToFilter($field, $value);
            }

            foreach ($collection as $item) {
                /** @var $item Mage_Core_Model_Abstract */
                if ($itemId = $item->getId()) {
                    $itemModel = Mage::getModel('searchcore/index');
                    $itemModel->setStoreId($this->getStoreId());
                    $itemModel->load($item->getId());
                    $object->setData($itemModel->getData());

                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * @param $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    protected function _construct()
    {
        $this->_init('searchcore/index', 'index_id');
    }

    /**
     * Perform actions after object delete
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return $this|Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        parent::_afterDelete($object);

        # Free result records due to
        # it can't be done using Foreign Keys
        if ($indexId = $object->getId()) {
            $this->_freeResultsFor($indexId);
        }

        return $this;
    }

    protected function _freeResultsFor($indexId)
    {
        # Skip this step if this is full reindex
        if ($this->getIsFullReindex()) {
            return $this;
        }

        # 1. Reset search relations

        /** @var Magpleasure_Searchcore_Model_Query $query */
        $query = Mage::getModel('searchcore/query');
        $query->setStoreId($this->getStoreId());

        $queries = $query->getCollection();
        $queries
            ->addIndexRelatedFilter($indexId)
            ->resetQueries();

        # 2. Remove old results

        /** @var Magpleasure_Searchcore_Model_Result $resultsModel */
        $resultsModel = Mage::getSingleton('searchcore/result');
        $resultsModel->flushSelectedByIndexId($indexId);

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFullReindex()
    {
        return Mage::registry(self::FULL_REINDEX_KEY);
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        if ($indexId = $object->getId()) {

            $this->_freeResultsFor($indexId);

            $indexData = $object->getDataIndex();
            if ($indexData) {

                $this->_updateRelatedData($indexId, $indexData);
            }
        }

        return $this;
    }

    protected function _updateRelatedData($indexId, $indexText)
    {
        $ignoreHelper = $this->_helper()->getIgnoreHelper();
        $wordHelper = Mage::getSingleton('searchcore/word');
        $stringHelper = $this->_helper()->getCommon()->getStrings();

        $words = explode(" ", $indexText);

        # index_id, word_id, location
        $posRelations = array();

        Varien_Profiler::start('mp::searchcore::index::prepare_relations');
        for ($i = 0; $i < count($words); $i++) {
            if ($ignoreHelper->isInIgnore($words[$i])) {
                continue;
            }

            $word = trim($words[$i]);
            $word = $stringHelper->strtolower($word);

            if ($word) {

                $wordId = $wordHelper->getWordId($word);
                $posRelations[] = array($indexId, $wordId, $i);
            }
        }
        Varien_Profiler::stop('mp::searchcore::index::prepare_relations');

        Varien_Profiler::start('mp::searchcore::index::insert_reations');
        $linkTable = $this->_helper()->getCommon()->getDatabase()->getTableName("mp_search_index_word");
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->insertArray($linkTable, array('index_id', 'word_id', 'location'), $posRelations);
        $write->commit();
        Varien_Profiler::stop('mp::searchcore::index::insert_reations');

        return $this;
    }
}