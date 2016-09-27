<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Layout_Abstract extends Mage_Core_Block_Template
{
    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Page Head
     *
     * @return Mage_Page_Block_Html_Head
     */
    public function getHead()
    {
        return $this->getLayout()->getBlock('head');
    }

    /**
     * Common Lib Extra Head
     *
     * @return Magpleasure_Common_Block_Page_Extrahead
     */
    public function getExtraHead()
    {
        return $this->getLayout()->getBlock('extra_head');
    }

    public function wantAsserts()
    {
        /** @var Magpleasure_Blog_Block_Layout $license */
        $license = $this->getLayout()->getBlock('layout');
        if ($license){

            $alias = $this->getNameInLayout();
            $parts = explode(".", $alias);
            $alias = $parts[count($parts) - 1];
            return $license->isBlockUsed($alias);
        }
        return true;
    }
}