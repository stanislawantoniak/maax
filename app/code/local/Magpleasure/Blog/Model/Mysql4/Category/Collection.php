<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Category_Collection extends Magpleasure_Blog_Model_Mysql4_Abstract_Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/category');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->_loadStores){
            foreach ($this as $item){
                $item->getResource()->loadStores($item);
            }
        }
        return $this;
    }

    public function addPostFilter($postId)
    {
        $postTable = $this->getTable('mpblog/post')."_category";

        $this->getSelect()
            ->join(array('post'=>$postTable), "post.category_id = main_table.category_id", array())
            ->where("post.post_id = ?", $postId)
            ;

        return $this;
    }

    public function setSortOrder($direction)
    {
        $this->getSelect()->order("main_table.sort_order {$direction}");
        return $this;
    }

    public function getSortOrders()
    {
        return $this->_collectValuesByField('sort_order');
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $categoryIdsSelect = clone $this->getSelect();
        $categoryIdsSelect->reset(Zend_Db_Select::ORDER);
        $categoryIdsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $categoryIdsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $categoryIdsSelect->reset(Zend_Db_Select::COLUMNS);
        $categoryIdsSelect->columns('main_table.category_id');

        $countSelect = new Zend_Db_Select($this->getResource()->getReadConnection());
        $tableName = $this->getMainTable();
        $countSelect->from(array('mt'=>$tableName), array(new Zend_Db_Expr("COUNT(*)")));
        $countSelect->where(new Zend_Db_Expr(sprintf("`mt`.`category_id` in (%s)", $categoryIdsSelect->__toString())));

        return $countSelect;
    }

}