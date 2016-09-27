<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Post_Collection extends Magpleasure_Blog_Model_Mysql4_Abstract_Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/post');
    }


    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->_loadStores){
            foreach ($this as $item){
                $item->getResource()->loadAdditionalData($item);
            }
        }
        return $this;
    }

    /**
     * Add tag filter
     *
     * @param int $tagId Tag Id
     * @return Magpleasure_Blog_Model_Mysql4_Post_Collection
     */
    public function addTagFilter($tagId)
    {
        $tagTable = $this->getMainTable()."_tag";
        $this->getSelect()
            ->join(array('tags'=>$tagTable), "tags.post_id = main_table.post_id", array())
            ->where("tags.tag_id = ?", $tagId)
            ;
        return $this;
    }

    public function addTagsFilter($tagIDs)
    {
        $tagTable = $this->getMainTable()."_tag";
        $this->getSelect()
            ->join(array('tags'=>$tagTable), "tags.post_id = main_table.post_id", array())
            ->where("tags.tag_id IN (?)", $tagIDs)
            ;

        return $this;
    }

    /**
     * Filter
     *
     * @param int|array $categoryIds
     * @return $this
     */
    public function addCategoryFilter($categoryIds)
    {
        if (!is_array($categoryIds)){
            # Wrap ids to be array if it not ready before
            $categoryIds = array($categoryIds);
        }

        $categoryTable = $this->getMainTable()."_category";
        $this->getSelect()
            ->join(array('categories'=>$categoryTable), "categories.post_id = main_table.post_id", array())
            ->where("categories.category_id IN (?)", $categoryIds)
        ;
        return $this;
    }

    /**
     *
     * @return Magpleasure_Blog_Model_Mysql4_Post_Collection
     */
    public function setDateOrder()
    {
        $this->getSelect()->order("IFNULL(main_table.published_at, main_table.created_at) DESC");
        return $this;
    }

    public function addToFilter($date)
    {
        $this->getSelect()->where(new Zend_Db_Expr("IFNULL(`main_table`.`published_at`, `main_table`.`created_at`) <= '{$date}'"));
        return $this;
    }

    public function addFromFilter($date)
    {
        $this->getSelect()->where(new Zend_Db_Expr("IFNULL(`main_table`.`published_at`, `main_table`.`created_at`) >= '{$date}'"));
        return $this;
    }

    public function setUrlKeyIsNotNull()
    {
        $this->getSelect()->where("main_table.url_key != ''");
    }

    public function getCombinedKeywords($limit = 10)
    {
        $tags = Mage::getModel('mpblog/tag')->getCollection();
        $tags
            ->addPostFilter($this->getAllIds())
            ->addWieghtData(Mage::app()->getStore()->getId())
            ->setOrder("post_count", Varien_Db_Select::SQL_DESC)
            ->setPageSize($limit)
        ;

        $__ = $this->_commonHelper()->getUnderscore();
        $tags = $tags->toArray();
        return $__->pluck($tags["items"], "name");
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $postIdsSelect = clone $this->getSelect();
        $postIdsSelect->reset(Zend_Db_Select::ORDER);
        $postIdsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $postIdsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $postIdsSelect->reset(Zend_Db_Select::COLUMNS);
        $postIdsSelect->columns('main_table.post_id');

        $countSelect = new Zend_Db_Select($this->getResource()->getReadConnection());
        $tableName = $this->getMainTable();
        $countSelect->from(array('mt'=>$tableName), array(new Zend_Db_Expr("COUNT(*)")));
        $countSelect->where(new Zend_Db_Expr(sprintf("`mt`.`post_id` in (%s)", $postIdsSelect->__toString())));

        return $countSelect;
    }

    public function getSelectedIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->limit($this->_pageSize, ($this->_curPage - 1) * $this->_pageSize);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');

        return $this->getConnection()->fetchCol($idsSelect);
    }


}