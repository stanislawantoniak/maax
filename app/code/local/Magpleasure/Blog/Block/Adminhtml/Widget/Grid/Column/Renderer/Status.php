<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Widget_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Date Helper
     *
     * @return Magpleasure_Blog_Helper_Date
     */
    protected function _date()
    {
        return Mage::helper('mpblog/date');
        
    }
    
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $status = $row->getStatus();
        $scheduledAt = $row->getPublishedAt();
        $localeCode = Mage::app()->getLocale()->getLocaleCode();

        if (($status == Magpleasure_Blog_Model_Post::STATUS_SCHEDULED) && $scheduledAt){

            $scheduledAt = new Zend_Date(
                $scheduledAt,
                Varien_Date::DATETIME_INTERNAL_FORMAT,
                $localeCode
            );
            $scheduledAt
                ->setTimezone(
                    $this->_date()->getTimezone()
                );

            $label = $this->_helper()->__(
                    "Scheduled on %s",
                    $scheduledAt->toString(Zend_Date::DATETIME_MEDIUM
                )
            );

            return $label;

        } else {
            return parent::render($row);
        }
    }
}