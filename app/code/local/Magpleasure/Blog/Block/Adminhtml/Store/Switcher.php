<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mpblog/store/switcher.phtml');
    }

    public function getResetValue()
    {
        return Magpleasure_Blog_Helper_Data_Store::RESET_VALUE;
    }
}