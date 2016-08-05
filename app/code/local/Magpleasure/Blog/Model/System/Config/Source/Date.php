<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_System_Config_Source_Date
    extends Magpleasure_Common_Model_System_Config_Source_Abstract
{
    /**
     * Blog Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function toArray()
    {
        $date = new Zend_Date();
        $date->subDay(6)->subHour(5)->subHour(3);

        return array(
            Magpleasure_Blog_Helper_Date::DATE_TIME_PASSED => $this->_helper()->_date()->getHumanizedDate($date),
            Magpleasure_Blog_Helper_Date::DATE_TIME_DIRECT => $this->_helper()->_date()->renderDate($date, null, true),
        );
    }
}

