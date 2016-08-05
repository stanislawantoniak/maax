<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Abstract Option Model
 */
class Magpleasure_Common_Model_System_Config_Source_Abstract
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
     * Generate Option Array from Parametric One
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_commonHelper()->getArrays()->paramsToValueLabel($this->toArray());
    }

    /**
     * Generate Parametric Array from Option One
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_commonHelper()->getArrays()->valueLabelToParams($this->toOptionArray());
    }
}