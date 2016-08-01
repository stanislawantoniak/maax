<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Resource_Query extends Magpleasure_Common_Model_Resource_Abstract
{
    const PATTERN_TABLE_NAME = 'mp_search_result';
    protected $_storeId;

    /**
     * Default Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }

    /**
     * Get Store
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set Store
     *
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function proceedResults($queryText, $storeId, Magpleasure_Searchcore_Model_Query $queryModel)
    {
        # Normalise Query Text
        $queryText = $this
            ->_helper()
            ->getTextTransformer()
            ->htmlToWords($queryText)
        ;


        # 1. Find ned results from index

        /** @var Magpleasure_Searchcore_Model_Index $indexModel */
        $indexModel = Mage::getModel('searchcore/index');

        /** @var Magpleasure_Searchcore_Model_Resource_Index_Collection $indexCollection */
        $indexCollection = $indexModel->getCollection();
        $indexCollection
            ->setStoreId($storeId)
            ->addSearchFilter($queryText);

        $scores = $indexCollection->getScores();

        Varien_Profiler::start('mp::searchcore::search_in_index');
        $indexIds = array_keys($scores);
        Varien_Profiler::stop('mp::searchcore::search_in_index');

        # 2. Lock result table
        Varien_Profiler::start('mp::searchcore::refresh_results_table');
        $write = $this->_commonHelper()->getDatabase()->getWriteConnection();
        $write->beginTransaction();

        # 3. Remove old results
        $resultTable = $this
            ->_commonHelper()
            ->getDatabase()
            ->getTableName(
                self::PATTERN_TABLE_NAME
            );

        $queryId = $queryModel->getId();
        $write->delete($resultTable, "`query_id` = '{$queryId}'");

        # 4. Fill new results

        $bindData = array();

        foreach ($indexIds as $indexId) {
            $bindData[] = array(
                'query_id' => $queryId,
                'index_id' => $indexId,
                'relevance' => $scores[$indexId],
            );
        }

        $queryModel
            ->setNumResults(count($bindData))
            ->setIsActive(1);

        if (count($bindData)) {
            $write->insertMultiple($resultTable, $bindData);
        }

        # 5. Unlock results table
        $write->commit();
        Varien_Profiler::stop('mp::searchcore::refresh_results_table');

        return $this;
    }

    public function incPopularity(Magpleasure_Searchcore_Model_Query $query)
    {
        if ($queryId = $query->getId()) {

            $queryTable = $this->getMainTable();
            $write = $this->_commonHelper()->getDatabase()->getWriteConnection();
            $write->beginTransaction();
            $write->update($queryTable, array('popularity' => new Zend_Db_Expr("`popularity` + 1")), "query_id = '{$queryId}'");
            $write->commit();
        }

        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('searchcore/query', 'query_id');
        $this->setUseUpdateDatetimeHelper(true);
    }
}