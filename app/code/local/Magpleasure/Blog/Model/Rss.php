<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Rss
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function blockCreated($event)
    {
        if (!$this->_helper()->getRssComment() && !$this->_helper()->getRssPost()){
            return ;
        }

        /** @var Mage_Core_Model_Layout $layout  */
        $layout = $event->getLayout();
        if ($layout){
            $rssList = $layout->getBlock('rss.list');
            if ($rssList instanceof Mage_Rss_Block_List){
                $alias = $rssList->getBlockAlias();
                $name = $rssList->getNameInLayout();
                $templte = $rssList->getTemplate();
                $parent = $rssList->getParentBlock();

                $layout->unsetBlock($name);
                $parent->unsetChild($alias);
                /** @var Magpleasure_Blog_Block_Rss_List $newRss  */
                $newRss = $layout->createBlock('mpblog/rss_list', $name);
                $newRss->setTemplate($templte);
                $parent->setChild($alias, $name);
                $parent->insert($newRss, $name);
            }
        }
    }

}