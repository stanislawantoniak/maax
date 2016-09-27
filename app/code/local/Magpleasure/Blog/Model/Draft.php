<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Draft extends Magpleasure_Common_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('mpblog/draft');
    }

    public function loadByPostAndUser($postId, $userId)
    {
        $this->loadByFewFields(array(
            'post_id' => $postId,
            'user_id' => $userId,
        ));

        return $this;
    }

    public function loadLatestUnassigned($userId)
    {
        $drafts = $this->getCollection();
        $drafts
            ->addFieldToFilter('user_id', $userId)
            ->addFieldToFilter('post_id', array('null' => true))
            ->setOrder('updated_at')
            ->setPageSize(1)
            ;

        foreach ($drafts as $draft){
            $this->load($draft->getId());
        }

        return $this;
    }

    public function clearForPost($postId, $userId)
    {
        $drafts = $this->getCollection();
        $drafts
            ->addFieldToFilter('post_id', $postId)
            ->addFieldToFilter('user_id', $userId)
            ->flushSelected()
            ;

        return $this;
    }

    public function clearUnassigned($userId)
    {
        $drafts = $this->getCollection();
        $drafts
            ->addFieldToFilter('user_id', $userId)
            ->addFieldToFilter('post_id', array('null' => true))
            ->flushSelected()
        ;

        return $this;
    }
}