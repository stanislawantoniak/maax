<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Block_Page_Bodyend extends Magpleasure_Common_Block_Template
{
    /**
     * Retrieves Extra Head instance
     *
     * @return Magpleasure_Common_Block_Page_Extrahead
     */
    protected function _getExtraHead()
    {
        return $this->getLayout()->getBlock('extra_head');
    }

    public function hasTemplates()
    {
        if ($head = $this->_getExtraHead()){
            return $head->hasTemplates();
        }

        return false;
    }

    public function getTemplates()
    {
        if ($head = $this->_getExtraHead()){
            return $head->getTemplates();
        }

        return false;
    }

    public function getTemplateHtml(array $template)
    {
        if ($head = $this->_getExtraHead()){
            return $head->getTemplateHtml($template);
        }

        return false;
    }
}