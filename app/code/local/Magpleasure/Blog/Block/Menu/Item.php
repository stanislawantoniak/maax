<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Block_Menu_Item extends Mage_Core_Block_Abstract
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _addBlogLink($parentBlock)
    {
        /** @var Mage_Page_Block_Template_Links $parentBlock  */
        $parentBlock->addLink(
            $this->_helper()->getMenuLabel(),
            $this->_helper()->_url()->getUrl(),
            $this->_helper()->getMenuLabel(),
            false,
            array(),
            $this->_helper()->getMenuPosition(),
            null,
            'class="top-link-mpblog"'
        );
        return $this;
    }

    public function addBlogLink()
    {
        /** @var Mage_Page_Block_Template_Links $parentBlock  */
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && $this->_helper()->getMenuEnabled()) {
            $this->_addBlogLink($parentBlock);
        }

        return $this;
    }

    public function addTopMenuLink()
    {
        $this->addBlogLink();
        return $this;
    }

    public function addFooterMenuLink()
    {
        /** @var Mage_Page_Block_Template_Links $parentBlock  */
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && $this->_helper()->getFooterEnabled()){
            $this->_addBlogLink($parentBlock);
        }
        return $this;
    }
}