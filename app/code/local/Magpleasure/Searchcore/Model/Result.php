<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Result extends Varien_Object
{

    /**
     * Flushing rows by index_id
     *
     * @param int|array $indexId
     */
    public function flushSelectedByIndexId($indexId)
    {
        if (is_array($indexId)) {
            $where = 'index_id IN (' . implode(', ', $indexId) . ')';
        } else {
            $where = "index_id = $indexId";
        }

        $linkTable = $this->_databaseHelper()->getTableName("mp_search_result");
        $write = $this->_databaseHelper()->getWriteConnection();

        $write->beginTransaction();
        $write->delete($linkTable, $where);
        $write->commit();
    }

    /**
     * Database helper
     *
     * @return Magpleasure_Common_Helper_Database
     */
    protected function _databaseHelper()
    {
        return Mage::helper('searchcore')->getCommon()->getDatabase();
    }
}