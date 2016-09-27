<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Tag_Collection extends Magpleasure_Blog_Model_Mysql4_Abstract_Collection
{
    const MIN_SIZE = 1;
    const MAX_SIZE = 10;

    protected $_addWheightData = false;
    protected $_postDataJoined = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/tag');
    }

    public function addPostFilter($postIds)
    {
        if (!is_array($postIds)){
            $postIds = array($postIds);
        }

        $this->_joinPostData();

        $this->getSelect()
            ->where("post.post_id IN (?)", $postIds)
            ;

        return $this;
    }

    public function setNameOrder()
    {
        $this->getSelect()->order("main_table.name ASC");
        return $this;
    }

    protected function _joinPostData()
    {
        if ($this->_postDataJoined){
            return $this;
        }

        $this->_postDataJoined = true;

        $postTagTable = Mage::getModel('mpblog/post')->getResource()->getMainTable()."_tag";
        $this->getSelect()
            ->join(array('post'=>$postTagTable), "post.tag_id = main_table.tag_id", array())
        ;

        return $this;
    }

    public function addWieghtData($store = null)
    {
        $this->_addWheightData = true;
        $this->_joinPostData();
        $this->getSelect()
            ->columns(array('post_count' => new Zend_Db_Expr("COUNT(post.post_id)")))
            ->group("main_table.tag_id")
            ;

        if ($store){

            if (!is_array($store)){
                $store = array($store);
            }

            $store = "'".implode("','", $store)."'";
            $postStoreTable = Mage::getModel('mpblog/post')->getResource()->getMainTable()."_store";
            $this->getSelect()
                ->join(array('store'=>$postStoreTable), "post.post_id = store.post_id", array())
                ->where(new Zend_Db_Expr("store.store_id IN ({$store})"))
                ;
        }
        return $this;
    }



    private function addSortByWeight()
    {


    }

    public function setMinimalPostCountFilter($count)
    {
        if ($this->_addWheightData){
            $this->getSelect()
                ->having("COUNT(post.post_id) >= ?", $count)
            ;
        }
        return $this;
    }

    protected  function _afterLoad()
    {
        parent::_afterLoad();

        if ($this->_addWheightData){
            $tags = array();
            $sizes = array();

            foreach ($this as $tag){
                $tags[$tag->getId()] = $tag->getPostCount();
            }

            if (count($tags)){

                $minimum_count = min(array_values($tags));
                $maximum_count = max(array_values($tags));

                $spread = $maximum_count - $minimum_count;
                if($spread == 0) {
                    $spread = 1;
                }

                foreach ($tags as $tagId => $tagCount){
                    $sizes[$tagId] = self::MIN_SIZE + ($tagCount - $minimum_count) * (self::MAX_SIZE - self::MIN_SIZE) / $spread;
                }

                foreach ($this as $tag){
                    if (isset($sizes[$tag->getId()])){
                        $tag->setTagType($sizes[$tag->getId()]);
                    }
                }
            }
        }
        return $this;
    }

    public function setPostStatusFilter($status)
    {
        if (!is_array($status)){
            $status = array($status);
        }

        $postTable = Mage::getModel('mpblog/post')->getResource()->getMainTable();
        $this->getSelect()
                ->join(array('postEntity' => $postTable), "post.post_id = postEntity.post_id", array())
                ->where("postEntity.status IN (?)", $status);

        return $this;
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  Varien_Data_Collection_Db
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == 'post_count'){
            $this->getSelect()->order("COUNT(post.post_id) {$direction}");
        } else {
            parent::setOrder($field, $direction);
        }

        return $this;
    }

    public function addTagNamesFilter(array $tagNames)
    {
        if (count($tagNames)){
            $filter = array();
            foreach ($tagNames as $tag){
                $filter[] = sprintf("`main_table`.`url_key` = '%s'", $tag);
            }
            $this->getSelect()->where(implode(" OR ", $filter));
        }

        return $this;
    }

    /**
     * Retrieves COUNT sql for getSize() method
     *
     * @return Varien_Db_Select|Zend_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $postIdsSelect = clone $this->getSelect();
        $postIdsSelect->reset(Zend_Db_Select::ORDER);
        $postIdsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $postIdsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $postIdsSelect->reset(Zend_Db_Select::COLUMNS);
        $postIdsSelect->columns('main_table.tag_id');

        $countSelect = new Zend_Db_Select($this->getResource()->getReadConnection());
        $tableName = $this->getMainTable();
        $countSelect->from(array('mt'=>$tableName), array(new Zend_Db_Expr("COUNT(*)")));
        $countSelect->where(new Zend_Db_Expr(sprintf("`mt`.`tag_id` in (%s)", $postIdsSelect->__toString())));

        return $countSelect;
    }
}