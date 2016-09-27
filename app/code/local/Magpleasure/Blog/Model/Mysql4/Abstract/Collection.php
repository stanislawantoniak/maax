<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Abstract_Collection extends Magpleasure_Common_Model_Resource_Collection_Abstract
{
    protected $_mainTable;

    protected $_addStoreData = false;

    protected $_loadStores = false;

    protected $_storeIds;

    /**
     * Retrieve main table
     *
     * @return string
     */
    public function getMainTable()
    {
        if ($this->_mainTable === null) {
            $this->setMainTable($this->getResource()->getMainTable());
        }

        return $this->_mainTable;
    }

    /**
     * Set main collection table
     *
     * @param string $table
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function setMainTable($table)
    {
        if (strpos($table, '/') !== false) {
            $table = $this->getTable($table);
        }

        if ($this->_mainTable !== null && $table !== $this->_mainTable && $this->getSelect() !== null) {
            $from = $this->getSelect()->getPart(Zend_Db_Select::FROM);
            if (isset($from['main_table'])) {
                $from['main_table']['tableName'] = $table;
            }
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $from);
        }

        $this->_mainTable = $table;
        return $this;
    }

    protected function _beforeLoad()
    {
        $this->_applyStoreFilter();
        return parent::_beforeLoad();
    }

    public function addStoreData()
    {
        $this->_loadStores = true;
        return $this;
    }

    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == "stores"){

            $this
                ->_addStoreData()
                ->getSelect()
                ->order("store.store_id {$direction}")
            ;

        } else {
            return parent::setOrder($field, $direction);
        }

        return $this;
    }

    protected function _addStoreData()
    {
        if ($this->_addStoreData){
            return $this;
        }

        $this->_addStoreData = true;
        $table = $this->getMainTable()."_store";
        $idFieldName = $this->getResource()->getIdFieldName();


        $this
            ->getSelect()
            ->joinInner(array('store'=>$table), "store.{$idFieldName} = main_table.{$idFieldName}", array())
            ->group("main_table.{$idFieldName}")
        ;

        return $this;
    }

    protected function _applyStoreFilter($storeIds = null)
    {
        if ($this->_storeIds){

            $this->_addStoreData();

            $store = $this->_storeIds;

            if (!is_array($store)){
                $store = array($store);
            }

            $storesFilter = "'".implode("','", $store)."'";
            $this->getSelect()->where("store.store_id IN ({$storesFilter})");
        }

        return $this;
    }

    public function addStoreFilter($store)
    {
        $this->_storeIds = $store;
        return $this;
    }
}