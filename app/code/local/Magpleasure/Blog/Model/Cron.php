<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Cron
{
    const CACHE_SCHEDULE_LOCK_ID = 'mpblog_cron_cache_lock_schedule';
    const CACHE_VIEWS_LOCK_ID = 'mpblog_cron_cache_lock_views';

    protected static function activateScheduledPosts()
    {
        /** @var $posts Magpleasure_Blog_Model_Mysql4_Post_Collection */
        $posts = Mage::getModel('mpblog/post')->getCollection();
        $now = new Zend_Date();

        $posts
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_SCHEDULED)
            ->addFieldToFilter('published_at', array('lt' => $now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)))
        ;

        foreach ($posts as $post){
            /** @var $post Magpleasure_Blog_Model_Post */
            $post->activateScheduled();
        }
    }

    protected static function sendNotifications()
    {
        /** @var $notifications Magpleasure_Blog_Model_Mysql4_Comment_Notification_Collection */
        $notifications = Mage::getModel('mpblog/comment_notification')->getCollection();

        $notifications
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Comment_Notification::STATUS_WAIT)
            ->setOrder('created_at', Mage_Core_Model_Mysql4_Collection_Abstract::SORT_ORDER_ASC)
            ->setPageSize(26)
            ;

        foreach ($notifications as $notification){
            /** @var $notification Magpleasure_Blog_Model_Comment_Notification */
            $notification->send();
        }

    }

    public static function runSchedule()
    {
        try {
            if(self::checkLock(self::CACHE_SCHEDULE_LOCK_ID)){
                self::activateScheduledPosts();
                self::sendNotifications();
                Mage::app()->removeCache(self::CACHE_SCHEDULE_LOCK_ID);
            } else {
                echo "Blog Pro was locked";
            }
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }

    public static function runViews()
    {
        try {
            if(self::checkLock(self::CACHE_VIEWS_LOCK_ID)){

                # Save Statistics

                /** @var $views Magpleasure_Blog_Model_Mysql4_View_Collection */
                $views = Mage::getModel('mpblog/view')->getCollection();

                $now = new Zend_Date();
                $now->subDay(1);
                $dayAgoString = $now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

                $views->getSelect()
                    ->columns(array('count' => new Zend_Db_Expr("count(main_table.view_id)")))
                    ->where('main_table.created_at <= ?', $dayAgoString)
                    ->group('main_table.post_id');

                foreach ($views as $viewData){
                    $post = Mage::getModel('mpblog/post')->load($viewData->getPostId());

                    if ($post->getId()){
                        $post
                            ->setViews($post->getViews() + $viewData->getCount())
                            ->save()
                            ;
                    }
                }

                # Delete cache

                /** @var $views Magpleasure_Blog_Model_Mysql4_View */
                $views = Mage::getResourceModel('mpblog/view');
                $views->deleteRowsBefore($dayAgoString);

                Mage::app()->removeCache(self::CACHE_VIEWS_LOCK_ID);
            } else {
                echo "Blog Pro was locked";
            }
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }

    public static function checkLock($lockKey)
    {
        if($time = Mage::app()->loadCache($lockKey)){
            if((time() - $time) <= 1800){
                return false;
            }
        }
        Mage::app()->saveCache(time(), $lockKey, array(), 1800);
        return true;
    }

}