<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

///TODO Need to be implemented in future versions
class Magpleasure_Searchcore_Model_Processor_Eav extends Magpleasure_Searchcore_Model_Processor_Abstract
{
    public function process(Mage_Core_Model_Abstract $object)
    {
        // TODO: Implement process() method.
    }

    /**
     * Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }
}