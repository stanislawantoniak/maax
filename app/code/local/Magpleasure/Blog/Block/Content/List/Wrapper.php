<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_List_Wrapper extends Mage_Core_Block_Template
{
    protected function _getPager()
    {
        return $this->getLayout()->getBlock(Magpleasure_Blog_Block_Content_List::PAGER_BLOCK_NAME);
    }

    public function getNextUrl()
    {
        if ($pager = $this->_getPager()){
            if (!$pager->isLastPage()){
                return $pager->getNextPageUrl();
            }
        }
        return "";
    }

    public function getPreviousUrl()
    {
        if ($pager = $this->_getPager()){
            if (!$pager->isFirstPage()){
                return $pager->getPreviousPageUrl();
            }
        }
        return "";
    }

}