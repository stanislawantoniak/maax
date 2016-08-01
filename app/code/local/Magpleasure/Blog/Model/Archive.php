<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Archive extends Magpleasure_Blog_Model_Abstract implements Magpleasure_Blog_Model_Interface
{
    const DEFAULT_LIMIT = 12;

    public function getLimit()
    {
        return self::DEFAULT_LIMIT;
    }

    public function load($id, $field=null)
    {
        $pattern = "/^([\d]{4})-([\d]{2})$/i";
        preg_match_all($pattern, $id, $matches);

        if (count($matches[1]) && count($matches[2])){
            $year = $matches[1][0];
            $month = $matches[2][0];

            if ($year && $month){
                $this->setYear($year);
                $this->setMonth($month);
            }
        }
        return $this;
    }

    protected function _getDateObject()
    {
        $date = new Zend_Date();
        $date
            ->setYear($this->getYear())
            ->setDay(1)
            ->setMonth($this->getMonth())
        ;

        return $date;
    }

    public function getFromFilter()
    {
        $date = $this->_getDateObject();
        $date->setDay(1)->setHour(0)->setMinute(0)->setSecond(0);
        $date->addSecond($this->_helper()->getTimezoneOffset());
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    public function getToFilter()
    {
        $date = $this->_getDateObject();
        $date->setDay(1)->setHour(23)->setMinute(59)->setSecond(59)->addMonth(1)->subDay(1);
        $date->addSecond($this->_helper()->getTimezoneOffset());
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    public function getId()
    {
        if ($this->getYear() && $this->getMonth()){
            $date = new Zend_Date();
            $date->setYear($this->getYear());
            $date->setDay(1);
            $date->setMonth($this->getMonth());
            $date->setLocale(Mage::app()->getLocale()->getLocaleCode());
            return "{$date->toString(Zend_Date::YEAR)}-{$date->toString(Zend_Date::MONTH)}";
        } else {
            return false;
        }
    }

    public function getLabel($storeId = null)
    {
        $date = new Zend_Date();
        $date->setYear($this->getYear());
        $date->setDay(1);
        $date->setMonth($this->getMonth());
        $date->setLocale(Mage::app()->getLocale()->getLocaleCode());
        return "{$date->toString(Zend_Date::MONTH_NAME)} {$date->toString(Zend_Date::YEAR)}";
    }

    public function getArchives($storeId = null)
    {
        $storeId = $storeId ? $storeId : Mage::app()->getStore()->getId();

        $periods = array();
        /** @var $post Magpleasure_Blog_Model_Post */
        $post = Mage::getModel('mpblog/post');
        $readConnection = $post->getResource()->getReadConnection();
        $select = new Zend_Db_Select($post->getResource()->getReadConnection());

        $postTable = $post->getResource()->getMainTable();
        $postTableStore = $postTable."_store";

        $siteTz = $this->_helper()->getTimeZoneOffset(true);

        $select->from(array('post'=>$postTable), array(
                    'year' => new Zend_Db_Expr("YEAR(CONVERT_TZ(IFNULL(post.published_at, post.created_at), '+00:00', '{$siteTz}') )"),
                    'month' => new Zend_Db_Expr("MONTH(CONVERT_TZ(IFNULL(post.published_at, post.created_at), '+00:00', '{$siteTz}'))"),
                ))
                ->where("post.status = ?", Magpleasure_Blog_Model_Post::STATUS_ENABLED)
                ->order(new Zend_Db_Expr("YEAR(CONVERT_TZ(IFNULL(post.published_at, post.created_at), '+00:00', '{$siteTz}')) DESC, MONTH(CONVERT_TZ(IFNULL(post.published_at, post.created_at), '+00:00', '{$siteTz}')) DESC"))
                ->group(new Zend_Db_Expr("YEAR(CONVERT_TZ(IFNULL(post.published_at, post.created_at), '+00:00', '{$siteTz}')), MONTH(CONVERT_TZ(IFNULL(post.published_at, post.created_at), '+00:00', '{$siteTz}'))"))
                ;


        if (!Mage::app()->isSingleStoreMode()){
            $select
                ->join(array('postStore'=>$postTableStore), "postStore.post_id = post.post_id", array())
                ->where("postStore.store_id = ?", $storeId)
                ;
        }

        try {

            foreach ($readConnection->fetchAll($select) as $row){
                /** @var $archive  Magpleasure_Blog_Model_Archive */
                $archive = Mage::getModel('mpblog/archive');
                $archive->addData($row);
                $periods[] = $archive;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $periods;
    }

    public function getArchiveUrl($page = 1)
    {
        return $this->_helper()->_url($this->getStoreId())->getUrl($this->getId(), Magpleasure_Blog_Helper_Url::ROUTE_ARCHIVE, $page);
    }

    public function getUrl($params = array(), $page = 1)
    {
        return $this->getArchiveUrl($page);
    }
}