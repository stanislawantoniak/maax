<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Widgets extends Mage_Core_Helper_Abstract
{
    /**
     * Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Ajax Dropdown Helper
     *
     * @return Magpleasure_Common_Helper_Widgets_Ajaxdropdown
     */
    public function getAjaxDropdown()
    {
        return Mage::helper('magpleasure/widgets_ajaxdropdown');
    }
}