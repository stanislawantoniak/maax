<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Comment_Notification_Collection extends Magpleasure_Blog_Model_Mysql4_Abstract_Collection
{
    protected $_isSubscriptionAdded = false;
    protected $_isPostAdded = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/comment_notification');
    }

    public function addSubscriptionData()
    {
        if ($this->_isSubscriptionAdded){
            return $this;
        }

        $this->_isSubscriptionAdded = true;

        $subscriptionTable = $this->_commonHelper()->getDatabase()->getTableName('mpblog/comment_subscription');
        $this->getSelect()->joinInner(
            array('subscription' => $subscriptionTable),
            "subscription.subscription_id = main_table.subscription_id",
            array(
                'customer_name' => 'customer_name',
                'email' => 'email',
                'customer_id' => 'customer_id',
            )
        );

        return $this;
    }

    public function addPostData()
    {
        if ($this->_isPostAdded){
            return $this;
        }

        $this->_isPostAdded = true;

        $postTable = $this->_commonHelper()->getDatabase()->getTableName('mpblog/post');
        $this->getSelect()->joinInner(
            array('post' => $postTable),
            "post.post_id = main_table.post_id",
            array()
        );

        return $this;
    }

    public function addCustomerNameFilter($filter)
    {
        $this->addSubscriptionData();

        $this
            ->getSelect()
            ->where("subscription.customer_name LIKE ?", "%".$filter."%");

        return $this;
    }

    public function addEmailFilter($filter)
    {
        $this->addSubscriptionData();

        $this
            ->getSelect()
            ->where("subscription.email LIKE ?", "%".$filter."%");


        return $this;
    }

    public function addPostTextFilter($filter)
    {
        $this->addPostData();
        $this
            ->getSelect()
            ->where("post.title LIKE ?", "%".$filter."%");

        return $this;
    }

    public function addCommenterNameFilter($filter)
    {
        $this->getSelect()
            ->where("subscription.customer_name LIKE ?", "%".$filter."%")
        ;

        return $this;
    }

    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == "customer_name") {

            $this
                ->getSelect()
                ->order("subscription.customer_name {$direction}");

        } elseif ($field == "email"){

            $this
                ->getSelect()
                ->order("subscription.email {$direction}")
            ;

        } elseif ($field == "post_id"){

            $this->addPostData();
            $this
                ->getSelect()
                ->order("post.title {$direction}")
            ;

        } else {

            return parent::setOrder($field, $direction);
        }
    }
}