<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Resource_Index_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var
     */
    protected $_storeId;
    /**
     * @var array
     */
    protected $_scores = array();

    /**
     * Relevance scores
     *
     * @return array
     */
    public function getScores()
    {
        return $this->_scores;
    }

    /**
     * Relevance scores
     *
     * @param $scores
     *
     * @return $this
     */
    public function setScores($scores)
    {
        $this->_scores = $scores;

        return $this;
    }

    /**
     * @param $storeIds
     *
     * @return $this
     */
    public function flush($storeIds)
    {
        $this->_flush($storeIds);

        return $this;
    }

    /**
     * @param array $storeIds
     *
     * @return $this
     */
    protected function _flush(array $storeIds)
    {
        try {
            $dbHelper = $this->_commonHelper()->getDatabase();

            if (!is_array($storeIds)) {
                $storeIds = array($storeIds);
            }

            # 1. Reset ready
            /** @var Magpleasure_Searchcore_Model_Query $query */
            $queries = Mage::getModel('searchcore/query')->getCollection();
            $queries
                ->addFieldToFilter('store_id', array('in' => $storeIds))
                ->resetQueries();

            $write = $dbHelper->getWriteConnection();

            # 2. Flush Index rows
            $indexTableName = $this->getMainTable();
            $storeFilter = "'" . implode("','", $storeIds) . "'";
            $storesFilter = "store_id IN ({$storeFilter})";

            $write->beginTransaction();
            $write->delete($indexTableName, $storesFilter);

            # Commit
            $write->commit();
        } catch (Exception $e) {
            $this->_commonHelper()->getException()->logException($e);
        }

        return $this;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    public function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Search method
     *
     * @param $query
     *
     * @return $this
     */
    public function addSearchFilter($query)
    {
        # 1. Process Query and get Suggestion IDs
        /** @var Magpleasure_Searchcore_Model_Suggestion $suggestionModel */
        $suggestionModel = Mage::getSingleton('searchcore/suggestion');

        $wordIds = array();
        $words = $suggestionModel->getWords($query);

        /** @var Magpleasure_Searchcore_Model_Suggestion_Word $word */
        foreach ($words as $word) {

            $typos = $word->getTypos(true);

            /** @var Magpleasure_Searchcore_Model_Resource_Word_Collection $collection */
            $collection = Mage::getModel('searchcore/word')->getCollection();
            $collection
                ->addFieldToFilter('word', array('in' => $typos))
            ;

            $result = $collection->getAllIds();

            if (!empty($result)) {
                $wordIds[] = $result;
            }
        }

        # 2. Create query and fetch raw results
        $rows = array();
        if (count($wordIds) > 0) {
            $rows = $this->_getRawFetch($wordIds, $this->getStoreId());
        }

        # 3. Calculate score for results
        $rangeHelper = $this->_helper()->getRangeHelper();
        $scores = $rangeHelper->getScoredList($rows, $wordIds);
        $this->setScores($scores);

        return $this;
    }

    /**
     * @param array $wordIdsArray
     * @param       $storeId
     *
     * @return array
     */
    protected function _getRawFetch(array $wordIdsArray, $storeId)
    {
        if (empty($wordIdsArray)) return array();

        $wordIdsArray = array_map('array_filter', $wordIdsArray);

        $dbHelper = $this->_commonHelper()->getDatabase();
        $storeId = $storeId ? $storeId : $this->getStoreId();

        $linkTableName = $dbHelper->getTableName('mp_search_index_word');
        $indexTableName = $dbHelper->getTableName('mp_search_index');

        # Define Columns
        $columnsList = array();
        $columnsList[] = 'w0.index_id';
        $columnsList[] = 'index0.updated_at';

        # Define FROM part
        $fromList = array();

        # Define where parts
        $whereList = array();

        foreach ($wordIdsArray as $i => $wordIds) {
            $columnsList[] = sprintf("w%d.location AS `%d`", $i, $i + 1);

            if ($i === 0){

                $fromList[] = sprintf(
                    "`%s` AS `w%d` INNER JOIN `%s` as `index0` ON (`w0`.`index_id` = `index0`.`index_id` AND `index0`.`store_id` = '%s')",
                    $linkTableName,
                    $i,
                    $indexTableName,
                    $storeId
                );

            } else {
                $fromList[] = sprintf(
                    "`%s` AS `w%d`",
                    $linkTableName,
                    $i
                );
            }

            if ($i < count($wordIdsArray) - 1) {
                $whereList[] = sprintf("w%d.index_id = w%d.index_id", $i, $i + 1);
            }

            if (count($wordIds) > 1) {
                $whereList[] = sprintf("w%d.word_id IN ('%s')", $i, implode("', '", $wordIds));
            } else {
                $whereList[] = sprintf("w%d.word_id = '%d'", $i, $wordIds[0]);
            }
        }

        # Prepare Select
        $columnsStr = implode(",", $columnsList);
        $fromStr = implode(",", $fromList);
        $whereStr =
            count($whereList) > 1 ?
            sprintf("WHERE (%s)", implode(') AND (', $whereList)) :
            "WHERE " . $whereList[0]
        ;
        $groupStr = "GROUP BY `w0`.`index_id`";

        # Compile it
        $sql = sprintf("SELECT %s FROM %s %s %s", $columnsStr, $fromStr, $whereStr, $groupStr);

        # Get compressed search results
        $result = $dbHelper->fetchAll($sql);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return !is_null($this->_storeId) ? $this->_storeId : Mage::app()->getStore()->getId();
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
     *
     */
    protected function _construct()
    {
        $this->_init('searchcore/index');
    }
}