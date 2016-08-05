<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Comment_Collection extends Magpleasure_Blog_Model_Mysql4_Abstract_Collection
{
    protected $_storeIds;

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/comment');
    }

    public function addStoreFilter($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    protected function _applyStoreFilter($storeIds = null)
    {
        if ($storeIds){

            if (!is_array($storeIds)){
                $storeIds = array($storeIds);
            }

            $storeIds = "'".implode("','", $storeIds)."'";
            $this
                ->getSelect()
                ->where(new Zend_Db_Expr("main_table.store_id IN ({$storeIds})"))
            ;
        }

        return $this;
    }


    public function addPostFilter($postId)
    {
        $this->addFieldToFilter('post_id', $postId);
        return $this;
    }

    public function addActiveFilter($ownerSessionId = null)
    {
        if ($ownerSessionId){
            $activeStatus = Magpleasure_Blog_Model_Comment::STATUS_APPROVED;
            $pendingStatus = Magpleasure_Blog_Model_Comment::STATUS_PENDING;
            $this->getSelect()
                ->where(new Zend_Db_Expr("(main_table.status = '{$activeStatus}') OR ((main_table.status = '{$pendingStatus}') AND (main_table.session_id = '$ownerSessionId'))"))
                ;

        } else {
            $this->addFieldToFilter('status', Magpleasure_Blog_Model_Comment::STATUS_APPROVED);
        }
        return $this;
    }

    public function addReplyTo()
    {
        $this->getSelect()
            ->joinLeft(array('replied'=>$this->getMainTable()), "replied.comment_id = main_table.reply_to", array('reply_to_text'=>'replied.message'))
            ;
        return $this;
    }

    public function addReplyToTextFilter($filter)
    {
        $this->getSelect()
            ->where("replied.message LIKE ('%{$filter}%')")
            ;
        return $this;
    }


    public function addMessageTextFilter($filter)
    {
        $this->getSelect()
            ->where("main_table.message LIKE ('%{$filter}%')")
            ;
        return $this;
    }

    public function addPostTextFilter($filter)
    {
        $postTable = Mage::getModel('mpblog/post')->getResource()->getMainTable();
        $this->getSelect()->join(array('post'=>$postTable), "main_table.post_id = post.post_id AND post.title LIKE ('%{$filter}%')", array());
        return $this;
    }

    public function addPostStoreFilter($storeIds)
    {
        if (!is_array($storeIds)){
            $storeIds = array($storeIds);
        }

        $table = Mage::getModel('mpblog/post')->getResource()->getMainTable()."_store";
        $storeIds = "'".implode("','", $storeIds)."'";
        $this->getSelect()->joinInner(array('store'=>$table), "store.post_id = main_table.post_id AND store.store_id IN ({$storeIds})", array());
        return $this;
    }

    public function setDateOrder($dir = 'DESC')
    {
        $this->getSelect()
            ->order("main_table.created_at {$dir}");
        return $this;
    }

    public function setNotReplies()
    {
        $this->getSelect()
            ->where("main_table.reply_to IS NULL")
            ;

        return $this;
    }

    public function setReplyToFilter($commentId)
    {
        $this->getSelect()
            ->where("main_table.reply_to = ?", $commentId)
            ;
        return $this;
    }

    public function addStatusFilter($statusId)
    {
        $this->getSelect()
            ->where(new Zend_Db_Expr("main_table.status = '{$statusId}'"))
            ;
        return $this;
    }

    public function addCategoryFilter($categoryId)
    {
        $postTable = $this->getTable('mpblog/post')."_category";

        $this->getSelect()
            ->join(array('post_category'=>$postTable), "post_category.post_id = main_table.post_id", array())
            ->where("post_category.category_id = ?", $categoryId)
        ;

        return $this;
    }

    protected function _beforeLoad()
    {
        if ($this->_storeIds){
            $this->_applyStoreFilter($this->_storeIds);
        }

        return parent::_beforeLoad();
    }

    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == "reply_to_text"){

            $this
                ->getSelect()
                ->order("replied.message {$direction}")
            ;

        } else {

            return parent::setOrder($field, $direction);
        }
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if ($this->_storeIds){

            if (!is_array($this->_storeIds)){
                $stores = array($this->_storeIds);
            } else {
                $stores = $this->_storeIds;
            }

            $storeFilter = "'".implode("','", $stores)."'";
            $countSelect
                ->where("main_table.store_id IN ({$storeFilter})")
            ;
        }

        return $countSelect;
    }
}