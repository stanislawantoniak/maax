<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Resource_Query_Collection
    extends Magpleasure_Common_Model_Resource_Collection_Abstract
{
    protected $_storeId;

    protected $_tableName;

    public function setMainTable($table)
    {
        $this->_tableName = $table;
        $this->_select->reset(Zend_Db_Select::FROM);
        $this->_initSelect();
        return $this;
    }

    /**
     * Get Store Id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set Store Id
     *
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function addIndexRelatedFilter($indexIds)
    {
        $resultTable = $this->_getResultTableName();
        $this
            ->getSelect()
            ->joinInner(array('result' => $resultTable), "result.query_id = main_table.query_id AND result.index_id = '{$indexIds}'", array())
            ;

        return $this;
    }

    protected function _getResultTableName()
    {
        return $this->_commonHelper()->getDatabase()->getTableName("mp_search_result");
    }

    /**
     * Fast reset for selected queries
     *
     * @return $this
     * @throws Varien_Db_Exception
     */
    public function resetQueries()
    {
        $resetField = Magpleasure_Searchcore_Model_Query::STATUS_FIELD;
        $resetValue = Magpleasure_Searchcore_Model_Query::STATUS_NO;

        $tableToUpdate = $this->getMainTable();

        $write = $this->_commonHelper()->getDatabase()->getWriteConnection();
        $write->beginTransaction();

        # Prepare Update
        $select = clone $this->getSelect();

        $queryTable = $this->_getQueryTableName();
        $query = sprintf('UPDATE %s', $write->quoteTableAs($queryTable, 'main_table'));


        # render JOIN conditions (FROM Part)
        $joinConds  = array();
        foreach ($select->getPart(Zend_Db_Select::FROM) as $correlationName => $joinProp) {

            if ($joinProp['joinType'] != Zend_Db_Select::FROM) {
                $joinType = strtoupper($joinProp['joinType']);

                $joinTable = '';
                if ($joinProp['schema'] !== null) {
                    $joinTable = sprintf('%s.', $write->quoteIdentifier($joinProp['schema']));
                }
                $joinTable .= $write->quoteTableAs($joinProp['tableName'], $correlationName);

                $join = sprintf(' %s %s', $joinType, $joinTable);

                if (!empty($joinProp['joinCondition'])) {
                    $join = sprintf('%s ON %s', $join, $joinProp['joinCondition']);
                }

                $joinConds[] = $join;
            }
        }

        if ($joinConds) {
            $query = sprintf("%s\n%s", $query, implode("\n", $joinConds));
        }

        # render UPDATE SET
        $columns = array();
        $columns[] = sprintf('%s = %s', $write->quoteIdentifier(array('main_table', $resetField)), $resetValue);
        $query = sprintf("%s\nSET %s", $query, implode(', ', $columns));

        # render WHERE
        $wherePart = $select->getPart(Zend_Db_Select::WHERE);
        if ($wherePart) {
            $query = sprintf("%s\nWHERE %s", $query, implode(' ', $wherePart));
        }

        $write->query($query);
        $write->commit();

        return $this;
    }

    public function getMainTable()
    {
        return $this->_tableName ? $this->_tableName : $this->getResource()->getMainTable();
    }

    protected function _getQueryTableName()
    {
        return $this->getMainTable();
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('searchcore/query');
    }
}