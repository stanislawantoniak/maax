<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Comment_Subscription_Collection extends Magpleasure_Blog_Model_Mysql4_Abstract_Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/comment_subscription');
    }

    public function addPostTextFilter($filter)
    {
        $postTable = Mage::getModel('mpblog/post')->getResource()->getMainTable();
        $this->getSelect()->join(array('post'=>$postTable), "main_table.post_id = post.post_id AND post.title LIKE ('%{$filter}%')", array());
        return $this;
    }

}