<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Social_Button extends Mage_Core_Block_Template
{
    public function getLocaleCode()
    {
        return Mage::app()->getLocale()->getLocaleCode();
    }
}