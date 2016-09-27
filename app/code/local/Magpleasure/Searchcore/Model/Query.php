<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Query extends Magpleasure_Common_Model_Abstract
{
    /**
     *
     */
    const STATUS_NO = 0;

    /**
     *
     */
    const STATUS_YES = 1;

    /**
     *
     */
    const STATUS_LOCKED = 2;

    /**
     *
     */
    const LOCK_TIMEOUT = 300; # 5 min

    /**
     *
     */
    const PROCESSED_TIMEOUT = 3600; # 1 hour

    /**
     *
     */
    const STATUS_FIELD = 'is_processed';

    /**
     * @var array
     */
    protected $_defaultData = array(
        'is_active' => 1,
        'num_results' => 0,
        'popularity' => 0,
        'display_in_terms' => 1,
        self::STATUS_FIELD => self::STATUS_NO,
    );

    /**
     * Retrieves search query object
     *
     * @param      $q
     * @param null $storeId
     *
     * @return Magpleasure_Searchcore_Model_Query
     */
    public function getQueryByQ($q, $storeId = null)
    {
        /** @var Magpleasure_Searchcore_Model_Query $query */
        $query = Mage::getModel('searchcore/query');

        if (Mage::app()->isSingleStoreMode()) {
            $storeId = '0';
        } else {
            $storeId = ($storeId !== null) ? $storeId : Mage::app()->getStore()->getId();
        }

        $this->setStoreId($storeId);
        $query->setStoreId($storeId);
        $query->loadByQ($q);

        if ($query->getId()) {
            return $query;
        } else {
            $query->addData($this->_defaultData);
            $query->addData(array(
                'query_text' => $q,
                'store_id' => $storeId,
            ));

            $query->save();

            return $query;
        }
    }

    /**
     * Set Store Id
     *
     * @param $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        $this->getResource()->setStoreId($storeId);

        return $this;
    }

    /**
     * Resource
     *
     * @return Magpleasure_Searchcore_Model_Resource_Query
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * @param $q
     *
     * @return $this
     */
    public function loadByQ($q)
    {
        $this->loadByFewFields(array(
            'query_text' => $q,
            'store_id' => $this->getStoreId(),
        ));

        return $this;
    }

    /**
     * Get Store Id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return
            $this->hasData('store_id') ?
            $this->getData('store_id') :
            Mage::app()->getStore()->getId()
        ;
    }

    /**
     * @return int
     */
    public function getResultCount()
    {
        return 0;
    }

    /**
     * Get Result Ids if it's possible to get it
     * ------------------------------------------------
     * Result Ids are Ids in index what should be used
     * to link index rows to master collection
     *
     * @param array $typeIds
     *
     * @return array
     */
    public function getResultIds($typeIds = null)
    {
        if (!is_array($typeIds)) {
            $typeIds = array($typeIds);
        }

        $this->_askForResults();

        return $this->_getResultIds($typeIds);
    }

    /**
     * @return $this
     */
    protected function _askForResults()
    {
        # If non proceed
        if (!$this->isProceed()) {

            # Try to lock it
            if (!$this->isLocked()) {

                $this->lock();
                Varien_Profiler::start('mp::serachcore::process_results');
                $this->_processResults();
                Varien_Profiler::stop('mp::serachcore::process_results');
                $this->unlock();
            }
        }

        $this
            ->setIsProcessed(self::STATUS_YES)
            ->incPopularity()
        ;

        return $this;
    }

    /**
     * @return bool
     */
    public function isProceed()
    {
        return
            ($this->getData(self::STATUS_FIELD) == self::STATUS_YES) &&
            !$this->_isExpired(self::PROCESSED_TIMEOUT)
        ;
    }

    /**
     * @param $timeout
     *
     * @return bool
     */
    protected function _isExpired($timeout)
    {
        $timeFirst = strtotime($this->getUpdatedAt());
        $timeSecond = time();
        $differenceInSeconds = $timeSecond - $timeFirst;

        return ($differenceInSeconds) > $timeout;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return
            $this->getData(self::STATUS_FIELD) == self::STATUS_LOCKED &&
            !$this->_isExpired(self::LOCK_TIMEOUT)
        ;
    }

    /**
     * @return $this
     */
    public function lock()
    {
        $this->_updateStatus(self::STATUS_LOCKED);

        return $this;
    }

    /**
     * Update Processed Status
     *
     * @param $newStatus
     *
     * @return $this
     */
    protected function _updateStatus($newStatus)
    {
        $this
            ->setData(
                self::STATUS_FIELD,
                $newStatus
            )
            ->save();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _processResults()
    {
        $query = $this->getData('query_text');
        $this
            ->getResource()
            ->proceedResults(
                $query,
                $this->getStoreId(),
                $this
            )
        ;

        return $this;
    }

    /**
     * @return $this
     */
    public function unlock()
    {
        $this->_updateStatus(self::STATUS_YES);

        return $this;
    }

    /**
     * @param array $typeIds
     *
     * @return array
     */
    protected function _getResultIds(array $typeIds = null)
    {
        ///TODO In future

        return array();
    }

    /**
     * @return $this
     */
    public function incPopularity()
    {
        $this
            ->getResource()
            ->incPopularity($this)
        ;

        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        # Reset proceed flag
        $this->_updateStatus(self::STATUS_NO);

        return $this;
    }

    /**
     * Query Collection
     *
     * @return Magpleasure_Searchcore_Model_Resource_Query_Collection
     */
    public function getCollection()
    {
        /** @var Magpleasure_Searchcore_Model_Resource_Query_Collection $collection */
        $collection = parent::getCollection();
        $collection->setStoreId($this->getStoreId());

        return $collection;
    }

    /**
     * @param Magpleasure_Common_Model_Resource_Collection_Abstract $collection
     * @param                                                       $typeCode
     *
     * @return $this
     */
    public function applyFilterToCollection(Magpleasure_Common_Model_Resource_Collection_Abstract $collection, $typeCode)
    {
        $dbHelper = $this->_helper()->getCommon()->getDatabase();

        if ($select = $collection->getSelect()) {

            if ($type = $this->_helper()->getTypeByCode($typeCode)) {

                $pkField = $type->getConfig()->getPkField();
                if ($pkField) {

                    $this->_askForResults();

                    $resultTable = $dbHelper->getTableName('mp_search_result');
                    $indexTable = $dbHelper->getTableName('mp_search_index');
                    $queryId = $this->getId();
                    $storeId = $this->getStoreId();

                    $select
                        ->joinInner(array('res' => $resultTable), "res.query_id = '{$queryId}'", array())
                        ->joinInner(array('index' => $indexTable), "index.index_id = res.index_id AND index.entity_id = main_table.{$pkField} AND index.store_id = '{$storeId}'", array())
                        ->order("res.relevance DESC");

                }
            }
        }

        return $this;
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

    public function hasSuggestion()
    {
        $hasSuggestion = false;
        foreach ($this->getSuggestionWords() as $word){
            /** @var Magpleasure_Searchcore_Model_Suggestion_Word $word */
            if ($word->getIsMistyped()){
                $hasSuggestion = true;
                break;

            }
        }

        return $hasSuggestion;
    }

    public function getSuggestionWords()
    {
        $words = $this->getSuggestion()->getWords();

        if (count($words)){

            $origWords = array();

            foreach ($words as $word){
                /** @var Magpleasure_Searchcore_Model_Suggestion_Word $word */
                $origWords[] = $word->getOrigValue();
            }

            $i = 0;
            foreach ($words as $word){

                if ($word->getIsMistyped()){

                    $correctedWords = $origWords;
                    $correctedWords[$i] = $word->getSuggestValue();

                    $word->setQuery(implode(" ", $correctedWords));
                }
                $i++;
            }
        }

        return $words;
    }

    /**
     * @return Magpleasure_Searchcore_Model_Suggestion
     */
    public function getSuggestion()
    {
        /** @var Magpleasure_Searchcore_Model_Suggestion $model */
        $model = Mage::getSingleton('searchcore/suggestion');
        $query = $this->getData('query_text');
        $model->prepareWords($query);

        return $model;
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('searchcore/query');
    }

    /**
     * @param null $typeIds
     *
     * @return null
     */
    protected function _getResultCollection($typeIds = null)
    {
        ///TODO Prepare Collection

        return null;
    }
}